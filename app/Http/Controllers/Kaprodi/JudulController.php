<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Pembimbing;
use App\Models\PengajuanJudul;
use App\Models\Skripsi;
use App\Models\Tahapan;
use App\Services\JudulSerupa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JudulController extends Controller
{
    public function index(Request $r)
    {
        $status = $r->get('status', 'menunggu');
        $q = PengajuanJudul::with('mahasiswa.pengguna')->orderByDesc('tanggal_ajuan');
        if ($status !== 'semua') $q->where('status', $status);
        return view('kaprodi.judul.index', ['items' => $q->paginate(20)->withQueryString(), 'status' => $status,
            'jumlah' => PengajuanJudul::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status')]);
    }

    public static function daftarDosen()
    {
        return Dosen::with('pengguna')->where('status_dosen', 'aktif')->get()->map(function ($d) {
            $d->t1 = $d->terisi(1); $d->t2 = $d->terisi(2);
            return $d;
        })->sortBy(fn ($d) => $d->pengguna->nama)->values();
    }

    public function show(PengajuanJudul $pengajuan)
    {
        $pengajuan->load('mahasiswa.pengguna', 'asal', 'kaprodi');
        $antrian = PengajuanJudul::with('mahasiswa.pengguna')->where('status', 'menunggu')->orderBy('tanggal_ajuan')->limit(15)->get();
        $riwayat = PengajuanJudul::where('id_mahasiswa', $pengajuan->id_mahasiswa)->where('id_pengajuan', '!=', $pengajuan->id_pengajuan)->orderByDesc('id_pengajuan')->get();
        return view('kaprodi.judul.show', [
            'p' => $pengajuan, 'antrian' => $antrian, 'riwayat' => $riwayat,
            'serupa' => JudulSerupa::cari($pengajuan->judul, $pengajuan->kata_kunci, $pengajuan->id_mahasiswa),
            'dosen' => self::daftarDosen(),
        ]);
    }

    public function putuskan(Request $r, PengajuanJudul $pengajuan)
    {
        if ($pengajuan->status !== 'menunggu') return $this->gagal('Pengajuan ini sudah diputuskan.');
        $v = $r->validate([
            'keputusan' => 'required|in:disetujui,perlu_revisi,ditolak',
            'catatan_kaprodi' => 'required_unless:keputusan,disetujui|nullable|string|max:2000',
            'judul_final' => 'nullable|string|max:255',
            'pembimbing_1' => 'required_if:keputusan,disetujui|nullable|exists:dosen,id_dosen',
            'pembimbing_2' => 'required_if:keputusan,disetujui|nullable|exists:dosen,id_dosen|different:pembimbing_1',
            'alasan' => 'nullable|string|max:255',
        ], ['catatan_kaprodi.required_unless' => 'Catatan wajib diisi untuk keputusan perlu revisi atau ditolak.',
            'pembimbing_2.different' => 'Pembimbing I dan Pembimbing II harus dosen yang berbeda.',
            'pembimbing_1.required_if' => 'Pilih Pembimbing I.', 'pembimbing_2.required_if' => 'Pilih Pembimbing II.']);
        $v += ['catatan_kaprodi' => null, 'judul_final' => null, 'pembimbing_1' => null, 'pembimbing_2' => null, 'alasan' => null];

        $mhs = $pengajuan->mahasiswa;
        if ($v['keputusan'] === 'disetujui') {
            if ($mhs->skripsi) return $this->gagal('Mahasiswa ini sudah memiliki skripsi aktif.');
            $lebih = [];
            foreach ([1, 2] as $u) {
                $d = Dosen::find($v['pembimbing_'.$u]);
                if ($d->terisi($u) >= $d->kuota($u)) $lebih[] = 'Pembimbing '.($u == 1 ? 'I' : 'II').' ('.$d->pengguna->nama.')';
            }
            if ($lebih && blank($v['alasan'])) {
                return $this->gagal('Kuota penuh untuk '.implode(' dan ', $lebih).'. Isi alasan penetapan melebihi kuota untuk tetap menyimpan.');
            }
        }

        DB::transaction(function () use ($pengajuan, $v, $mhs) {
            $pengajuan->update(['status' => $v['keputusan'], 'catatan_kaprodi' => $v['catatan_kaprodi'] ?? null,
                'id_kaprodi' => auth()->id(), 'tanggal_putusan' => now()]);
            LogAktivitas::catat('Memutuskan pengajuan judul: '.PengajuanJudul::STATUS[$v['keputusan']], 'pengajuan_judul', $pengajuan->id_pengajuan);

            if ($v['keputusan'] === 'disetujui') {
                $s = Skripsi::create(['id_mahasiswa' => $mhs->id_mahasiswa, 'id_pengajuan' => $pengajuan->id_pengajuan,
                    'id_tahapan' => Tahapan::urutan(2)->id_tahapan, 'judul_final' => ($v['judul_final'] ?? null) ?: $pengajuan->judul,
                    'status_skripsi' => 'aktif', 'tanggal_mulai' => today(), 'tanggal_masuk_tahap' => today()]);
                foreach ([1, 2] as $u) {
                    $p = Pembimbing::create(['id_skripsi' => $s->id_skripsi, 'id_dosen' => $v['pembimbing_'.$u], 'urutan' => $u, 'status' => 'aktif',
                        'tanggal_penetapan' => today(), 'alasan' => $v['alasan'] ?? null]);
                    Notifikasi::kirim($p->dosen->id_pengguna, 'Mahasiswa bimbingan baru', 'Anda ditetapkan sebagai '.$p->label().' untuk '.$mhs->pengguna->nama.'.', route('dosen.mahasiswa.show', $s));
                }
                LogAktivitas::catat('Menetapkan pembimbing', 'skripsi', $s->id_skripsi);
                Notifikasi::kirim($mhs->id_pengguna, 'Judul disetujui', 'Judul Anda disetujui dan pembimbing telah ditetapkan.', route('mahasiswa.beranda'));
            } else {
                Notifikasi::kirim($mhs->id_pengguna, 'Pengajuan judul: '.PengajuanJudul::STATUS[$v['keputusan']], mb_substr($v['catatan_kaprodi'], 0, 200), route('mahasiswa.judul'));
            }
        });
        $berikut = PengajuanJudul::where('status', 'menunggu')->orderBy('tanggal_ajuan')->first();
        return redirect($berikut ? route('kaprodi.judul.show', $berikut) : route('kaprodi.judul.index'))->with('ok', 'Keputusan disimpan.');
    }
}

<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Pembimbing;
use App\Models\Skripsi;
use App\Models\Tahapan;
use App\Services\Peringatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    public function index(Request $r)
    {
        $q = Skripsi::query();
        if ($r->filled('tahap')) $q->where('id_tahapan', $r->tahap);
        $q->where('status_skripsi', $r->get('status', 'aktif'));
        if ($s = trim((string) $r->cari)) {
            $q->where(fn ($w) => $w->whereRaw('LOWER(judul_final) LIKE ?', ['%'.mb_strtolower($s).'%'])
                ->orWhereHas('mahasiswa', fn ($m) => $m->where('nim', 'like', "%$s%")->orWhereHas('pengguna', fn ($p) => $p->whereRaw('LOWER(nama) LIKE ?', ['%'.mb_strtolower($s).'%']))));
        }
        $items = Peringatan::muat($q);
        if ($r->boolean('ditinjau')) $items = $items->filter(fn ($s) => $s->indikator)->values();
        $items = $items->sortBy(fn ($s) => $s->mahasiswa->pengguna->nama)->values();
        return view('kaprodi.mahasiswa.index', ['items' => $items, 'tahapan' => Tahapan::semua()]);
    }

    public function show(Skripsi $skripsi)
    {
        return view('kaprodi.mahasiswa.show', ['s' => $skripsi, 'dosen' => JudulController::daftarDosen()]);
    }

    public function gantiPembimbing(Request $r, Skripsi $skripsi)
    {
        $v = $r->validate(['urutan' => 'required|in:1,2', 'id_dosen' => 'required|exists:dosen,id_dosen', 'alasan' => 'required|string|max:255']);
        $lama = $skripsi->pembimbingKe((int) $v['urutan']);
        $lain = $skripsi->pembimbingKe($v['urutan'] == 1 ? 2 : 1);
        if ($lama && $lama->id_dosen == $v['id_dosen']) return $this->gagal('Dosen tersebut sudah menjadi pembimbing pada urutan ini.');
        if ($lain && $lain->id_dosen == $v['id_dosen']) return $this->gagal('Pembimbing I dan II harus dosen yang berbeda.');
        DB::transaction(function () use ($skripsi, $v, $lama) {
            if ($lama) $lama->update(['status' => 'diganti', 'tanggal_berakhir' => today(), 'alasan' => $v['alasan']]);
            $baru = Pembimbing::create(['id_skripsi' => $skripsi->id_skripsi, 'id_dosen' => $v['id_dosen'], 'urutan' => $v['urutan'],
                'status' => 'aktif', 'tanggal_penetapan' => today(), 'alasan' => $v['alasan']]);
            LogAktivitas::catat('Mengganti '.$baru->label(), 'pembimbing', $baru->id_pembimbing);
            Notifikasi::kirim($baru->dosen->id_pengguna, 'Mahasiswa bimbingan baru', 'Anda ditetapkan sebagai '.$baru->label().' untuk '.$skripsi->mahasiswa->pengguna->nama.'.', route('dosen.mahasiswa.show', $skripsi));
            Notifikasi::kirim($skripsi->mahasiswa->id_pengguna, 'Pembimbing diganti', $baru->label().' Anda kini '.$baru->dosen->pengguna->nama.'.', route('mahasiswa.beranda'));
            if ($lama) Notifikasi::kirim($lama->dosen->id_pengguna, 'Penetapan pembimbing berakhir', 'Anda tidak lagi menjadi '.$lama->label().' untuk '.$skripsi->mahasiswa->pengguna->nama.'.', null);
        });
        return $this->ok('Pembimbing diganti. Riwayat bimbingan dengan pembimbing lama tetap tersimpan.');
    }
}

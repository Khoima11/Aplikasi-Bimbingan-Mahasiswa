<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\CatatanBimbingan;
use App\Models\ItemRevisi;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Skripsi;
use App\Services\TahapanService;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function index()
    {
        $d = $this->dosen();
        $mhs = DashboardController::mahasiswaBimbingan($d->id_dosen);
        $mhs->each(function ($s) use ($d) {
            $s->sebagai = $s->pembimbing->firstWhere('id_dosen', $d->id_dosen)?->label();
            $s->menunggu = $s->catatan()->where('id_dosen', $d->id_dosen)->where('status_validasi', 'menunggu')->count();
        });
        return view('dosen.mahasiswa.index', ['mhs' => $mhs->sortBy(fn ($s) => $s->mahasiswa->pengguna->nama)->values()]);
    }

    private function pastikanPembimbing(Skripsi $s): void
    {
        abort_unless($s->dibimbingOleh($this->dosen()), 403, 'Anda bukan pembimbing aktif mahasiswa ini.');
    }

    public function show(Skripsi $skripsi)
    {
        $d = $this->dosen();
        abort_unless($skripsi->semuaPembimbing()->where('id_dosen', $d->id_dosen)->exists(), 403);
        return view('dosen.mahasiswa.show', [
            's' => $skripsi, 'd' => $d, 'aktif' => $skripsi->dibimbingOleh($d),
            'menunggu' => $skripsi->catatan()->where('id_dosen', $d->id_dosen)->where('status_validasi', 'menunggu')->orderBy('tanggal_bimbingan')->get(),
            'revisi' => $skripsi->revisi()->with('dokumenPerbaikan')->where('id_dosen', $d->id_dosen)->orderByRaw("case status when 'dikerjakan' then 0 when 'belum' then 1 else 2 end")->orderByDesc('id_revisi')->get(),
            'syarat' => TahapanService::syarat($skripsi, $d),
            'catatanTerakhir' => $skripsi->catatan()->where('id_dosen', $d->id_dosen)->where('status_validasi', 'divalidasi')->orderByDesc('tanggal_bimbingan')->limit(10)->get(),
        ]);
    }

    private function catatanMilik(CatatanBimbingan $c): void
    {
        abort_unless($c->id_dosen === $this->dosen()->id_dosen, 403);
    }

    public function validasi(Request $r, CatatanBimbingan $catatan)
    {
        $this->catatanMilik($catatan);
        if ($catatan->status_validasi !== 'menunggu') return $this->gagal('Catatan ini sudah ditanggapi.');
        $catatan->update(['status_validasi' => 'divalidasi', 'catatan_dosen' => $r->input('catatan_dosen') ?: null, 'tanggal_validasi' => now()]);
        if ($catatan->id_jadwal) $catatan->jadwal?->update(['status' => 'selesai']);
        LogAktivitas::catat('Memvalidasi catatan bimbingan', 'catatan_bimbingan', $catatan->id_catatan);
        Notifikasi::kirim($catatan->skripsi->mahasiswa->id_pengguna, 'Catatan divalidasi', 'Catatan bimbingan '.$catatan->tanggal_bimbingan->format('d/m/Y').' telah divalidasi.', route('mahasiswa.bimbingan'));
        return $this->ok('Catatan divalidasi.');
    }

    public function koreksi(Request $r, CatatanBimbingan $catatan)
    {
        $this->catatanMilik($catatan);
        $v = $r->validate(['catatan_dosen' => 'required|string|max:2000'], ['catatan_dosen.required' => 'Tanggapan wajib diisi bila catatan dikoreksi.']);
        if ($catatan->status_validasi !== 'menunggu') return $this->gagal('Catatan ini sudah ditanggapi.');
        $catatan->update(['status_validasi' => 'dikoreksi', 'catatan_dosen' => $v['catatan_dosen'], 'tanggal_validasi' => now()]);
        LogAktivitas::catat('Mengoreksi catatan bimbingan', 'catatan_bimbingan', $catatan->id_catatan);
        Notifikasi::kirim($catatan->skripsi->mahasiswa->id_pengguna, 'Catatan dikoreksi', 'Dosen mengoreksi catatan '.$catatan->tanggal_bimbingan->format('d/m/Y').'. Silakan perbaiki.', route('mahasiswa.bimbingan'));
        return $this->ok('Catatan dikembalikan ke mahasiswa untuk diperbaiki.');
    }

    public function tambahRevisi(Request $r, Skripsi $skripsi)
    {
        $this->pastikanPembimbing($skripsi);
        $v = $r->validate(['bab' => 'required|string|max:30', 'uraian' => 'required|string|max:3000', 'id_catatan' => 'nullable|exists:catatan_bimbingan,id_catatan']);
        $i = ItemRevisi::create(['id_skripsi' => $skripsi->id_skripsi, 'id_dosen' => $this->dosen()->id_dosen, 'id_catatan' => $v['id_catatan'] ?? null,
            'bab' => $v['bab'], 'uraian' => $v['uraian'], 'status' => 'belum', 'tanggal_dibuat' => now()]);
        LogAktivitas::catat('Menambah item revisi', 'item_revisi', $i->id_revisi);
        Notifikasi::kirim($skripsi->mahasiswa->id_pengguna, 'Revisi baru', 'Item revisi baru pada '.$v['bab'].'.', route('mahasiswa.revisi'));
        return $this->ok('Item revisi ditambahkan.');
    }

    private function revisiMilik(ItemRevisi $i): void { abort_unless($i->id_dosen === $this->dosen()->id_dosen, 403); }

    public function terimaRevisi(ItemRevisi $revisi)
    {
        $this->revisiMilik($revisi);
        if ($revisi->status === 'diterima') return $this->gagal('Revisi sudah diterima.');
        $revisi->update(['status' => 'diterima', 'tanggal_diterima' => now()]);
        LogAktivitas::catat('Menerima item revisi', 'item_revisi', $revisi->id_revisi);
        Notifikasi::kirim($revisi->skripsi->mahasiswa->id_pengguna, 'Revisi diterima', 'Revisi '.$revisi->bab.' diterima dosen.', route('mahasiswa.revisi', ['tab' => 'diterima']));
        return $this->ok('Revisi diterima.');
    }

    public function kembalikanRevisi(Request $r, ItemRevisi $revisi)
    {
        $this->revisiMilik($revisi);
        $v = $r->validate(['alasan' => 'required|string|max:200']);
        $revisi->update(['status' => 'belum', 'keterangan_mahasiswa' => 'Dikembalikan dosen: '.$v['alasan'], 'tanggal_dikerjakan' => null]);
        Notifikasi::kirim($revisi->skripsi->mahasiswa->id_pengguna, 'Revisi dikembalikan', $revisi->bab.': '.$v['alasan'], route('mahasiswa.revisi'));
        return $this->ok('Revisi dikembalikan ke mahasiswa.');
    }

    public function persetujuan(Request $r, Skripsi $skripsi)
    {
        $this->pastikanPembimbing($skripsi);
        $e = TahapanService::beriPersetujuan($skripsi, $this->dosen(), $r->input('catatan'));
        return $e ? $this->gagal($e) : $this->ok('Persetujuan disimpan.');
    }
}

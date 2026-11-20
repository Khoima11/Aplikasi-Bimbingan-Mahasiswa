<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\CatatanBimbingan;
use App\Models\Dosen;
use App\Models\PengajuanJudul;
use App\Models\Skripsi;
use App\Models\Tahapan;
use App\Services\Peringatan;

class DashboardController extends Controller
{
    public static function bebanDosen()
    {
        return Dosen::with('pengguna')->where('status_dosen', 'aktif')->get()->map(function ($d) {
            $d->p1 = $d->terisi(1); $d->p2 = $d->terisi(2);
            $d->draf_lama = Peringatan::drafTerlambat($d->id_dosen);
            $d->menunggu = CatatanBimbingan::where('id_dosen', $d->id_dosen)->where('status_validasi', 'menunggu')->count();
            return $d;
        })->sortBy(fn ($d) => $d->pengguna->nama)->values();
    }

    public static function rataLama(): ?float
    {
        $selesai = Skripsi::where('status_skripsi', 'selesai')->whereNotNull('tanggal_selesai')->get();
        if ($selesai->isEmpty()) return null;
        return round($selesai->avg(fn ($s) => $s->tanggal_mulai->diffInDays($s->tanggal_selesai)) / 30.4, 1);
    }

    public function index()
    {
        $aktif = Peringatan::muat(Skripsi::where('status_skripsi', 'aktif'));
        $tahapan = Tahapan::semua();
        $belumSkripsi = \App\Models\Mahasiswa::where('status_mahasiswa', 'aktif')->doesntHave('skripsi')->count();
        $sebaran = $tahapan->map(fn ($t) => [
            'nama' => $t->nama_tahapan,
            'jumlah' => $t->urutan === 1 ? $belumSkripsi : Skripsi::where('id_tahapan', $t->id_tahapan)->count(),
        ]);
        return view('kaprodi.dashboard', [
            'jumlahAktif' => $aktif->count(),
            'menunggu' => PengajuanJudul::where('status', 'menunggu')->count(),
            'ditinjau' => $aktif->filter(fn ($s) => $s->indikator)->values(),
            'rata' => self::rataLama(),
            'sebaran' => $sebaran,
            'dosen' => self::bebanDosen(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\CatatanBimbingan;
use App\Models\JadwalBimbingan;
use App\Models\Skripsi;
use App\Services\Peringatan;

class DashboardController extends Controller
{
    public static function mahasiswaBimbingan(int $idDosen)
    {
        return Peringatan::muat(Skripsi::where('status_skripsi', 'aktif')->whereHas('pembimbing', fn ($q) => $q->where('id_dosen', $idDosen)));
    }

    public function index()
    {
        $d = $this->dosen();
        $mhs = self::mahasiswaBimbingan($d->id_dosen);
        return view('dosen.dashboard', [
            'd' => $d, 'mhs' => $mhs,
            'nPermintaan' => JadwalBimbingan::where('id_dosen', $d->id_dosen)->where('status', 'diajukan')->count(),
            'nCatatan' => CatatanBimbingan::where('id_dosen', $d->id_dosen)->where('status_validasi', 'menunggu')->count(),
            'nDraf' => Peringatan::drafMenunggu($d->id_dosen)->count(),
            'jadwal' => JadwalBimbingan::with('skripsi.mahasiswa.pengguna')->where('id_dosen', $d->id_dosen)->where('status', 'disetujui')
                ->whereBetween('tanggal', [today(), today()->addDays(7)])->orderBy('tanggal')->orderBy('jam')->get(),
        ]);
    }

    /** Halaman validasi cepat (ramah HP). */
    public function tindakan()
    {
        $d = $this->dosen();
        return view('dosen.tindakan', [
            'catatan' => CatatanBimbingan::with('skripsi.mahasiswa.pengguna')->where('id_dosen', $d->id_dosen)->where('status_validasi', 'menunggu')->orderBy('tanggal_bimbingan')->get(),
            'jadwal' => JadwalBimbingan::with('skripsi.mahasiswa.pengguna')->where('id_dosen', $d->id_dosen)->where('status', 'diajukan')->orderBy('tanggal')->get(),
            'draf' => Peringatan::drafMenunggu($d->id_dosen)->with('skripsi.mahasiswa.pengguna')->orderBy('tanggal_unggah')->get(),
        ]);
    }
}

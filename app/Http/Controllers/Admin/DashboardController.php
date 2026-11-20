<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\Periode;
use App\Models\Skripsi;
use App\Services\TahapanService;

class DashboardController extends Controller
{
    public function index()
    {
        $siap = TahapanService::siapUjian();
        return view('admin.dashboard', [
            'siap' => $siap,
            'aktif' => Skripsi::where('status_skripsi', 'aktif')->count(),
            'dosen' => Dosen::where('status_dosen', 'aktif')->count(),
            'periode' => Periode::aktif(),
            'akun' => ['Mahasiswa' => Mahasiswa::count(), 'Dosen' => Dosen::count(), 'Nonaktif' => Pengguna::where('status', 'nonaktif')->count()],
        ]);
    }
}

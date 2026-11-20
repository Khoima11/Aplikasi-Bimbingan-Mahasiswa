<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pengguna;

abstract class Controller
{
    protected function pengguna(): Pengguna { return auth()->user(); }
    protected function dosen(): Dosen
    {
        return $this->pengguna()->dosen ?? abort(403, 'Akun ini belum memiliki data dosen. Hubungi admin.');
    }
    protected function mahasiswa(): Mahasiswa
    {
        return $this->pengguna()->mahasiswa ?? abort(403, 'Akun ini belum memiliki data mahasiswa. Hubungi admin.');
    }
    protected function ok(string $pesan) { return back()->with('ok', $pesan); }
    protected function gagal(string $pesan) { return back()->with('gagal', $pesan)->withInput(); }
}

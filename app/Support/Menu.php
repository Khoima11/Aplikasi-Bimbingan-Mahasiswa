<?php

namespace App\Support;

class Menu
{
    /** [label, route, ikon, pola route aktif, label pendek HP] */
    public static function untuk(?string $peran): array
    {
        return match ($peran) {
            'admin' => [
                ['Dashboard', 'admin.dashboard', 'rumah', 'admin.dashboard', 'Beranda'],
                ['Pengguna', 'admin.pengguna.index', 'orang', 'admin.pengguna.*', 'Pengguna'],
                ['Hasil ujian', 'admin.ujian.index', 'toga', 'admin.ujian.*', 'Ujian'],
                ['Periode & tahapan', 'admin.master', 'atur', 'admin.master', 'Master'],
                ['Log aktivitas', 'admin.log', 'daftar', 'admin.log', 'Log'],
            ],
            'kaprodi' => [
                ['Dashboard', 'kaprodi.dashboard', 'rumah', 'kaprodi.dashboard', 'Beranda'],
                ['Verifikasi judul', 'kaprodi.judul.index', 'centang', 'kaprodi.judul.*', 'Judul'],
                ['Progres mahasiswa', 'kaprodi.mahasiswa.index', 'orang', 'kaprodi.mahasiswa.*', 'Progres'],
                ['Laporan', 'kaprodi.laporan', 'grafik', 'kaprodi.laporan*', 'Laporan'],
            ],
            'dosen' => [
                ['Dashboard', 'dosen.dashboard', 'rumah', 'dosen.dashboard', 'Beranda'],
                ['Perlu tindakan', 'dosen.tindakan', 'centang', 'dosen.tindakan', 'Tindakan'],
                ['Jadwal & slot', 'dosen.jadwal', 'kalender', 'dosen.jadwal', 'Jadwal'],
                ['Mahasiswa', 'dosen.mahasiswa.index', 'orang', 'dosen.mahasiswa.*', 'Mahasiswa'],
                ['Review dokumen', 'dosen.review.index', 'dokumen', 'dosen.review.*', 'Review'],
            ],
            'mahasiswa' => [
                ['Beranda', 'mahasiswa.beranda', 'rumah', 'mahasiswa.beranda', 'Beranda'],
                ['Bimbingan', 'mahasiswa.bimbingan', 'kalender', 'mahasiswa.bimbingan', 'Bimbingan'],
                ['Revisi', 'mahasiswa.revisi', 'centang', 'mahasiswa.revisi', 'Revisi'],
                ['Dokumen', 'mahasiswa.dokumen', 'dokumen', 'mahasiswa.dokumen*', 'Dokumen'],
                ['Pengajuan judul', 'mahasiswa.judul', 'pena', 'mahasiswa.judul*', 'Judul'],
            ],
            default => [],
        };
    }
}

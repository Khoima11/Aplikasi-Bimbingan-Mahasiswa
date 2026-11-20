<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dosen;
use App\Http\Controllers\Kaprodi;
use App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\UmumController;
use Illuminate\Support\Facades\Route;

// Publik
Route::get('/login', [AuthController::class, 'form'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:10,1']);
Route::get('/verifikasi/{kode?}', [UmumController::class, 'verifikasi'])->name('verifikasi');

Route::middleware(['auth', 'siap'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/kata-sandi', [AuthController::class, 'formPassword'])->name('password.form');
    Route::post('/kata-sandi', [AuthController::class, 'ubahPassword'])->name('password.update');

    Route::get('/', [UmumController::class, 'beranda'])->name('beranda');
    Route::post('/peran', [UmumController::class, 'gantiPeran'])->name('peran.ganti');
    Route::get('/akun', [UmumController::class, 'akun'])->name('akun');
    Route::get('/notifikasi', [UmumController::class, 'notifikasi'])->name('notifikasi');
    Route::get('/notifikasi/{notifikasi}', [UmumController::class, 'bacaNotifikasi'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', [UmumController::class, 'bacaSemua'])->name('notifikasi.semua');

    Route::get('/berkas/dokumen/{dokumen}', [UmumController::class, 'unduhDokumen'])->name('berkas.dokumen');
    Route::get('/berkas/koreksi/{review}', [UmumController::class, 'unduhKoreksi'])->name('berkas.koreksi');
    Route::get('/lembar/{skripsi}', [UmumController::class, 'lembar'])->name('lembar');
    Route::get('/lembar/{skripsi}/pdf', [UmumController::class, 'lembarPdf'])->name('lembar.pdf');

    // ---------------- ADMIN PRODI ----------------
    Route::prefix('admin')->name('admin.')->middleware('peran:admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pengguna', [Admin\PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('/pengguna/tambah', [Admin\PenggunaController::class, 'create'])->name('pengguna.create');
        Route::post('/pengguna', [Admin\PenggunaController::class, 'store'])->name('pengguna.store');
        Route::get('/pengguna/templat', [Admin\PenggunaController::class, 'templat'])->name('pengguna.templat');
        Route::post('/pengguna/impor', [Admin\PenggunaController::class, 'impor'])->name('pengguna.impor');
        Route::get('/pengguna/{pengguna}', [Admin\PenggunaController::class, 'edit'])->name('pengguna.edit');
        Route::put('/pengguna/{pengguna}', [Admin\PenggunaController::class, 'update'])->name('pengguna.update');
        Route::post('/pengguna/{pengguna}/reset', [Admin\PenggunaController::class, 'reset'])->name('pengguna.reset');
        Route::post('/pengguna/{pengguna}/status', [Admin\PenggunaController::class, 'status'])->name('pengguna.status');

        Route::get('/master', [Admin\MasterController::class, 'index'])->name('master');
        Route::post('/periode', [Admin\MasterController::class, 'simpanPeriode'])->name('periode.store');
        Route::post('/periode/{periode}/aktif', [Admin\MasterController::class, 'aktifkanPeriode'])->name('periode.aktif');
        Route::put('/tahapan', [Admin\MasterController::class, 'simpanTahapan'])->name('tahapan.update');
        Route::put('/pengaturan', [Admin\MasterController::class, 'simpanPengaturan'])->name('pengaturan.update');

        Route::get('/ujian', [Admin\UjianController::class, 'index'])->name('ujian.index');
        Route::get('/ujian/{skripsi}/catat', [Admin\UjianController::class, 'create'])->name('ujian.create');
        Route::post('/ujian/{skripsi}', [Admin\UjianController::class, 'store'])->name('ujian.store');
        Route::post('/ujian/revisi/{ujian}', [Admin\UjianController::class, 'revisiSelesai'])->name('ujian.revisi');

        Route::get('/log', [Admin\LogController::class, 'index'])->name('log');
    });

    // ---------------- KAPRODI ----------------
    Route::prefix('kaprodi')->name('kaprodi.')->middleware('peran:kaprodi')->group(function () {
        Route::get('/', [Kaprodi\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/judul', [Kaprodi\JudulController::class, 'index'])->name('judul.index');
        Route::get('/judul/{pengajuan}', [Kaprodi\JudulController::class, 'show'])->name('judul.show');
        Route::post('/judul/{pengajuan}', [Kaprodi\JudulController::class, 'putuskan'])->name('judul.putuskan');
        Route::get('/mahasiswa', [Kaprodi\MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::get('/mahasiswa/{skripsi}', [Kaprodi\MahasiswaController::class, 'show'])->name('mahasiswa.show');
        Route::post('/mahasiswa/{skripsi}/pembimbing', [Kaprodi\MahasiswaController::class, 'gantiPembimbing'])->name('mahasiswa.pembimbing');
        Route::get('/laporan', [Kaprodi\LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/pdf', [Kaprodi\LaporanController::class, 'pdf'])->name('laporan.pdf');
    });

    // ---------------- DOSEN ----------------
    Route::prefix('dosen')->name('dosen.')->middleware('peran:dosen')->group(function () {
        Route::get('/', [Dosen\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/tindakan', [Dosen\DashboardController::class, 'tindakan'])->name('tindakan');
        Route::get('/jadwal', [Dosen\JadwalController::class, 'index'])->name('jadwal');
        Route::post('/slot', [Dosen\JadwalController::class, 'simpanSlot'])->name('slot.store');
        Route::post('/slot/{slot}/status', [Dosen\JadwalController::class, 'statusSlot'])->name('slot.status');
        Route::post('/jadwal/{jadwal}/setujui', [Dosen\JadwalController::class, 'setujui'])->name('jadwal.setujui');
        Route::post('/jadwal/{jadwal}/tolak', [Dosen\JadwalController::class, 'tolak'])->name('jadwal.tolak');
        Route::post('/jadwal/{jadwal}/usul', [Dosen\JadwalController::class, 'usul'])->name('jadwal.usul');
        Route::get('/mahasiswa', [Dosen\MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::get('/mahasiswa/{skripsi}', [Dosen\MahasiswaController::class, 'show'])->name('mahasiswa.show');
        Route::post('/catatan/{catatan}/validasi', [Dosen\MahasiswaController::class, 'validasi'])->name('catatan.validasi');
        Route::post('/catatan/{catatan}/koreksi', [Dosen\MahasiswaController::class, 'koreksi'])->name('catatan.koreksi');
        Route::post('/mahasiswa/{skripsi}/revisi', [Dosen\MahasiswaController::class, 'tambahRevisi'])->name('revisi.store');
        Route::post('/revisi/{revisi}/terima', [Dosen\MahasiswaController::class, 'terimaRevisi'])->name('revisi.terima');
        Route::post('/revisi/{revisi}/kembalikan', [Dosen\MahasiswaController::class, 'kembalikanRevisi'])->name('revisi.kembalikan');
        Route::post('/mahasiswa/{skripsi}/persetujuan', [Dosen\MahasiswaController::class, 'persetujuan'])->name('persetujuan');
        Route::get('/review', [Dosen\ReviewController::class, 'index'])->name('review.index');
        Route::get('/review/{dokumen}', [Dosen\ReviewController::class, 'show'])->name('review.show');
        Route::post('/review/{dokumen}', [Dosen\ReviewController::class, 'store'])->name('review.store');
    });

    // ---------------- MAHASISWA ----------------
    Route::prefix('mahasiswa')->name('mahasiswa.')->middleware('peran:mahasiswa')->group(function () {
        Route::get('/', [Mahasiswa\BerandaController::class, 'index'])->name('beranda');
        Route::get('/judul', [Mahasiswa\JudulController::class, 'index'])->name('judul');
        Route::post('/judul', [Mahasiswa\JudulController::class, 'store'])->name('judul.store');
        Route::get('/judul/serupa', [Mahasiswa\JudulController::class, 'serupa'])->name('judul.serupa');
        Route::get('/bimbingan', [Mahasiswa\BimbinganController::class, 'index'])->name('bimbingan');
        Route::post('/jadwal', [Mahasiswa\BimbinganController::class, 'ajukan'])->name('jadwal.store');
        Route::post('/jadwal/{jadwal}/terima', [Mahasiswa\BimbinganController::class, 'terimaUsulan'])->name('jadwal.terima');
        Route::post('/jadwal/{jadwal}/batal', [Mahasiswa\BimbinganController::class, 'batal'])->name('jadwal.batal');
        Route::post('/catatan', [Mahasiswa\BimbinganController::class, 'simpanCatatan'])->name('catatan.store');
        Route::put('/catatan/{catatan}', [Mahasiswa\BimbinganController::class, 'perbaikiCatatan'])->name('catatan.update');
        Route::get('/revisi', [Mahasiswa\RevisiController::class, 'index'])->name('revisi');
        Route::post('/revisi/{revisi}', [Mahasiswa\RevisiController::class, 'tandai'])->name('revisi.tandai');
        Route::get('/dokumen', [Mahasiswa\DokumenController::class, 'index'])->name('dokumen');
        Route::post('/dokumen', [Mahasiswa\DokumenController::class, 'store'])->name('dokumen.store');
        Route::get('/dokumen/{dokumen}', [Mahasiswa\DokumenController::class, 'show'])->name('dokumen.show');
    });
});

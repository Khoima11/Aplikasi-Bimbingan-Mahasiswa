<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Periode;
use App\Models\Tahapan;
use Illuminate\Database\Seeder;

/** Data acuan wajib. Aman dijalankan berulang kali. */
class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $peran = ['admin' => 'Mengelola data master, pengguna, dan hasil ujian', 'kaprodi' => 'Verifikasi judul, penetapan pembimbing, pemantauan, laporan',
            'dosen' => 'Membimbing, memvalidasi catatan, mereview draf, memberi persetujuan', 'mahasiswa' => 'Mengajukan judul dan melakukan bimbingan skripsi'];
        foreach ($peran as $n => $k) Peran::firstOrCreate(['nama_peran' => $n], ['keterangan' => $k]);

        $tahap = [
            [1, 'Pengajuan Judul', 30, null, false],
            [2, 'Penyusunan Proposal', 120, 4, true],
            [3, 'Seminar Proposal', 30, null, false],
            [4, 'Penelitian & Penulisan', 180, 6, true],
            [5, 'Sidang Munaqasyah', 30, null, false],
            [6, 'Selesai', null, null, false],
        ];
        foreach ($tahap as [$u, $n, $b, $m, $p]) {
            Tahapan::firstOrCreate(['urutan' => $u], ['nama_tahapan' => $n, 'batas_hari' => $b, 'min_bimbingan' => $m, 'butuh_persetujuan' => $p]);
        }
        foreach (Pengaturan::BAWAAN as $k => [$nilai, $ket]) Pengaturan::firstOrCreate(['kunci' => $k], ['nilai' => $nilai, 'keterangan' => $ket]);

        if (! Periode::exists()) {
            $th = now()->month >= 8 ? now()->year : now()->year - 1;
            $ganjil = now()->month >= 8 || now()->month <= 1;
            Periode::create(['nama_periode' => ($ganjil ? 'Ganjil ' : 'Genap ').$th.'/'.($th + 1),
                'tanggal_mulai' => $ganjil ? "$th-08-01" : ($th + 1).'-02-01', 'tanggal_selesai' => $ganjil ? ($th + 1).'-01-31' : ($th + 1).'-07-31', 'status' => 'aktif']);
        }

        if (! Pengguna::whereHas('peran', fn ($q) => $q->where('nama_peran', 'admin'))->exists()) {
            $u = Pengguna::create(['nama' => 'Admin Prodi', 'username' => 'admin', 'email' => 'admin@sibimta.local',
                'password' => config('sibimta.admin_password'), 'status' => 'aktif', 'wajib_ganti_password' => false]);
            $u->peran()->attach(Peran::where('nama_peran', 'admin')->value('id_peran'));
        }
    }
}

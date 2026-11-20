<?php

namespace Database\Seeders;

use App\Models\Berkas;
use App\Models\CatatanBimbingan;
use App\Models\Dokumen;
use App\Models\Dosen;
use App\Models\ItemRevisi;
use App\Models\JadwalBimbingan;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\Pembimbing;
use App\Models\PengajuanJudul;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Periode;
use App\Models\Persetujuan;
use App\Models\ReviewDokumen;
use App\Models\Skripsi;
use App\Models\SlotBimbingan;
use App\Models\Tahapan;
use App\Models\Ujian;
use Illuminate\Database\Seeder;

/** Data contoh untuk uji coba. Kata sandi semua akun contoh = DEMO_PASSWORD. */
class DemoSeeder extends Seeder
{
    private function akun(string $nama, string $username, array $peran): Pengguna
    {
        $u = Pengguna::create(['nama' => $nama, 'username' => $username, 'email' => strtolower($username).'@sibimta.local',
            'password' => config('sibimta.demo_password'), 'status' => 'aktif', 'wajib_ganti_password' => false]);
        $u->peran()->attach(Peran::whereIn('nama_peran', $peran)->pluck('id_peran'));
        return $u;
    }

    private function pdf(string $judul): string
    {
        $teks = str_replace(['(', ')'], '', $judul);
        $isi = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n4 0 obj<</Length ".(strlen($teks) + 40).">>stream\nBT /F1 14 Tf 60 780 Td ($teks) Tj ET\nendstream endobj\n5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";
        $b = Berkas::create(['mime' => 'application/pdf', 'ukuran_byte' => strlen($isi), 'isi_base64' => base64_encode($isi)]);
        return 'db:'.$b->id_berkas;
    }

    public function run(): void
    {
        $periode = Periode::aktif();
        $T = fn ($u) => Tahapan::urutan($u)->id_tahapan;

        // ---- Dosen ----
        $kap = $this->akun('Dr. Ahmad Fauzi, M.Pd.', '197501012000031001', ['kaprodi', 'dosen']);
        $defs = [
            [$kap, 'Lektor Kepala', 8, 8],
            [$this->akun('Dr. Siti Rahmawati, M.Pd.I.', '198003152006042002', ['dosen']), 'Lektor', 3, 6],
            [$this->akun('Muhammad Hidayat, M.Pd.', '198507202011011003', ['dosen']), 'Asisten Ahli', 6, 6],
            [$this->akun('Dr. Nurul Aini, M.Ag.', '197811102005012004', ['dosen']), 'Lektor', 6, 6],
            [$this->akun('Hendra Saputra, M.Kom.', '199002142019031005', ['dosen']), 'Asisten Ahli', 4, 6],
        ];
        $D = [];
        foreach ($defs as [$u, $jab, $k1, $k2]) {
            $D[] = Dosen::create(['id_pengguna' => $u->id_pengguna, 'nidn' => $u->username, 'jabatan_fungsional' => $jab,
                'kuota_pembimbing_1' => $k1, 'kuota_pembimbing_2' => $k2, 'status_dosen' => 'aktif']);
        }

        // ---- Mahasiswa ----
        $mhs = [
            ['Aisyah Nur Fadilah', '2205108001', 2022], ['Bagas Pratama', '2205108002', 2022], ['Citra Lestari', '2105108003', 2021],
            ['Dimas Arya Putra', '2205108004', 2022], ['Eka Wulandari', '2005108005', 2020], ['Fajar Maulana', '2105108006', 2021],
            ['Gita Permatasari', '2305108007', 2023], ['Hafiz Alfarizi', '2305108008', 2023],
        ];
        $M = [];
        foreach ($mhs as [$n, $nim, $ang]) {
            $u = $this->akun($n, $nim, ['mahasiswa']);
            $M[] = Mahasiswa::create(['id_pengguna' => $u->id_pengguna, 'nim' => $nim, 'angkatan' => $ang, 'no_hp' => '0812'.substr($nim, -7), 'status_mahasiswa' => 'aktif']);
        }

        $judul = [
            0 => ['Manajemen Kurikulum Merdeka di Madrasah Tsanawiyah Negeri Palembang', 'manajemen kurikulum, kurikulum merdeka, madrasah'],
            1 => ['Strategi Kepala Madrasah dalam Meningkatkan Kompetensi Pedagogik Guru', 'kepala madrasah, kompetensi pedagogik, guru'],
            2 => ['Pengelolaan Sarana dan Prasarana Pendidikan di Pondok Pesantren Modern', 'sarana prasarana, pondok pesantren, pengelolaan'],
            3 => ['Implementasi Manajemen Berbasis Sekolah di Madrasah Aliyah Swasta', 'manajemen berbasis sekolah, madrasah aliyah'],
            4 => ['Supervisi Akademik Pengawas Madrasah terhadap Kinerja Guru PAI', 'supervisi akademik, pengawas, kinerja guru'],
            5 => ['Manajemen Hubungan Masyarakat dalam Penerimaan Peserta Didik Baru', 'humas, penerimaan peserta didik baru, manajemen'],
            6 => ['Kepemimpinan Transformasional Kepala Sekolah dan Budaya Mutu', 'kepemimpinan transformasional, budaya mutu'],
        ];
        $latar = 'Berdasarkan observasi awal, pengelolaan pada lembaga tersebut belum berjalan optimal sehingga perlu dikaji bagaimana perencanaan, pelaksanaan, dan evaluasinya dilakukan.';
        $buatSkripsi = function (int $i, int $tahap, int $hariMulai, int $hariTahap, array $pb) use ($M, $judul, $latar, $periode, $T, $kap) {
            $p = PengajuanJudul::create(['id_mahasiswa' => $M[$i]->id_mahasiswa, 'id_periode' => $periode?->id_periode, 'judul' => $judul[$i][0],
                'latar_masalah' => $latar, 'kata_kunci' => $judul[$i][1], 'status' => 'disetujui', 'catatan_kaprodi' => 'Disetujui.',
                'id_kaprodi' => $kap->id_pengguna, 'tanggal_ajuan' => now()->subDays($hariMulai + 5), 'tanggal_putusan' => now()->subDays($hariMulai)]);
            $s = Skripsi::create(['id_mahasiswa' => $M[$i]->id_mahasiswa, 'id_pengajuan' => $p->id_pengajuan, 'id_tahapan' => $T($tahap),
                'judul_final' => $judul[$i][0], 'status_skripsi' => $tahap === 6 ? 'selesai' : 'aktif', 'tanggal_mulai' => today()->subDays($hariMulai),
                'tanggal_masuk_tahap' => today()->subDays($hariTahap), 'tanggal_selesai' => $tahap === 6 ? today()->subDays($hariTahap) : null]);
            foreach ($pb as $u => $d) {
                Pembimbing::create(['id_skripsi' => $s->id_skripsi, 'id_dosen' => $d->id_dosen, 'urutan' => $u + 1, 'status' => 'aktif', 'tanggal_penetapan' => today()->subDays($hariMulai)]);
            }
            return $s;
        };
        $catat = function (Skripsi $s, Dosen $d, int $hariLalu, string $bab, string $status = 'divalidasi') {
            return CatatanBimbingan::create(['id_skripsi' => $s->id_skripsi, 'id_dosen' => $d->id_dosen, 'tanggal_bimbingan' => today()->subDays($hariLalu),
                'mode' => $hariLalu % 2 ? 'luring' : 'daring', 'bab_dibahas' => $bab,
                'ringkasan' => "Membahas {$bab}: memperjelas rumusan masalah, menyesuaikan kerangka teori, dan menambah rujukan jurnal lima tahun terakhir.",
                'status_validasi' => $status, 'catatan_dosen' => $status === 'dikoreksi' ? 'Ringkasan belum mencantumkan arahan tentang teknik sampling. Mohon dilengkapi.' : null,
                'tanggal_input' => now()->subDays($hariLalu), 'tanggal_validasi' => $status === 'menunggu' ? null : now()->subDays(max(0, $hariLalu - 1))]);
        };

        // 0: Aisyah - Penyusunan Proposal, aktif
        $s0 = $buatSkripsi(0, 2, 60, 60, [$D[1], $D[2]]);
        foreach ([50, 36, 20] as $h) $catat($s0, $D[1], $h, 'Bab 1');
        foreach ([40, 18] as $h) $catat($s0, $D[2], $h, 'Bab 2');
        $catat($s0, $D[1], 3, 'Bab 3', 'menunggu');
        $catat($s0, $D[2], 6, 'Bab 2', 'dikoreksi');
        $d0 = Dokumen::create(['id_skripsi' => $s0->id_skripsi, 'bagian' => 'bab1', 'versi' => 1, 'nama_file' => 'Bab1_Aisyah.pdf', 'path_file' => $this->pdf('Bab 1 versi 1'), 'ukuran_kb' => 1, 'catatan_mahasiswa' => 'Draf awal', 'tanggal_unggah' => now()->subDays(30)]);
        $rv = ReviewDokumen::create(['id_dokumen' => $d0->id_dokumen, 'id_dosen' => $D[1]->id_dosen, 'hasil' => 'revisi', 'komentar' => 'Latar belakang terlalu umum.', 'tanggal_review' => now()->subDays(27)]);
        ReviewDokumen::create(['id_dokumen' => $d0->id_dokumen, 'id_dosen' => $D[2]->id_dosen, 'hasil' => 'disetujui', 'komentar' => 'Cukup baik.', 'tanggal_review' => now()->subDays(26)]);
        $d1 = Dokumen::create(['id_skripsi' => $s0->id_skripsi, 'bagian' => 'bab1', 'versi' => 2, 'nama_file' => 'Bab1_Aisyah_rev.pdf', 'path_file' => $this->pdf('Bab 1 versi 2'), 'ukuran_kb' => 1, 'catatan_mahasiswa' => 'Perbaikan latar belakang', 'tanggal_unggah' => now()->subDays(4)]);
        ItemRevisi::create(['id_skripsi' => $s0->id_skripsi, 'id_dosen' => $D[1]->id_dosen, 'id_review' => $rv->id_review, 'bab' => 'Bab 1', 'uraian' => 'Persempit latar belakang pada kondisi madrasah yang diteliti.', 'status' => 'dikerjakan', 'id_dokumen_perbaikan' => $d1->id_dokumen, 'keterangan_mahasiswa' => 'Sudah diperbaiki di halaman 2-3', 'tanggal_dibuat' => now()->subDays(27), 'tanggal_dikerjakan' => now()->subDays(4)]);
        ItemRevisi::create(['id_skripsi' => $s0->id_skripsi, 'id_dosen' => $D[1]->id_dosen, 'bab' => 'Bab 1', 'uraian' => 'Tambahkan data pendukung dari dokumen EMIS madrasah.', 'status' => 'belum', 'tanggal_dibuat' => now()->subDays(20)]);
        ItemRevisi::create(['id_skripsi' => $s0->id_skripsi, 'id_dosen' => $D[2]->id_dosen, 'bab' => 'Bab 2', 'uraian' => 'Gunakan teori manajemen kurikulum dari minimal tiga ahli.', 'status' => 'diterima', 'tanggal_dibuat' => now()->subDays(40), 'tanggal_dikerjakan' => now()->subDays(25), 'tanggal_diterima' => now()->subDays(22)]);

        // 1: Bagas - pengajuan menunggu (pengajuan ulang)
        $pa = PengajuanJudul::create(['id_mahasiswa' => $M[1]->id_mahasiswa, 'id_periode' => $periode?->id_periode, 'judul' => 'Peran Kepala Madrasah terhadap Guru', 'latar_masalah' => $latar, 'kata_kunci' => 'kepala madrasah, guru', 'status' => 'perlu_revisi', 'catatan_kaprodi' => 'Judul terlalu luas, fokuskan pada aspek kompetensi tertentu.', 'id_kaprodi' => $kap->id_pengguna, 'tanggal_ajuan' => now()->subDays(12), 'tanggal_putusan' => now()->subDays(10)]);
        PengajuanJudul::create(['id_mahasiswa' => $M[1]->id_mahasiswa, 'id_periode' => $periode?->id_periode, 'id_pengajuan_asal' => $pa->id_pengajuan, 'judul' => $judul[1][0], 'latar_masalah' => $latar, 'kata_kunci' => $judul[1][1], 'status' => 'menunggu', 'tanggal_ajuan' => now()->subDays(2)]);

        // 2: Citra - Penelitian & Penulisan
        $s2 = $buatSkripsi(2, 4, 260, 90, [$D[0], $D[3]]);
        foreach ([250, 230, 210, 190, 175] as $h) { $catat($s2, $D[0], $h, 'Proposal'); $catat($s2, $D[3], $h - 2, 'Proposal'); }
        foreach ([80, 60, 40, 25, 10] as $h) $catat($s2, $D[0], $h, 'Bab 4');
        foreach ([70, 45, 15] as $h) $catat($s2, $D[3], $h, 'Bab 4');
        foreach ([$D[0], $D[3]] as $d) Persetujuan::create(['id_skripsi' => $s2->id_skripsi, 'id_dosen' => $d->id_dosen, 'jenis' => 'seminar_proposal', 'status' => 'berlaku', 'tanggal_persetujuan' => now()->subDays(110)]);
        Ujian::create(['id_skripsi' => $s2->id_skripsi, 'jenis' => 'seminar_proposal', 'tanggal_pelaksanaan' => today()->subDays(92), 'hasil' => 'lulus_dengan_revisi', 'id_admin' => 1, 'tanggal_input' => now()->subDays(90)]);
        Dokumen::create(['id_skripsi' => $s2->id_skripsi, 'bagian' => 'bab4', 'versi' => 1, 'nama_file' => 'Bab4_Citra.pdf', 'path_file' => $this->pdf('Bab 4 versi 1'), 'ukuran_kb' => 1, 'tanggal_unggah' => now()->subDays(20)]);

        // 3: Dimas - Seminar Proposal (kedua pembimbing sudah setuju) => siap ujian
        $s3 = $buatSkripsi(3, 3, 140, 5, [$D[2], $D[4]]);
        foreach ([120, 100, 80, 60, 30] as $h) { $catat($s3, $D[2], $h, 'Proposal'); $catat($s3, $D[4], $h - 1, 'Proposal'); }
        foreach ([$D[2], $D[4]] as $d) Persetujuan::create(['id_skripsi' => $s3->id_skripsi, 'id_dosen' => $d->id_dosen, 'jenis' => 'seminar_proposal', 'status' => 'berlaku', 'tanggal_persetujuan' => now()->subDays(5)]);

        // 4: Eka - Penyusunan Proposal, lama tanpa bimbingan, semester tinggi => perlu ditinjau
        $s4 = $buatSkripsi(4, 2, 200, 200, [$D[3], $D[1]]);
        $catat($s4, $D[3], 75, 'Bab 1');
        ItemRevisi::create(['id_skripsi' => $s4->id_skripsi, 'id_dosen' => $D[3]->id_dosen, 'bab' => 'Bab 1', 'uraian' => 'Rumusan masalah belum operasional.', 'status' => 'belum', 'tanggal_dibuat' => now()->subDays(75)]);

        // 5: Fajar - selesai
        $s5 = $buatSkripsi(5, 6, 420, 30, [$D[0], $D[2]]);
        foreach ([400, 350, 300, 250, 200, 150, 100, 60] as $h) { $catat($s5, $D[0], $h, 'Naskah'); $catat($s5, $D[2], $h - 1, 'Naskah'); }
        Ujian::create(['id_skripsi' => $s5->id_skripsi, 'jenis' => 'sidang', 'tanggal_pelaksanaan' => today()->subDays(32), 'hasil' => 'lulus', 'id_admin' => 1, 'tanggal_input' => now()->subDays(30)]);

        // 6: Gita - perlu revisi (belum mengajukan ulang). 7: Hafiz - belum mengajukan.
        PengajuanJudul::create(['id_mahasiswa' => $M[6]->id_mahasiswa, 'id_periode' => $periode?->id_periode, 'judul' => $judul[6][0], 'latar_masalah' => $latar, 'kata_kunci' => $judul[6][1], 'status' => 'perlu_revisi', 'catatan_kaprodi' => 'Perjelas lokasi penelitian dan variabel budaya mutu.', 'id_kaprodi' => $kap->id_pengguna, 'tanggal_ajuan' => now()->subDays(8), 'tanggal_putusan' => now()->subDays(6)]);

        // ---- Slot & jadwal ----
        foreach ($D as $i => $d) {
            foreach ([2, 4, 7, 9] as $k => $h) {
                SlotBimbingan::create(['id_dosen' => $d->id_dosen, 'tanggal' => today()->addDays($h + $i % 2), 'jam_mulai' => ['09:00', '13:00', '10:00', '14:00'][$k],
                    'jam_selesai' => ['10:30', '14:30', '11:30', '15:30'][$k], 'mode' => $k % 2 ? 'daring' : 'luring',
                    'lokasi' => $k % 2 ? 'Google Meet (tautan dikirim dosen)' : 'Ruang Dosen Lt. 2', 'kuota' => 3, 'status' => 'buka']);
            }
        }
        $slot = SlotBimbingan::where('id_dosen', $D[1]->id_dosen)->orderBy('tanggal')->first();
        JadwalBimbingan::create(['id_skripsi' => $s0->id_skripsi, 'id_dosen' => $D[1]->id_dosen, 'id_slot' => $slot->id_slot, 'tanggal' => $slot->tanggal, 'jam' => $slot->jam_mulai, 'mode' => $slot->mode, 'rencana_bahasan' => 'Revisi Bab 1 dan kerangka Bab 3', 'status' => 'diajukan', 'tanggal_ajuan' => now()->subHours(5)]);
        $slot2 = SlotBimbingan::where('id_dosen', $D[0]->id_dosen)->orderBy('tanggal')->first();
        JadwalBimbingan::create(['id_skripsi' => $s2->id_skripsi, 'id_dosen' => $D[0]->id_dosen, 'id_slot' => $slot2->id_slot, 'tanggal' => $slot2->tanggal, 'jam' => $slot2->jam_mulai, 'mode' => $slot2->mode, 'rencana_bahasan' => 'Pembahasan hasil wawancara', 'status' => 'disetujui', 'tanggal_ajuan' => now()->subDays(1)]);

        // ---- Notifikasi awal ----
        Notifikasi::kirim($M[0]->id_pengguna, 'Draf selesai direview', 'Bab 1 v1 direview Pembimbing I: perlu revisi.', route('mahasiswa.dokumen'));
        Notifikasi::kirim($kap->id_pengguna, 'Pengajuan judul baru', 'Bagas Pratama mengajukan judul (pengajuan ke-2).', route('kaprodi.judul.index'));
        Notifikasi::kirim($D[1]->id_pengguna, 'Permintaan jadwal', 'Aisyah Nur Fadilah mengajukan jadwal bimbingan.', route('dosen.jadwal'));
    }
}

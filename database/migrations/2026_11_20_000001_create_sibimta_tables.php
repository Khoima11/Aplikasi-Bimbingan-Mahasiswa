<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Struktur tabel mengikuti Bagian I.1 Dokumen Perancangan SIBIMTA (22 tabel).
 * Tipe enum ditulis sebagai string berpanjang tetap agar portabel di PostgreSQL/MySQL;
 * nilai yang diizinkan dijaga oleh validasi aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $t) {
            $t->increments('id_pengguna');
            $t->string('nama', 100);
            $t->string('username', 30)->unique();
            $t->string('email', 100)->unique();
            $t->string('password', 255);
            $t->string('status', 10)->default('aktif');               // aktif / nonaktif
            $t->boolean('wajib_ganti_password')->default(true);
            $t->dateTime('terakhir_login')->nullable();
        });

        Schema::create('peran', function (Blueprint $t) {
            $t->increments('id_peran');
            $t->string('nama_peran', 20)->unique();                   // admin / kaprodi / dosen / mahasiswa
            $t->string('keterangan', 100)->nullable();
        });

        Schema::create('pengguna_peran', function (Blueprint $t) {
            $t->unsignedInteger('id_pengguna');
            $t->unsignedInteger('id_peran');
            $t->primary(['id_pengguna', 'id_peran']);
            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
            $t->foreign('id_peran')->references('id_peran')->on('peran')->cascadeOnDelete();
        });

        Schema::create('mahasiswa', function (Blueprint $t) {
            $t->increments('id_mahasiswa');
            $t->unsignedInteger('id_pengguna')->unique();
            $t->string('nim', 20)->unique();
            $t->smallInteger('angkatan');
            $t->string('no_hp', 15)->nullable();
            $t->string('status_mahasiswa', 10)->default('aktif');     // aktif / cuti / lulus / keluar
            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna');
        });

        Schema::create('dosen', function (Blueprint $t) {
            $t->increments('id_dosen');
            $t->unsignedInteger('id_pengguna')->unique();
            $t->string('nidn', 20)->unique();
            $t->string('jabatan_fungsional', 50)->nullable();
            $t->unsignedSmallInteger('kuota_pembimbing_1')->default(10);
            $t->unsignedSmallInteger('kuota_pembimbing_2')->default(10);
            $t->string('status_dosen', 15)->default('aktif');         // aktif / tugas_belajar / nonaktif
            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna');
        });

        Schema::create('periode', function (Blueprint $t) {
            $t->increments('id_periode');
            $t->string('nama_periode', 30)->unique();
            $t->date('tanggal_mulai');
            $t->date('tanggal_selesai');
            $t->string('status', 10)->default('selesai');             // aktif / selesai
        });

        Schema::create('tahapan', function (Blueprint $t) {
            $t->increments('id_tahapan');
            $t->unsignedSmallInteger('urutan')->unique();
            $t->string('nama_tahapan', 50);
            $t->unsignedSmallInteger('batas_hari')->nullable();
            $t->unsignedSmallInteger('min_bimbingan')->nullable();
            $t->boolean('butuh_persetujuan')->default(false);
        });

        Schema::create('pengaturan', function (Blueprint $t) {
            $t->increments('id_pengaturan');
            $t->string('kunci', 50)->unique();
            $t->string('nilai', 100);
            $t->string('keterangan', 255)->nullable();
        });

        Schema::create('pengajuan_judul', function (Blueprint $t) {
            $t->increments('id_pengajuan');
            $t->unsignedInteger('id_mahasiswa');
            $t->unsignedInteger('id_periode')->nullable();
            $t->unsignedInteger('id_pengajuan_asal')->nullable();
            $t->string('judul', 255);
            $t->text('latar_masalah')->nullable();
            $t->string('kata_kunci', 150)->nullable();
            $t->string('status', 15)->default('menunggu');            // menunggu / perlu_revisi / disetujui / ditolak
            $t->text('catatan_kaprodi')->nullable();
            $t->unsignedInteger('id_kaprodi')->nullable();
            $t->dateTime('tanggal_ajuan');
            $t->dateTime('tanggal_putusan')->nullable();
            $t->foreign('id_mahasiswa')->references('id_mahasiswa')->on('mahasiswa');
            $t->foreign('id_periode')->references('id_periode')->on('periode');
            $t->foreign('id_pengajuan_asal')->references('id_pengajuan')->on('pengajuan_judul');
            $t->foreign('id_kaprodi')->references('id_pengguna')->on('pengguna');
        });

        Schema::create('skripsi', function (Blueprint $t) {
            $t->increments('id_skripsi');
            $t->unsignedInteger('id_mahasiswa')->unique();
            $t->unsignedInteger('id_pengajuan')->unique();
            $t->unsignedInteger('id_tahapan');
            $t->string('judul_final', 255);
            $t->string('status_skripsi', 10)->default('aktif');       // aktif / selesai / berhenti
            $t->date('tanggal_mulai');
            $t->date('tanggal_masuk_tahap');
            $t->date('tanggal_selesai')->nullable();
            $t->foreign('id_mahasiswa')->references('id_mahasiswa')->on('mahasiswa');
            $t->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan_judul');
            $t->foreign('id_tahapan')->references('id_tahapan')->on('tahapan');
        });

        Schema::create('pembimbing', function (Blueprint $t) {
            $t->increments('id_pembimbing');
            $t->unsignedInteger('id_skripsi');
            $t->unsignedInteger('id_dosen');
            $t->unsignedSmallInteger('urutan');                        // 1 / 2
            $t->string('status', 10)->default('aktif');               // aktif / diganti
            $t->date('tanggal_penetapan');
            $t->date('tanggal_berakhir')->nullable();
            $t->string('alasan', 255)->nullable();
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
            $t->foreign('id_dosen')->references('id_dosen')->on('dosen');
        });

        Schema::create('slot_bimbingan', function (Blueprint $t) {
            $t->increments('id_slot');
            $t->unsignedInteger('id_dosen');
            $t->date('tanggal');
            $t->time('jam_mulai');
            $t->time('jam_selesai');
            $t->string('mode', 10)->default('luring');                // luring / daring
            $t->string('lokasi', 150)->nullable();
            $t->unsignedSmallInteger('kuota')->default(1);
            $t->string('status', 10)->default('buka');                // buka / tutup
            $t->foreign('id_dosen')->references('id_dosen')->on('dosen');
        });

        Schema::create('jadwal_bimbingan', function (Blueprint $t) {
            $t->increments('id_jadwal');
            $t->unsignedInteger('id_skripsi');
            $t->unsignedInteger('id_dosen');
            $t->unsignedInteger('id_slot')->nullable();
            $t->date('tanggal');
            $t->time('jam');
            $t->string('mode', 10)->default('luring');
            $t->string('rencana_bahasan', 255)->nullable();
            $t->string('status', 20)->default('diajukan');            // diajukan / disetujui / diusulkan_ulang / ditolak / dibatalkan / selesai
            $t->string('catatan_dosen', 255)->nullable();
            $t->dateTime('tanggal_ajuan');
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
            $t->foreign('id_dosen')->references('id_dosen')->on('dosen');
            $t->foreign('id_slot')->references('id_slot')->on('slot_bimbingan')->nullOnDelete();
        });

        Schema::create('catatan_bimbingan', function (Blueprint $t) {
            $t->increments('id_catatan');
            $t->unsignedInteger('id_skripsi');
            $t->unsignedInteger('id_dosen');
            $t->unsignedInteger('id_jadwal')->nullable()->unique();
            $t->date('tanggal_bimbingan');
            $t->string('mode', 10)->default('luring');
            $t->string('bab_dibahas', 50);
            $t->text('ringkasan');
            $t->string('status_validasi', 12)->default('menunggu');   // menunggu / divalidasi / dikoreksi
            $t->text('catatan_dosen')->nullable();
            $t->dateTime('tanggal_input');
            $t->dateTime('tanggal_validasi')->nullable();
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
            $t->foreign('id_dosen')->references('id_dosen')->on('dosen');
            $t->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_bimbingan');
        });

        Schema::create('dokumen', function (Blueprint $t) {
            $t->increments('id_dokumen');
            $t->unsignedInteger('id_skripsi');
            $t->string('bagian', 20);                                  // proposal / bab1..bab5 / naskah_lengkap
            $t->unsignedSmallInteger('versi');
            $t->string('nama_file', 150);
            $t->string('path_file', 255);
            $t->unsignedInteger('ukuran_kb');
            $t->string('catatan_mahasiswa', 255)->nullable();
            $t->dateTime('tanggal_unggah');
            $t->unique(['id_skripsi', 'bagian', 'versi']);
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
        });

        Schema::create('review_dokumen', function (Blueprint $t) {
            $t->increments('id_review');
            $t->unsignedInteger('id_dokumen');
            $t->unsignedInteger('id_dosen');
            $t->string('hasil', 10);                                   // revisi / disetujui
            $t->text('komentar')->nullable();
            $t->string('file_koreksi', 255)->nullable();
            $t->dateTime('tanggal_review');
            $t->unique(['id_dokumen', 'id_dosen']);
            $t->foreign('id_dokumen')->references('id_dokumen')->on('dokumen');
            $t->foreign('id_dosen')->references('id_dosen')->on('dosen');
        });

        Schema::create('item_revisi', function (Blueprint $t) {
            $t->increments('id_revisi');
            $t->unsignedInteger('id_skripsi');
            $t->unsignedInteger('id_dosen');
            $t->unsignedInteger('id_catatan')->nullable();
            $t->unsignedInteger('id_review')->nullable();
            $t->string('bab', 30);
            $t->text('uraian');
            $t->string('status', 12)->default('belum');               // belum / dikerjakan / diterima
            $t->string('keterangan_mahasiswa', 255)->nullable();
            $t->unsignedInteger('id_dokumen_perbaikan')->nullable();
            $t->dateTime('tanggal_dibuat');
            $t->dateTime('tanggal_dikerjakan')->nullable();
            $t->dateTime('tanggal_diterima')->nullable();
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
            $t->foreign('id_dosen')->references('id_dosen')->on('dosen');
            $t->foreign('id_catatan')->references('id_catatan')->on('catatan_bimbingan');
            $t->foreign('id_review')->references('id_review')->on('review_dokumen');
            $t->foreign('id_dokumen_perbaikan')->references('id_dokumen')->on('dokumen');
        });

        Schema::create('persetujuan', function (Blueprint $t) {
            $t->increments('id_persetujuan');
            $t->unsignedInteger('id_skripsi');
            $t->unsignedInteger('id_dosen');
            $t->string('jenis', 20);                                   // seminar_proposal / sidang
            $t->string('status', 12)->default('berlaku');             // berlaku / dibatalkan
            $t->string('catatan', 255)->nullable();
            $t->dateTime('tanggal_persetujuan');
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
            $t->foreign('id_dosen')->references('id_dosen')->on('dosen');
        });

        Schema::create('ujian', function (Blueprint $t) {
            $t->increments('id_ujian');
            $t->unsignedInteger('id_skripsi');
            $t->string('jenis', 20);                                   // seminar_proposal / sidang
            $t->date('tanggal_pelaksanaan');
            $t->string('hasil', 20);                                   // lulus / lulus_dengan_revisi / tidak_lulus
            $t->string('catatan', 255)->nullable();
            $t->date('tanggal_revisi_selesai')->nullable();
            $t->unsignedInteger('id_admin');
            $t->dateTime('tanggal_input');
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
            $t->foreign('id_admin')->references('id_pengguna')->on('pengguna');
        });

        Schema::create('notifikasi', function (Blueprint $t) {
            $t->increments('id_notifikasi');
            $t->unsignedInteger('id_pengguna');
            $t->string('judul', 100);
            $t->string('pesan', 255);
            $t->string('tautan', 255)->nullable();
            $t->boolean('sudah_dibaca')->default(false);
            $t->dateTime('tanggal_kirim');
            $t->index(['id_pengguna', 'sudah_dibaca']);
            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
        });

        Schema::create('lembar_bimbingan', function (Blueprint $t) {
            $t->increments('id_lembar');
            $t->unsignedInteger('id_skripsi');
            $t->string('kode_verifikasi', 40)->unique();
            $t->unsignedSmallInteger('jumlah_bimbingan');
            $t->string('tahap_saat_cetak', 50);
            $t->unsignedInteger('id_pencetak');
            $t->dateTime('tanggal_cetak');
            $t->foreign('id_skripsi')->references('id_skripsi')->on('skripsi');
            $t->foreign('id_pencetak')->references('id_pengguna')->on('pengguna');
        });

        Schema::create('log_aktivitas', function (Blueprint $t) {
            $t->increments('id_log');
            $t->unsignedInteger('id_pengguna')->nullable();
            $t->string('aktivitas', 100);
            $t->string('tabel_terkait', 50)->nullable();
            $t->unsignedInteger('id_data')->nullable();
            $t->string('alamat_ip', 45)->nullable();
            $t->dateTime('waktu');
            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        // ---- Tabel teknis (bukan bagian model data bisnis) ----
        // Penyimpanan isi berkas. Hosting gratis tidak memiliki disk permanen,
        // sehingga isi berkas (base64) disimpan di database; path_file berisi "db:{id}".
        Schema::create('berkas', function (Blueprint $t) {
            $t->increments('id_berkas');
            $t->string('mime', 120);
            $t->unsignedInteger('ukuran_byte');
            $t->longText('isi_base64');
        });

        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->unsignedInteger('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        foreach (['sessions', 'berkas', 'log_aktivitas', 'lembar_bimbingan', 'notifikasi', 'ujian', 'persetujuan',
            'item_revisi', 'review_dokumen', 'dokumen', 'catatan_bimbingan', 'jadwal_bimbingan', 'slot_bimbingan',
            'pembimbing', 'skripsi', 'pengajuan_judul', 'pengaturan', 'tahapan', 'periode', 'dosen', 'mahasiswa',
            'pengguna_peran', 'peran', 'pengguna'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

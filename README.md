# SIBIMTA — Sistem Informasi Bimbingan Skripsi

Aplikasi web responsif (laptop dan HP) untuk pengajuan judul, bimbingan, revisi, draf berversi,
persetujuan maju ujian, dan pemantauan progres skripsi. Dibangun dari Dokumen Perancangan SIBIMTA
(Kelompok 2) dengan Laravel 13, PostgreSQL, Tailwind CSS, Alpine.js, dan Chart.js.

## Fitur per peran

| Peran | Yang bisa dilakukan |
|---|---|
| Mahasiswa | Ajukan judul (dengan pencarian judul serupa), pilih slot jadwal pembimbing, isi catatan bimbingan, tandai revisi dikerjakan, unggah draf berversi, lihat hasil review, unduh lembar bimbingan PDF ber-QR |
| Dosen | Buka slot ketersediaan, setujui/tolak/usulkan ulang jadwal, validasi atau koreksi catatan, kelola item revisi, review draf + berkas koreksi, beri persetujuan maju seminar/sidang, halaman "Perlu tindakan" untuk HP |
| Kaprodi | Verifikasi judul, tetapkan/ganti Pembimbing I & II dengan cek kuota, dashboard pemantauan + peringatan "Perlu ditinjau", laporan (PDF) |
| Admin Prodi | Kelola pengguna & peran (termasuk impor Excel/CSV), periode, tahapan, pengaturan, catat hasil ujian, log aktivitas |
| Panitia ujian (tanpa login) | Pindai QR lembar bimbingan di `/verifikasi` |

Satu akun bisa punya beberapa peran (misalnya Kaprodi sekaligus Dosen) dan berpindah lewat menu "Sebagai ...".

## Akun contoh (bila `SEED_DEMO=true`)

Kata sandi semua akun = nilai `DEMO_PASSWORD`.

| Peran | Username |
|---|---|
| Admin Prodi | `admin` |
| Kaprodi + Dosen | `197501012000031001` |
| Dosen | `198003152006042002` |
| Mahasiswa (tahap proposal) | `2205108001` |
| Mahasiswa (judul menunggu keputusan) | `2205108002` |
| Mahasiswa (belum mengajukan judul) | `2305108008` |

## Online gratis: Render + Neon (sekitar 15 menit)

Yang dibutuhkan: akun GitHub, akun [Neon](https://neon.com) (database), dan akun [Render](https://render.com) (server). Semua bisa daftar memakai akun GitHub.

1. **Taruh kode di GitHub.** Buat repositori baru lalu unggah seluruh isi folder ini.
2. **Buat database di Neon.** New Project → nama `sibimta`, region **AWS Asia Pacific (Singapore)** → buka *Connect* →
   matikan *Connection pooling* → salin connection string (`postgresql://...?...sslmode=require`).
3. **Buat aplikasi di Render.** Dashboard → **New → Blueprint** → pilih repositori ini. Render membaca `render.yaml`
   dan meminta dua nilai:
   - `DB_URL` → tempel connection string dari Neon.
   - `DEMO_PASSWORD` → kata sandi untuk akun `admin` dan akun contoh (buat yang kuat).
4. Klik **Apply**. Build pertama sekitar 5–10 menit. Setelah status *Live*, buka alamat `https://sibimta-xxxx.onrender.com`.
   Tabel database dan data awal dibuat otomatis.

### Hal yang perlu diketahui tentang paket gratis

- Server Render gratis **tidur setelah 15 menit tanpa pengunjung**. Pengunjung berikutnya menunggu sekitar 1 menit
  sampai server bangun; setelah itu normal.
- Neon gratis menyediakan 1 GB per proyek dan tidak kedaluwarsa. Berkas draf disimpan di database (karena disk Render
  gratis terhapus setiap restart), jadi batasi ukuran berkas di *Periode & tahapan → Pengaturan sistem* bila perlu.
- Kuota bandwidth Render paket Hobby 5 GB/bulan, cukup untuk uji coba.

### Memakai data asli (tanpa data contoh)

Sebelum deploy pertama, ubah `SEED_DEMO` menjadi `false` di Render (Environment). Hanya akun `admin` yang dibuat.
Masuk sebagai admin, lalu impor dosen dan mahasiswa dari menu **Pengguna → Impor dari Excel** (templat tersedia di sana).
Untuk menghapus data contoh yang sudah terlanjur dibuat: di Neon, hapus lalu buat ulang database, ubah `SEED_DEMO=false`,
lalu *Manual Deploy* di Render.

## Menjalankan di komputer sendiri

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
# siapkan database PostgreSQL bernama sibimta, atau isi DB_URL
php artisan migrate --seed
php artisan serve        # buka http://127.0.0.1:8000
```

Tes otomatis aturan bisnis (butuh database `sibimta_test`): `php artisan test`.

## Struktur penting

- `database/migrations` — 22 tabel sesuai Bagian I.1 dokumen perancangan (+ tabel teknis `berkas` dan `sessions`).
- `app/Services/TahapanService.php` — gerbang persetujuan dan perpindahan tahap (AB-04, AB-12, AB-13).
- `app/Services/Peringatan.php` — indikator Perlu Ditinjau PD-01 s.d. PD-05.
- `app/Services/JudulSerupa.php` — pencarian judul serupa (AB-03).
- `Dockerfile`, `docker/`, `render.yaml` — konfigurasi server produksi.

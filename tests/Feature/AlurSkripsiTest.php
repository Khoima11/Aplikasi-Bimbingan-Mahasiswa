<?php

namespace Tests\Feature;

use App\Models\CatatanBimbingan;
use App\Models\Dokumen;
use App\Models\Dosen;
use App\Models\ItemRevisi;
use App\Models\JadwalBimbingan;
use App\Models\LembarBimbingan;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Skripsi;
use App\Models\SlotBimbingan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AlurSkripsiTest extends TestCase
{
    use RefreshDatabase;
    protected bool $seed = true;

    private function akun(string $username, array $peran, bool $wajibGanti = false): Pengguna
    {
        $u = Pengguna::create(['nama' => 'Pengguna '.$username, 'username' => $username, 'email' => $username.'@t.id', 'password' => 'rahasia123', 'status' => 'aktif', 'wajib_ganti_password' => $wajibGanti]);
        $u->peran()->attach(Peran::whereIn('nama_peran', $peran)->pluck('id_peran'));
        if (in_array('dosen', $peran)) Dosen::create(['id_pengguna' => $u->id_pengguna, 'nidn' => $username, 'kuota_pembimbing_1' => 1, 'kuota_pembimbing_2' => 2]);
        if (in_array('mahasiswa', $peran)) Mahasiswa::create(['id_pengguna' => $u->id_pengguna, 'nim' => $username, 'angkatan' => 2022]);
        return $u->fresh();
    }

    private function sebagai(Pengguna $u, string $peran) { return $this->actingAs($u->fresh())->withSession(['peran_aktif' => $peran]); }

    public function test_login_dan_wajib_ganti_kata_sandi(): void
    {
        $u = $this->akun('M001', ['mahasiswa'], true);
        $this->post('/login', ['username' => 'M001', 'password' => 'salah'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => 'M001', 'password' => 'rahasia123'])->assertRedirect(route('beranda'));
        $this->get(route('mahasiswa.beranda'))->assertRedirect(route('password.form'));
        $this->post(route('password.update'), ['password_lama' => 'rahasia123', 'password' => 'BaruSekali1', 'password_confirmation' => 'BaruSekali1'])->assertRedirect(route('beranda'));
        $this->assertFalse($u->fresh()->wajib_ganti_password);
        $this->get(route('mahasiswa.beranda'))->assertOk();
    }

    public function test_akun_nonaktif_ditolak(): void
    {
        $u = $this->akun('M002', ['mahasiswa']);
        $u->update(['status' => 'nonaktif']);
        $this->post('/login', ['username' => 'M002', 'password' => 'rahasia123'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_alur_lengkap_dari_judul_sampai_seminar(): void
    {
        $kap = $this->akun('K001', ['kaprodi', 'dosen']);
        $d2 = $this->akun('D002', ['dosen']);
        $admin = Pengguna::where('username', 'admin')->first();
        $m = $this->akun('M100', ['mahasiswa']);

        // Pengajuan judul (AB-01: tidak boleh dua pengajuan menunggu)
        $data = ['judul' => 'Manajemen Kurikulum Merdeka pada Madrasah Aliyah', 'latar_masalah' => str_repeat('Latar masalah penelitian. ', 4), 'kata_kunci' => 'kurikulum merdeka, madrasah'];
        $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.judul.store'), $data)->assertSessionHasNoErrors();
        $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.judul.store'), $data)->assertSessionHas('gagal');
        $p = PengajuanJudul::firstOrFail();

        // Mahasiswa tidak boleh membuka halaman kaprodi
        $this->sebagai($m, 'mahasiswa')->get(route('kaprodi.judul.show', $p))->assertForbidden();

        // AB-05: pembimbing sama ditolak; kuota penuh butuh alasan
        $this->sebagai($kap, 'kaprodi')->post(route('kaprodi.judul.putuskan', $p), ['keputusan' => 'disetujui', 'pembimbing_1' => $kap->dosen->id_dosen, 'pembimbing_2' => $kap->dosen->id_dosen])->assertSessionHasErrors('pembimbing_2');
        $this->sebagai($kap, 'kaprodi')->post(route('kaprodi.judul.putuskan', $p), ['keputusan' => 'perlu_revisi'])->assertSessionHasErrors('catatan_kaprodi');
        $this->sebagai($kap, 'kaprodi')->post(route('kaprodi.judul.putuskan', $p), ['keputusan' => 'disetujui', 'pembimbing_1' => $kap->dosen->id_dosen, 'pembimbing_2' => $d2->dosen->id_dosen])->assertSessionHas('ok');
        $s = Skripsi::firstOrFail();
        $this->assertSame(2, $s->tahapan->urutan);
        $this->assertCount(2, $s->pembimbing);

        // Kuota P1 kaprodi = 1 kini penuh: mahasiswa lain butuh alasan
        $m2 = $this->akun('M101', ['mahasiswa']);
        $p2 = PengajuanJudul::create(['id_mahasiswa' => $m2->mahasiswa->id_mahasiswa, 'judul' => 'Judul lain yang cukup panjang', 'latar_masalah' => 'x', 'kata_kunci' => 'x', 'status' => 'menunggu', 'tanggal_ajuan' => now()]);
        $this->sebagai($kap, 'kaprodi')->post(route('kaprodi.judul.putuskan', $p2), ['keputusan' => 'disetujui', 'pembimbing_1' => $kap->dosen->id_dosen, 'pembimbing_2' => $d2->dosen->id_dosen])->assertSessionHas('gagal');
        $this->assertSame('menunggu', $p2->fresh()->status);

        // Slot dan jadwal (AB-07)
        $slot = SlotBimbingan::create(['id_dosen' => $d2->dosen->id_dosen, 'tanggal' => today()->addDays(3), 'jam_mulai' => '09:00', 'jam_selesai' => '10:00', 'mode' => 'luring', 'kuota' => 1, 'status' => 'buka']);
        $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.jadwal.store'), ['id_slot' => $slot->id_slot, 'rencana_bahasan' => 'Bab 1'])->assertSessionHas('ok');
        $j = JadwalBimbingan::firstOrFail();
        $this->sebagai($m2, 'mahasiswa')->post(route('mahasiswa.jadwal.store'), ['id_slot' => $slot->id_slot, 'rencana_bahasan' => 'x'])->assertSessionHas('gagal');
        $this->sebagai($kap, 'dosen')->post(route('dosen.jadwal.setujui', $j))->assertForbidden();
        $this->sebagai($d2, 'dosen')->post(route('dosen.jadwal.setujui', $j))->assertSessionHas('ok');
        $this->assertSame('disetujui', $j->fresh()->status);

        // Persetujuan diblokir sebelum minimal bimbingan (AB-12)
        $this->sebagai($d2, 'dosen')->post(route('dosen.persetujuan', $s))->assertSessionHas('gagal');

        // Catatan bimbingan: diisi mahasiswa, divalidasi dosen (AB-08)
        $min = $s->tahapan->min_bimbingan;
        foreach ([$kap, $d2] as $d) {
            for ($i = 0; $i < $min; $i++) {
                $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.catatan.store'), ['id_dosen' => $d->dosen->id_dosen, 'tanggal_bimbingan' => today()->toDateString(), 'mode' => 'luring', 'bab_dibahas' => 'Bab 1', 'ringkasan' => 'Membahas rumusan masalah dan teori utama.'])->assertSessionHas('ok');
            }
        }
        $this->assertSame(0, $s->jumlahTervalidasi());
        $catatan = CatatanBimbingan::all();
        $koreksi = $catatan->first();
        $this->sebagai($kap, 'dosen')->post(route('dosen.catatan.koreksi', $koreksi), ['catatan_dosen' => 'Lengkapi.'])->assertSessionHas('ok');
        $this->sebagai($m, 'mahasiswa')->put(route('mahasiswa.catatan.update', $koreksi), ['bab_dibahas' => 'Bab 1', 'ringkasan' => 'Ringkasan yang sudah dilengkapi dengan arahan.'])->assertSessionHas('ok');
        foreach (CatatanBimbingan::all() as $c) {
            $dosen = $c->id_dosen === $kap->dosen->id_dosen ? $kap : $d2;
            $this->sebagai($d2, 'dosen')->post(route('dosen.catatan.validasi', $c))->assertStatus($dosen->is($d2) ? 302 : 403);
            if (! $dosen->is($d2)) $this->sebagai($kap, 'dosen')->post(route('dosen.catatan.validasi', $c))->assertSessionHas('ok');
        }
        $this->assertSame($min * 2, $s->jumlahTervalidasi());

        // Persetujuan kedua pembimbing memindahkan tahap
        $this->sebagai($kap, 'dosen')->post(route('dosen.persetujuan', $s))->assertSessionHas('ok');
        $this->assertSame(2, $s->fresh()->tahapan->urutan);
        $this->sebagai($d2, 'dosen')->post(route('dosen.persetujuan', $s))->assertSessionHas('ok');
        $this->assertSame(3, $s->fresh()->tahapan->urutan);

        // Admin: tidak lulus mengembalikan tahap dan membatalkan persetujuan (AB-13)
        $this->sebagai($admin, 'admin')->post(route('admin.ujian.store', $s), ['jenis' => 'seminar_proposal', 'tanggal_pelaksanaan' => today()->toDateString(), 'hasil' => 'tidak_lulus'])->assertRedirect();
        $this->assertSame(2, $s->fresh()->tahapan->urutan);
        $this->assertSame(0, $s->persetujuan()->where('status', 'berlaku')->count());
        // Catatan lama tidak dihitung pada tahap baru? Tanggal masuk tahap = hari ini, catatan hari ini tetap dihitung.
        $this->sebagai($kap, 'dosen')->post(route('dosen.persetujuan', $s->fresh()));
        $this->sebagai($d2, 'dosen')->post(route('dosen.persetujuan', $s->fresh()));
        $this->assertSame(3, $s->fresh()->tahapan->urutan);
        $this->sebagai($admin, 'admin')->post(route('admin.ujian.store', $s), ['jenis' => 'seminar_proposal', 'tanggal_pelaksanaan' => today()->toDateString(), 'hasil' => 'lulus'])->assertRedirect();
        $this->assertSame(4, $s->fresh()->tahapan->urutan);
        // Jenis ujian salah ditolak
        $this->sebagai($admin, 'admin')->post(route('admin.ujian.store', $s), ['jenis' => 'seminar_proposal', 'tanggal_pelaksanaan' => today()->toDateString(), 'hasil' => 'lulus'])->assertSessionHas('gagal');
    }

    public function test_revisi_dokumen_akses_dan_lembar(): void
    {
        $kap = $this->akun('K001', ['kaprodi', 'dosen']);
        $d2 = $this->akun('D002', ['dosen']);
        $m = $this->akun('M100', ['mahasiswa']);
        $lain = $this->akun('M200', ['mahasiswa']);
        $p = PengajuanJudul::create(['id_mahasiswa' => $m->mahasiswa->id_mahasiswa, 'judul' => 'Judul uji yang cukup panjang', 'latar_masalah' => 'x', 'kata_kunci' => 'uji', 'status' => 'menunggu', 'tanggal_ajuan' => now()]);
        $this->sebagai($kap, 'kaprodi')->post(route('kaprodi.judul.putuskan', $p), ['keputusan' => 'disetujui', 'pembimbing_1' => $kap->dosen->id_dosen, 'pembimbing_2' => $d2->dosen->id_dosen]);
        $s = Skripsi::firstOrFail();

        // Unggah dokumen berversi (AB-10) + validasi tipe
        $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.dokumen.store'), ['bagian' => 'bab1', 'berkas' => UploadedFile::fake()->create('a.exe', 10)])->assertSessionHasErrors('berkas');
        $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.dokumen.store'), ['bagian' => 'bab1', 'berkas' => UploadedFile::fake()->createWithContent('bab1.pdf', "%PDF-1.4 isi")])->assertSessionHas('ok');
        $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.dokumen.store'), ['bagian' => 'bab1', 'berkas' => UploadedFile::fake()->createWithContent('bab1b.pdf', "%PDF-1.4 isi kedua")])->assertSessionHas('ok');
        $dok = Dokumen::orderByDesc('versi')->first();
        $this->assertSame(2, $dok->versi);

        // Hak akses berkas (AB-11)
        $this->sebagai($m, 'mahasiswa')->get(route('berkas.dokumen', $dok))->assertOk()->assertSee('isi kedua');
        $this->sebagai($d2, 'dosen')->get(route('berkas.dokumen', $dok))->assertOk();
        $this->sebagai($lain, 'mahasiswa')->get(route('berkas.dokumen', $dok))->assertForbidden();
        $admin = Pengguna::where('username', 'admin')->first();
        $this->sebagai($admin, 'admin')->get(route('berkas.dokumen', $dok))->assertForbidden();

        // Review membuat item revisi; mahasiswa menandai; dosen menerima (AB-09)
        $this->sebagai($d2, 'dosen')->post(route('dosen.review.store', $dok), ['hasil' => 'revisi', 'komentar' => 'Perbaiki', 'revisi' => ['Perjelas latar belakang', '']])->assertRedirect();
        $i = ItemRevisi::firstOrFail();
        $this->assertSame('belum', $i->status);
        $this->sebagai($lain, 'mahasiswa')->post(route('mahasiswa.revisi.tandai', $i))->assertForbidden();
        $this->sebagai($m, 'mahasiswa')->post(route('mahasiswa.revisi.tandai', $i), ['id_dokumen_perbaikan' => $dok->id_dokumen])->assertSessionHas('ok');
        $this->sebagai($kap, 'dosen')->post(route('dosen.revisi.terima', $i))->assertForbidden();
        $this->sebagai($d2, 'dosen')->post(route('dosen.revisi.terima', $i))->assertSessionHas('ok');
        $this->assertSame('diterima', $i->fresh()->status);

        // Lembar bimbingan PDF + verifikasi publik (AB-15)
        $this->sebagai($m, 'mahasiswa')->get(route('lembar.pdf', $s))->assertOk()->assertHeader('content-type', 'application/pdf');
        $kode = LembarBimbingan::firstOrFail()->kode_verifikasi;
        auth()->logout();
        $this->get(route('verifikasi', $kode))->assertOk()->assertSee('Lembar bimbingan asli')->assertDontSee('Membahas');
        $this->get(route('verifikasi', 'ZZZZ-ZZZZ-ZZZZ'))->assertOk()->assertSee('Kode tidak ditemukan');
        $this->sebagai($lain, 'mahasiswa')->get(route('lembar', $s))->assertForbidden();
    }

    public function test_judul_serupa_dan_laporan(): void
    {
        $kap = $this->akun('K001', ['kaprodi', 'dosen']);
        $m = $this->akun('M100', ['mahasiswa']);
        $m2 = $this->akun('M101', ['mahasiswa']);
        PengajuanJudul::create(['id_mahasiswa' => $m2->mahasiswa->id_mahasiswa, 'judul' => 'Manajemen Kurikulum Merdeka di Madrasah Tsanawiyah', 'latar_masalah' => 'x', 'kata_kunci' => 'kurikulum merdeka, madrasah', 'status' => 'disetujui', 'tanggal_ajuan' => now()]);
        $this->sebagai($m, 'mahasiswa')->getJson(route('mahasiswa.judul.serupa', ['judul' => 'Evaluasi Kurikulum Merdeka di Madrasah', 'kata_kunci' => 'kurikulum merdeka']))
            ->assertOk()->assertJsonFragment(['judul' => 'Manajemen Kurikulum Merdeka di Madrasah Tsanawiyah']);
        foreach (['tahapan', 'dosen', 'ditinjau', 'lama'] as $tab) {
            $this->sebagai($kap, 'kaprodi')->get(route('kaprodi.laporan', ['tab' => $tab]))->assertOk();
            $this->sebagai($kap, 'kaprodi')->get(route('kaprodi.laporan.pdf', ['tab' => $tab]))->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_impor_pengguna_dari_csv(): void
    {
        $admin = Pengguna::where('username', 'admin')->first();
        $csv = "nim,nama,email,angkatan,no_hp\n2405100001,Satu Mahasiswa,satu@t.id,2024,0811\n2405100002,Dua Mahasiswa,,2024,\nadmin,Duplikat,dup@t.id,2024,\n";
        $this->sebagai($admin, 'admin')->post(route('admin.pengguna.impor'), ['jenis' => 'mahasiswa', 'berkas' => UploadedFile::fake()->createWithContent('m.csv', $csv)])->assertSessionHas('ok');
        $this->assertSame(2, Mahasiswa::count());
        $u = Pengguna::where('username', '2405100001')->first();
        $this->assertTrue($u->wajib_ganti_password);
        $this->assertTrue($u->punyaPeran('mahasiswa'));
    }
}

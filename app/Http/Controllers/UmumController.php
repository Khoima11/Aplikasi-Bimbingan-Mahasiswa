<?php

namespace App\Http\Controllers;

use App\Models\CatatanBimbingan;
use App\Models\Dokumen;
use App\Models\LembarBimbingan;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Pengaturan;
use App\Models\ReviewDokumen;
use App\Models\Skripsi;
use App\Services\Akses;
use App\Services\Penyimpanan;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UmumController extends Controller
{
    public const BERANDA = ['admin' => 'admin.dashboard', 'kaprodi' => 'kaprodi.dashboard', 'dosen' => 'dosen.dashboard', 'mahasiswa' => 'mahasiswa.beranda'];

    public function beranda()
    {
        $p = session('peran_aktif');
        if (! $p || ! isset(self::BERANDA[$p])) abort(403, 'Akun ini belum memiliki peran. Hubungi admin.');
        return redirect()->route(self::BERANDA[$p]);
    }

    public function gantiPeran(Request $r)
    {
        $p = $r->input('peran');
        abort_unless($this->pengguna()->punyaPeran($p), 403);
        session(['peran_aktif' => $p]);
        return redirect()->route(self::BERANDA[$p]);
    }

    public function akun() { return view('umum.akun', ['u' => $this->pengguna()->load('mahasiswa', 'dosen', 'peran')]); }

    public function notifikasi()
    {
        $items = $this->pengguna()->notifikasi()->orderByDesc('id_notifikasi')->paginate(20);
        return view('umum.notifikasi', compact('items'));
    }

    public function bacaNotifikasi(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->id_pengguna === auth()->id(), 403);
        $notifikasi->update(['sudah_dibaca' => true]);
        return $notifikasi->tautan ? redirect($notifikasi->tautan) : redirect()->route('notifikasi');
    }

    public function bacaSemua()
    {
        $this->pengguna()->notifikasi()->where('sudah_dibaca', false)->update(['sudah_dibaca' => true]);
        return $this->ok('Semua notifikasi ditandai dibaca.');
    }

    public function unduhDokumen(Dokumen $dokumen)
    {
        Akses::pastikan($dokumen->skripsi);
        $ext = pathinfo($dokumen->nama_file, PATHINFO_EXTENSION);
        $nama = $dokumen->skripsi->mahasiswa->nim.'_'.$dokumen->bagian.'_v'.$dokumen->versi.($ext ? '.'.$ext : '');
        return Penyimpanan::unduh($dokumen->path_file, $nama);
    }

    public function unduhKoreksi(ReviewDokumen $review)
    {
        Akses::pastikan($review->dokumen->skripsi);
        abort_unless($review->file_koreksi, 404);
        return Penyimpanan::unduh($review->file_koreksi, 'koreksi_'.$review->dokumen->bagian.'_v'.$review->dokumen->versi.'_'.Str::slug($review->dosen->pengguna->nama).'.'.(str_contains($review->file_koreksi, 'pdf') ? 'pdf' : 'docx'));
    }

    // -------- Lembar bimbingan (AB-08, AB-15, KF-22) --------
    private function dataLembar(Skripsi $s): array
    {
        Akses::pastikan($s);
        $s->load(['mahasiswa.pengguna', 'pembimbing.dosen.pengguna', 'tahapan']);
        $catatan = CatatanBimbingan::with('dosen.pengguna')->where('id_skripsi', $s->id_skripsi)
            ->where('status_validasi', 'divalidasi')->orderBy('tanggal_bimbingan')->get();
        return [$s, $catatan];
    }

    public function lembar(Skripsi $skripsi)
    {
        [$s, $catatan] = $this->dataLembar($skripsi);
        $riwayat = $s->lembar()->with('pencetak')->orderByDesc('id_lembar')->limit(10)->get();
        return view('umum.lembar', compact('s', 'catatan', 'riwayat'));
    }

    public function lembarPdf(Skripsi $skripsi)
    {
        [$s, $catatan] = $this->dataLembar($skripsi);
        $lembar = LembarBimbingan::create([
            'id_skripsi' => $s->id_skripsi, 'kode_verifikasi' => strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4)),
            'jumlah_bimbingan' => $catatan->count(), 'tahap_saat_cetak' => $s->tahapan->nama_tahapan,
            'id_pencetak' => auth()->id(), 'tanggal_cetak' => now(),
        ]);
        LogAktivitas::catat('Mencetak lembar bimbingan', 'lembar_bimbingan', $lembar->id_lembar);
        $url = route('verifikasi', $lembar->kode_verifikasi);
        $qr = (new QRCode(new QROptions(['outputInterface' => QRGdImagePNG::class, 'scale' => 6, 'outputBase64' => true])))->render($url);
        $pdf = Pdf::loadView('pdf.lembar', compact('s', 'catatan', 'lembar', 'qr', 'url'))->setPaper('a4');
        return $pdf->download('Lembar_Bimbingan_'.$s->mahasiswa->nim.'.pdf');
    }

    public function verifikasi(Request $r, ?string $kode = null)
    {
        $kode = strtoupper(trim($kode ?? $r->query('kode', '')));
        $lembar = $kode ? LembarBimbingan::with('skripsi.mahasiswa.pengguna')->where('kode_verifikasi', $kode)->first() : null;
        return view('umum.verifikasi', compact('kode', 'lembar'));
    }
}

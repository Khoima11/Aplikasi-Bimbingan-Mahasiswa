<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\ItemRevisi;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\ReviewDokumen;
use App\Services\Penyimpanan;
use App\Services\Peringatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index()
    {
        $d = $this->dosen();
        return view('dosen.review.index', [
            'menunggu' => Peringatan::drafMenunggu($d->id_dosen)->with('skripsi.mahasiswa.pengguna')->orderBy('tanggal_unggah')->get(),
            'selesai' => ReviewDokumen::with('dokumen.skripsi.mahasiswa.pengguna')->where('id_dosen', $d->id_dosen)->orderByDesc('tanggal_review')->limit(20)->get(),
        ]);
    }

    public function show(Dokumen $dokumen)
    {
        $d = $this->dosen();
        abort_unless($dokumen->skripsi->dibimbingOleh($d), 403);
        $versi = Dokumen::with('review')->where('id_skripsi', $dokumen->id_skripsi)->orderBy('bagian')->orderByDesc('versi')->get();
        return view('dosen.review.show', ['dok' => $dokumen->load('review.dosen.pengguna', 'skripsi.mahasiswa.pengguna'), 'd' => $d, 'versi' => $versi,
            'milik' => $dokumen->reviewDari($d->id_dosen)]);
    }

    public function store(Request $r, Dokumen $dokumen)
    {
        $d = $this->dosen();
        abort_unless($dokumen->skripsi->dibimbingOleh($d), 403);
        if ($dokumen->reviewDari($d->id_dosen)) return $this->gagal('Anda sudah mereview versi ini.');
        $aturan = Penyimpanan::aturanUnggah(); $aturan[0] = 'nullable';
        $v = $r->validate(['hasil' => 'required|in:revisi,disetujui', 'komentar' => 'nullable|string|max:5000', 'file_koreksi' => $aturan,
            'revisi' => 'nullable|array', 'revisi.*' => 'nullable|string|max:3000'], [], ['file_koreksi' => 'berkas koreksi']);
        DB::transaction(function () use ($r, $v, $dokumen, $d) {
            $rv = ReviewDokumen::create(['id_dokumen' => $dokumen->id_dokumen, 'id_dosen' => $d->id_dosen, 'hasil' => $v['hasil'],
                'komentar' => $v['komentar'] ?? null, 'file_koreksi' => $r->hasFile('file_koreksi') ? Penyimpanan::simpan($r->file('file_koreksi')) : null,
                'tanggal_review' => now()]);
            foreach (array_filter($v['revisi'] ?? [], fn ($x) => trim((string) $x) !== '') as $u) {
                ItemRevisi::create(['id_skripsi' => $dokumen->id_skripsi, 'id_dosen' => $d->id_dosen, 'id_review' => $rv->id_review,
                    'bab' => $dokumen->labelBagian(), 'uraian' => trim($u), 'status' => 'belum', 'tanggal_dibuat' => now()]);
            }
            LogAktivitas::catat('Mereview dokumen '.$dokumen->label(), 'review_dokumen', $rv->id_review);
            Notifikasi::kirim($dokumen->skripsi->mahasiswa->id_pengguna, 'Draf selesai direview', $dokumen->label().' direview: '.($v['hasil'] === 'revisi' ? 'perlu revisi' : 'disetujui').'.', route('mahasiswa.dokumen.show', $dokumen));
        });
        return redirect()->route('dosen.review.index')->with('ok', 'Review disimpan.');
    }
}

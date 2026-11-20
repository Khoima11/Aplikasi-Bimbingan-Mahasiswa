<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\Pengaturan;
use App\Services\Penyimpanan;
use Illuminate\Http\Request;

class DokumenController extends Controller
{
    private function skripsi()
    {
        $s = $this->mahasiswa()->skripsi;
        if (! $s) abort(redirect()->route('mahasiswa.judul')->with('gagal', 'Unggah draf tersedia setelah judul disetujui.'));
        return $s->load('pembimbing.dosen.pengguna');
    }

    public function index()
    {
        $s = $this->skripsi();
        return view('mahasiswa.dokumen', ['s' => $s, 'maks' => Pengaturan::angka('ukuran_berkas_mb'),
            'dokumen' => $s->dokumen()->with('review')->orderByDesc('tanggal_unggah')->orderByDesc('versi')->get()]);
    }

    public function store(Request $r)
    {
        $s = $this->skripsi();
        $v = $r->validate(['bagian' => 'required|in:'.implode(',', array_keys(Dokumen::BAGIAN)), 'berkas' => Penyimpanan::aturanUnggah(),
            'catatan_mahasiswa' => 'nullable|string|max:255'], ['berkas.max' => 'Ukuran berkas melebihi batas.', 'berkas.mimes' => 'Berkas harus PDF atau DOCX.'], ['catatan_mahasiswa' => 'keterangan perubahan']);
        $f = $r->file('berkas');
        $versi = (int) $s->dokumen()->where('bagian', $v['bagian'])->max('versi') + 1;
        $d = Dokumen::create(['id_skripsi' => $s->id_skripsi, 'bagian' => $v['bagian'], 'versi' => $versi,
            'nama_file' => mb_substr($f->getClientOriginalName(), 0, 150), 'path_file' => Penyimpanan::simpan($f),
            'ukuran_kb' => (int) ceil($f->getSize() / 1024), 'catatan_mahasiswa' => $v['catatan_mahasiswa'] ?? null, 'tanggal_unggah' => now()]);
        LogAktivitas::catat('Mengunggah '.$d->label(), 'dokumen', $d->id_dokumen);
        Notifikasi::kirim($s->pembimbing->map(fn ($p) => $p->dosen->id_pengguna)->all(), 'Draf baru', $this->pengguna()->nama.' mengunggah '.$d->label().'.', route('dosen.review.show', $d));
        return $this->ok($d->label().' berhasil diunggah.');
    }

    public function show(Dokumen $dokumen)
    {
        $s = $this->skripsi();
        abort_unless($dokumen->id_skripsi === $s->id_skripsi, 403);
        return view('mahasiswa.dokumen_show', ['s' => $s, 'dok' => $dokumen->load('review.dosen.pengguna', 'review.revisi')]);
    }
}

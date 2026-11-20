<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skripsi;
use App\Models\Ujian;
use App\Services\TahapanService;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    public function index()
    {
        return view('admin.ujian.index', [
            'siap' => TahapanService::siapUjian(),
            'riwayat' => Ujian::with('skripsi.mahasiswa.pengguna', 'admin')->orderByDesc('id_ujian')->paginate(15),
        ]);
    }

    public function create(Skripsi $skripsi)
    {
        $skripsi->load('mahasiswa.pengguna', 'tahapan', 'pembimbing.dosen.pengguna', 'persetujuan');
        return view('admin.ujian.form', ['s' => $skripsi]);
    }

    public function store(Request $r, Skripsi $skripsi)
    {
        $v = $r->validate(['jenis' => 'required|in:seminar_proposal,sidang', 'tanggal_pelaksanaan' => 'required|date|before_or_equal:today',
            'hasil' => 'required|in:lulus,lulus_dengan_revisi,tidak_lulus', 'catatan' => 'nullable|string|max:255',
            'tanggal_revisi_selesai' => 'nullable|date|after_or_equal:tanggal_pelaksanaan'], [], ['tanggal_pelaksanaan' => 'tanggal pelaksanaan']);
        if ($e = TahapanService::catatUjian($skripsi, $v)) return $this->gagal($e);
        return redirect()->route('admin.ujian.index')->with('ok', 'Hasil ujian dicatat dan tahap skripsi diperbarui.');
    }

    public function revisiSelesai(Request $r, Ujian $ujian)
    {
        $v = $r->validate(['tanggal_revisi_selesai' => 'required|date']);
        TahapanService::selesaikanRevisiSidang($ujian, $v['tanggal_revisi_selesai']);
        return $this->ok('Revisi sidang selesai. Skripsi dinyatakan selesai.');
    }
}

<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ItemRevisi;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class RevisiController extends Controller
{
    public function index(Request $r)
    {
        $s = $this->mahasiswa()->skripsi;
        if (! $s) return redirect()->route('mahasiswa.judul')->with('gagal', 'Daftar revisi tersedia setelah judul disetujui.');
        $tab = in_array($r->tab, ['belum', 'dikerjakan', 'diterima']) ? $r->tab : 'belum';
        return view('mahasiswa.revisi', ['tab' => $tab,
            'jumlah' => $s->revisi()->selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status'),
            'items' => $s->revisi()->with('dosen.pengguna', 'dokumenPerbaikan')->where('status', $tab)->orderByDesc('id_revisi')->get(),
            'dokumen' => $s->dokumen()->orderByDesc('tanggal_unggah')->get()]);
    }

    public function tandai(Request $r, ItemRevisi $revisi)
    {
        $s = $this->mahasiswa()->skripsi;
        abort_unless($s && $revisi->id_skripsi === $s->id_skripsi, 403);
        if ($revisi->status !== 'belum') return $this->gagal('Item revisi ini tidak berstatus belum dikerjakan.');
        $v = $r->validate(['id_dokumen_perbaikan' => ['nullable', \Illuminate\Validation\Rule::exists('dokumen', 'id_dokumen')->where('id_skripsi', $s->id_skripsi)],
            'keterangan_mahasiswa' => 'nullable|string|max:255']);
        $revisi->update(['status' => 'dikerjakan', 'tanggal_dikerjakan' => now(), 'id_dokumen_perbaikan' => $v['id_dokumen_perbaikan'] ?? null,
            'keterangan_mahasiswa' => $v['keterangan_mahasiswa'] ?? null]);
        LogAktivitas::catat('Menandai revisi dikerjakan', 'item_revisi', $revisi->id_revisi);
        Notifikasi::kirim($revisi->dosen->id_pengguna, 'Revisi dikerjakan', $this->pengguna()->nama.' menandai revisi '.$revisi->bab.' telah dikerjakan.', route('dosen.mahasiswa.show', $s));
        return $this->ok('Revisi ditandai dikerjakan. Menunggu verifikasi dosen.');
    }
}

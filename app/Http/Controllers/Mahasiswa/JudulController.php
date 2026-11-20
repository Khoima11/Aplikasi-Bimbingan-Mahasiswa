<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\PengajuanJudul;
use App\Models\Pengguna;
use App\Models\Periode;
use App\Services\JudulSerupa;
use Illuminate\Http\Request;

class JudulController extends Controller
{
    public function index()
    {
        $m = $this->mahasiswa();
        $riwayat = $m->pengajuan()->get();
        $terakhir = $riwayat->first();
        return view('mahasiswa.judul', ['m' => $m, 'riwayat' => $riwayat, 'terakhir' => $terakhir,
            'bolehAjukan' => ! $m->skripsi && ! $riwayat->contains('status', 'menunggu')]);
    }

    public function serupa(Request $r)
    {
        return response()->json(JudulSerupa::cari((string) $r->judul, (string) $r->kata_kunci, $this->mahasiswa()->id_mahasiswa));
    }

    public function store(Request $r)
    {
        $m = $this->mahasiswa();
        if ($m->skripsi) return $this->gagal('Anda sudah memiliki skripsi aktif.');
        if ($m->pengajuan()->where('status', 'menunggu')->exists()) return $this->gagal('Masih ada pengajuan yang menunggu keputusan kaprodi.');
        $v = $r->validate(['judul' => 'required|string|min:15|max:255', 'latar_masalah' => 'required|string|min:50|max:5000',
            'kata_kunci' => 'required|string|max:150'], [], ['latar_masalah' => 'latar masalah', 'kata_kunci' => 'kata kunci']);
        $asal = $m->pengajuan()->first();
        $p = PengajuanJudul::create($v + ['id_mahasiswa' => $m->id_mahasiswa, 'id_periode' => Periode::aktif()?->id_periode,
            'id_pengajuan_asal' => $asal && $asal->status === 'perlu_revisi' ? $asal->id_pengajuan : null,
            'status' => 'menunggu', 'tanggal_ajuan' => now()]);
        LogAktivitas::catat('Mengajukan judul', 'pengajuan_judul', $p->id_pengajuan);
        $kaprodi = Pengguna::whereHas('peran', fn ($q) => $q->where('nama_peran', 'kaprodi'))->pluck('id_pengguna')->all();
        Notifikasi::kirim($kaprodi, 'Pengajuan judul baru', $this->pengguna()->nama.' mengajukan judul.', route('kaprodi.judul.show', $p));
        return redirect()->route('mahasiswa.judul')->with('ok', 'Pengajuan judul terkirim. Anda akan mendapat notifikasi setelah kaprodi memutuskan.');
    }
}

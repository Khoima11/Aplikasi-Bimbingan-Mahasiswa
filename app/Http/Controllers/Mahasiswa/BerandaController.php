<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Tahapan;

class BerandaController extends Controller
{
    public function index()
    {
        $m = $this->mahasiswa();
        $s = $m->skripsi()->with(['tahapan', 'pembimbing.dosen.pengguna'])->first();
        $data = ['m' => $m, 's' => $s, 'tahapan' => Tahapan::semua(), 'pengajuan' => $m->pengajuan()->first(),
            'notif' => $this->pengguna()->notifikasi()->orderByDesc('id_notifikasi')->limit(4)->get()];
        if ($s) {
            $data['revisiBelum'] = $s->revisi()->where('status', 'belum')->count();
            $data['dikoreksi'] = $s->catatan()->where('status_validasi', 'dikoreksi')->count();
            $data['usulan'] = $s->jadwal()->where('status', 'diusulkan_ulang')->count();
            $data['jadwal'] = $s->jadwal()->with('dosen.pengguna')->where('status', 'disetujui')->where('tanggal', '>=', today())->orderBy('tanggal')->orderBy('jam')->first();
            $data['progres'] = $s->pembimbing->map(fn ($p) => ['label' => $p->label(), 'nama' => $p->dosen->pengguna->nama,
                'n' => $s->jumlahTervalidasi($p->id_dosen, true), 'total' => $s->jumlahTervalidasi($p->id_dosen),
                'setuju' => ($j = $s->tahapan->jenisPersetujuan()) ? $s->persetujuan()->where('id_dosen', $p->id_dosen)->where('jenis', $j)->where('status', 'berlaku')->exists() : null]);
        }
        return view('mahasiswa.beranda', $data);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Pengaturan;
use App\Models\Periode;
use App\Models\Tahapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function index()
    {
        return view('admin.master', [
            'periode' => Periode::orderByDesc('tanggal_mulai')->get(),
            'tahapan' => Tahapan::semua(),
            'pengaturan' => Pengaturan::orderBy('id_pengaturan')->get(),
        ]);
    }

    public function simpanPeriode(Request $r)
    {
        $v = $r->validate(['nama_periode' => 'required|string|max:30|unique:periode,nama_periode', 'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai']);
        $p = Periode::create($v + ['status' => 'selesai']);
        LogAktivitas::catat('Menambah periode '.$p->nama_periode, 'periode', $p->id_periode);
        return $this->ok('Periode ditambahkan. Klik "Jadikan aktif" untuk mengaktifkannya.');
    }

    public function aktifkanPeriode(Periode $periode)
    {
        DB::transaction(function () use ($periode) {
            Periode::where('status', 'aktif')->update(['status' => 'selesai']);
            $periode->update(['status' => 'aktif']);
        });
        LogAktivitas::catat('Mengaktifkan periode '.$periode->nama_periode, 'periode', $periode->id_periode);
        return $this->ok("Periode {$periode->nama_periode} kini aktif.");
    }

    public function simpanTahapan(Request $r)
    {
        $v = $r->validate(['t' => 'required|array', 't.*.nama_tahapan' => 'required|string|max:50', 't.*.batas_hari' => 'nullable|integer|min:1|max:3650',
            't.*.min_bimbingan' => 'nullable|integer|min:0|max:99']);
        foreach ($v['t'] as $id => $d) {
            Tahapan::whereKey($id)->update(['nama_tahapan' => $d['nama_tahapan'], 'batas_hari' => $d['batas_hari'] ?? null, 'min_bimbingan' => $d['min_bimbingan'] ?? null]);
        }
        LogAktivitas::catat('Mengubah nilai tahapan', 'tahapan');
        return $this->ok('Tahapan disimpan. Perubahan tidak memengaruhi persetujuan dan hasil ujian yang sudah tercatat.');
    }

    public function simpanPengaturan(Request $r)
    {
        $v = $r->validate(['p' => 'required|array', 'p.*' => 'required|string|max:100']);
        foreach ($v['p'] as $kunci => $nilai) {
            if (isset(Pengaturan::BAWAAN[$kunci]) && ! in_array($kunci, ['nama_prodi', 'nama_institusi']) && (! ctype_digit($nilai) || (int) $nilai < 1)) {
                return $this->gagal("Nilai \"".Pengaturan::BAWAAN[$kunci][1]."\" harus berupa angka bulat positif.");
            }
            Pengaturan::where('kunci', $kunci)->update(['nilai' => $nilai]);
        }
        Pengaturan::lupakan();
        LogAktivitas::catat('Mengubah pengaturan sistem', 'pengaturan');
        return $this->ok('Pengaturan disimpan.');
    }
}

<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Models\Periode;
use App\Models\Skripsi;
use App\Models\Tahapan;
use App\Services\Peringatan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public const TAB = ['tahapan' => 'Progres per Tahapan', 'dosen' => 'Beban Bimbingan Dosen', 'ditinjau' => 'Mahasiswa Perlu Ditinjau', 'lama' => 'Lama Penyelesaian'];

    private function data(Request $r): array
    {
        $tab = array_key_exists($r->tab, self::TAB) ? $r->tab : 'tahapan';
        $q = Skripsi::query();
        if ($r->filled('periode')) $q->whereHas('pengajuan', fn ($p) => $p->where('id_periode', $r->periode));
        if ($r->filled('angkatan')) $q->whereHas('mahasiswa', fn ($m) => $m->where('angkatan', $r->angkatan));
        $semua = Peringatan::muat($q);
        $aktif = $semua->where('status_skripsi', 'aktif');
        $ringkas = ['Total skripsi' => $semua->count(), 'Selesai' => $semua->where('status_skripsi', 'selesai')->count(),
            'Perlu ditinjau' => $aktif->filter(fn ($s) => $s->indikator)->count(), 'Rata-rata lama (bulan)' => DashboardController::rataLama() ?? '-'];
        $baris = match ($tab) {
            'tahapan' => Tahapan::semua()->where('urutan', '>', 1)->map(function ($t) use ($semua) {
                $di = $semua->where('id_tahapan', $t->id_tahapan);
                $hari = $di->map(fn ($s) => $s->tanggal_masuk_tahap->diffInDays(today()));
                return [$t->nama_tahapan, $di->count(), $semua->count() ? round($di->count() / $semua->count() * 100).'%' : '0%',
                    $t->urutan < 6 && $hari->count() ? round($hari->avg()) : '-',
                    $t->batas_hari && $t->urutan < 6 ? $hari->filter(fn ($h) => $h > $t->batas_hari)->count() : '-'];
            })->values()->all(),
            'dosen' => DashboardController::bebanDosen()->map(fn ($d) => [$d->pengguna->nama, $d->p1.' / '.$d->kuota_pembimbing_1, $d->p2.' / '.$d->kuota_pembimbing_2, $d->draf_lama, $d->menunggu])->all(),
            'ditinjau' => $aktif->filter(fn ($s) => $s->indikator)->map(fn ($s) => [$s->mahasiswa->pengguna->nama, $s->mahasiswa->nim, $s->tahapan->nama_tahapan,
                collect($s->indikator)->pluck('label')->implode('; '), $s->pembimbing->map(fn ($p) => $p->dosen->pengguna->nama)->implode(', ')])->values()->all(),
            'lama' => $semua->where('status_skripsi', 'selesai')->map(fn ($s) => [$s->mahasiswa->pengguna->nama, $s->mahasiswa->nim, $s->mahasiswa->angkatan,
                $s->tanggal_mulai->format('d/m/Y'), $s->tanggal_selesai?->format('d/m/Y'), $s->tanggal_selesai ? round($s->tanggal_mulai->diffInDays($s->tanggal_selesai) / 30.4, 1) : '-'])->values()->all(),
        };
        $kepala = [
            'tahapan' => ['Tahapan', 'Jumlah', 'Persentase', 'Rata-rata hari di tahap', 'Melewati batas'],
            'dosen' => ['Dosen', 'Pembimbing I (terisi/kuota)', 'Pembimbing II (terisi/kuota)', 'Draf > batas review', 'Catatan menunggu validasi'],
            'ditinjau' => ['Nama', 'NIM', 'Tahap', 'Indikator', 'Pembimbing'],
            'lama' => ['Nama', 'NIM', 'Angkatan', 'Mulai', 'Selesai', 'Lama (bulan)'],
        ][$tab];
        $periode = $r->filled('periode') ? Periode::find($r->periode)?->nama_periode : 'Semua periode';
        return compact('tab', 'ringkas', 'baris', 'kepala', 'periode');
    }

    public function index(Request $r)
    {
        return view('kaprodi.laporan', $this->data($r) + [
            'daftarPeriode' => Periode::orderByDesc('tanggal_mulai')->get(),
            'daftarAngkatan' => \App\Models\Mahasiswa::select('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
        ]);
    }

    public function pdf(Request $r)
    {
        $d = $this->data($r);
        \App\Models\LogAktivitas::catat('Mengunduh laporan '.self::TAB[$d['tab']], null);
        return Pdf::loadView('pdf.laporan', $d + ['judul' => self::TAB[$d['tab']], 'angkatan' => $r->angkatan])
            ->setPaper('a4', in_array($d['tab'], ['ditinjau', 'dosen']) ? 'landscape' : 'portrait')
            ->download('Laporan_'.$d['tab'].'_'.now()->format('Ymd').'.pdf');
    }
}

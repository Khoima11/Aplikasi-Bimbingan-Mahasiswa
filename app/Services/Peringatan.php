<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\Pengaturan;
use App\Models\Skripsi;
use Illuminate\Support\Collection;

/** Indikator "Perlu Ditinjau" PD-01..PD-04 (AB-14) dan PD-05 untuk beban dosen. */
class Peringatan
{
    /** Muat skripsi aktif beserta data yang dibutuhkan untuk menghitung indikator. */
    public static function muat($query = null): Collection
    {
        $q = $query ?? Skripsi::query();
        return $q->with(['mahasiswa.pengguna', 'tahapan', 'pembimbing.dosen.pengguna'])
            ->withMax(['catatan as terakhir_bimbingan' => fn ($c) => $c->where('status_validasi', 'divalidasi')], 'tanggal_bimbingan')
            ->withMin(['revisi as revisi_tertua' => fn ($r) => $r->where('status', 'belum')], 'tanggal_dibuat')
            ->get()->each(fn ($s) => $s->indikator = self::hitung($s));
    }

    public static function hitung(Skripsi $s): array
    {
        if ($s->status_skripsi !== 'aktif') return [];
        $out = [];
        $hari = fn ($tgl) => (int) \Carbon\Carbon::parse($tgl)->startOfDay()->diffInDays(today());
        $terakhir = $s->terakhir_bimbingan ?? $s->catatan()->where('status_validasi', 'divalidasi')->max('tanggal_bimbingan');
        $dasar = $terakhir ?: $s->tanggal_mulai;
        $n = $hari($dasar);
        if ($n > Pengaturan::angka('batas_tanpa_bimbingan_hari')) {
            $out[] = ['kode' => 'PD-01', 'label' => "Tanpa bimbingan {$n} hari"];
        }
        $batas = $s->tahapan?->batas_hari;
        $diTahap = $hari($s->tanggal_masuk_tahap);
        if ($batas && $diTahap > $batas) {
            $out[] = ['kode' => 'PD-02', 'label' => "Di tahap ini {$diTahap} hari"];
        }
        $tertua = $s->revisi_tertua ?? $s->revisi()->where('status', 'belum')->min('tanggal_dibuat');
        if ($tertua && ($r = $hari($tertua)) > Pengaturan::angka('batas_revisi_hari')) {
            $out[] = ['kode' => 'PD-03', 'label' => "Revisi tertunda {$r} hari"];
        }
        $sem = $s->mahasiswa?->semester();
        if ($sem && $sem >= Pengaturan::angka('semester_peringatan')) {
            $out[] = ['kode' => 'PD-04', 'label' => "Semester {$sem}"];
        }
        return $out;
    }

    /** PD-05: jumlah versi dokumen yang belum direview dosen melebihi batas hari. */
    public static function drafTerlambat(int $idDosen): int
    {
        $batas = now()->subDays(Pengaturan::angka('batas_review_hari'));
        return self::drafMenunggu($idDosen)->where('tanggal_unggah', '<', $batas)->count();
    }

    public static function drafMenunggu(int $idDosen)
    {
        return Dokumen::whereHas('skripsi', fn ($q) => $q->where('status_skripsi', 'aktif')
            ->whereHas('pembimbing', fn ($p) => $p->where('id_dosen', $idDosen)))
            ->whereDoesntHave('review', fn ($r) => $r->where('id_dosen', $idDosen))
            // hanya versi terbaru tiap bagian yang perlu direview
            ->whereRaw('versi = (select max(d2.versi) from dokumen d2 where d2.id_skripsi = dokumen.id_skripsi and d2.bagian = dokumen.bagian)');
    }
}

<?php

namespace App\Services;

use App\Models\PengajuanJudul;

/** Pencarian judul serupa berdasarkan kata kunci (AB-03). Bukan cek plagiarisme. */
class JudulSerupa
{
    private const STOP = ['yang', 'dan', 'atau', 'dengan', 'untuk', 'pada', 'dalam', 'dari', 'terhadap', 'sebagai', 'oleh', 'melalui',
        'studi', 'kasus', 'analisis', 'pengaruh', 'hubungan', 'tentang', 'antara', 'serta', 'kota', 'kabupaten', 'negeri', 'tahun',
        'berbasis', 'upaya', 'peran', 'implementasi', 'penerapan', 'the', 'of', 'and', 'in'];

    public static function kata(string $judul, ?string $kataKunci): array
    {
        $kk = collect(preg_split('/[,;]+/', mb_strtolower($kataKunci ?? '')))->map(fn ($x) => trim($x))->filter(fn ($x) => mb_strlen($x) >= 3);
        $jw = collect(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($judul)))->filter(fn ($w) => mb_strlen($w) >= 4 && ! in_array($w, self::STOP));
        return $kk->merge($jw)->unique()->take(12)->values()->all();
    }

    public static function cari(string $judul, ?string $kataKunci, ?int $kecualiMahasiswa = null, int $batas = 5): array
    {
        $kata = self::kata($judul, $kataKunci);
        if (! $kata) return [];
        $q = PengajuanJudul::with('mahasiswa.pengguna')->whereIn('status', ['disetujui', 'menunggu'])
            ->where(function ($w) use ($kata) {
                foreach ($kata as $k) {
                    $w->orWhereRaw('LOWER(judul) LIKE ?', ['%'.$k.'%'])->orWhereRaw('LOWER(kata_kunci) LIKE ?', ['%'.$k.'%']);
                }
            });
        if ($kecualiMahasiswa) $q->where('id_mahasiswa', '!=', $kecualiMahasiswa);
        return $q->limit(60)->get()->map(function ($p) use ($kata) {
            $teks = mb_strtolower($p->judul.' '.$p->kata_kunci);
            $skor = collect($kata)->filter(fn ($k) => str_contains($teks, $k))->count();
            return ['judul' => $p->judul, 'nama' => $p->mahasiswa?->pengguna?->nama, 'nim' => $p->mahasiswa?->nim,
                'tahun' => $p->tanggal_ajuan?->year, 'status' => $p->status, 'skor' => $skor];
        })->sortByDesc('skor')->filter(fn ($x) => $x['skor'] >= min(2, count($kata)))->take($batas)->values()->all();
    }
}

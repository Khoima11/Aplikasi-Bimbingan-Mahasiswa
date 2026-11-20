@props(['s'])
@php
$peta = [
 'menunggu' => ['Menunggu', 'jingga'], 'perlu_revisi' => ['Perlu revisi', 'merah'], 'disetujui' => ['Disetujui', 'hijau'], 'ditolak' => ['Ditolak', 'merah'],
 'diajukan' => ['Diajukan', 'jingga'], 'diusulkan_ulang' => ['Diusulkan ulang', 'emas'], 'dibatalkan' => ['Dibatalkan', 'abu'], 'selesai' => ['Selesai', 'hijau'],
 'divalidasi' => ['Divalidasi', 'hijau'], 'dikoreksi' => ['Dikoreksi', 'merah'],
 'belum' => ['Belum dikerjakan', 'jingga'], 'dikerjakan' => ['Dikerjakan', 'emas'], 'diterima' => ['Diterima', 'hijau'],
 'revisi' => ['Perlu revisi', 'merah'], 'aktif' => ['Aktif', 'hijau'], 'nonaktif' => ['Nonaktif', 'abu'], 'buka' => ['Buka', 'hijau'], 'tutup' => ['Tutup', 'abu'],
 'lulus' => ['Lulus', 'hijau'], 'lulus_dengan_revisi' => ['Lulus dengan revisi', 'emas'], 'tidak_lulus' => ['Tidak lulus', 'merah'],
 'berlaku' => ['Berlaku', 'hijau'], 'diganti' => ['Diganti', 'abu'], 'berhenti' => ['Berhenti', 'abu'],
];
[$t, $w] = $peta[$s] ?? [ucfirst(str_replace('_', ' ', $s)), 'abu'];
@endphp
<span {{ $attributes->merge(['class' => "pill pill-$w"]) }}>{{ $t }}</span>

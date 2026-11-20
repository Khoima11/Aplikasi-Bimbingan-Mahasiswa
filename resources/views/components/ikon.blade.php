@props(['n', 'c' => 'size-5'])
@php
$p = [
 'rumah' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z',
 'orang' => 'M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M21 19v-1a4 4 0 0 0-3-3.87M16 3.13a3.5 3.5 0 0 1 0 6.75',
 'toga' => 'M2 9l10-5 10 5-10 5zM6 11v5c3 2.5 9 2.5 12 0v-5M22 9v6',
 'atur' => 'M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0M14 4v4M8 10v4M16 16v4',
 'daftar' => 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
 'centang' => 'M9 11l3 3 8-8M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9',
 'grafik' => 'M4 20V10M10 20V4M16 20v-7M22 20H2',
 'kalender' => 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4',
 'dokumen' => 'M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8zM14 3v5h5M9 13h6M9 17h6',
 'pena' => 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z',
 'lonceng' => 'M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.94 1.94 0 0 0 3.4 0',
 'menu' => 'M4 6h16M4 12h16M4 18h16',
 'tutup' => 'M6 6l12 12M18 6 6 18',
 'unduh' => 'M12 3v12m0 0-4-4m4 4 4-4M4 17v3h16v-3',
 'unggah' => 'M12 21V9m0 0-4 4m4-4 4 4M4 7V4h16v3',
 'keluar' => 'M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3',
 'cetak' => 'M6 9V3h12v6M6 18H4v-7h16v7h-2M8 14h8v7H8z',
 'peringatan' => 'M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0',
 'lainnya' => 'M5 12h.01M12 12h.01M19 12h.01',
];
@endphp
<svg {{ $attributes->merge(['class' => $c]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $p[$n] ?? '' }}"/></svg>

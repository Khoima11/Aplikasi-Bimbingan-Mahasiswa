<!DOCTYPE html><html><head><meta charset="utf-8"><style>
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1C2433; }
.kop { border-bottom: 2px solid #7A1F3D; padding-bottom: 6px; margin-bottom: 10px; }
h1 { font-size: 14px; margin: 0 0 4px; } .redup { color: #5B6577; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th { background: #F4DCE4; border: 1px solid #999; padding: 5px; text-align: left; }
td { border: 1px solid #999; padding: 5px; vertical-align: top; }
.r td { border: none; padding: 2px 10px 2px 0; }
</style></head><body>
<div class="kop"><b>{{ strtoupper(\App\Models\Pengaturan::nilai('nama_institusi')) }}</b><br>Program Studi {{ \App\Models\Pengaturan::nilai('nama_prodi') }}</div>
<h1>Laporan {{ $judul }}</h1>
<p class="redup">Periode: {{ $periode }}{{ $angkatan ? ' · Angkatan '.$angkatan : '' }} · Dicetak {{ now()->translatedFormat('d F Y, H:i') }} oleh {{ auth()->user()->nama }}</p>
<table class="r"><tr>@foreach ($ringkas as $l => $v)<td>{{ $l }}: <b>{{ $v }}</b></td>@endforeach</tr></table>
<table><thead><tr><th style="width:20px">No</th>@foreach ($kepala as $h)<th>{{ $h }}</th>@endforeach</tr></thead><tbody>
@forelse ($baris as $i => $b)<tr><td>{{ $i + 1 }}</td>@foreach ($b as $c)<td>{{ $c }}</td>@endforeach</tr>
@empty<tr><td colspan="{{ count($kepala) + 1 }}">Tidak ada data.</td></tr>@endforelse
</tbody></table>
<p class="redup" style="margin-top:14px">Dihasilkan otomatis oleh SIBIMTA. Status "Perlu Ditinjau" adalah peringatan dini, bukan kesimpulan akhir.</p>
</body></html>

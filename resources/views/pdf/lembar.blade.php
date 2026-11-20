<!DOCTYPE html><html><head><meta charset="utf-8"><style>
@page { margin: 28mm 20mm 22mm 20mm; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1C2433; }
.kop { border-bottom: 2px solid #7A1F3D; padding-bottom: 8px; margin-bottom: 12px; }
.kop .a { font-size: 13px; font-weight: bold; } .kop .b { font-size: 11px; }
h2 { text-align: center; font-size: 13px; margin: 6px 0 12px; letter-spacing: .5px; }
table { width: 100%; border-collapse: collapse; }
.id td { padding: 2px 4px; vertical-align: top; }
.isi th { background: #F4DCE4; border: 1px solid #999; padding: 5px; font-size: 10px; }
.isi td { border: 1px solid #999; padding: 5px; vertical-align: top; }
.kaki { margin-top: 16px; } .kaki td { vertical-align: top; }
.kecil { font-size: 9px; color: #5B6577; }
</style></head><body>
<div class="kop"><div class="a">{{ strtoupper(\App\Models\Pengaturan::nilai('nama_institusi')) }}</div><div class="b">Program Studi {{ \App\Models\Pengaturan::nilai('nama_prodi') }}</div></div>
<h2>LEMBAR BIMBINGAN SKRIPSI</h2>
<table class="id">
<tr><td style="width:120px">Nama</td><td>: {{ $s->mahasiswa->pengguna->nama }}</td></tr>
<tr><td>NIM</td><td>: {{ $s->mahasiswa->nim }}</td></tr>
<tr><td>Judul</td><td>: {{ $s->judul_final }}</td></tr>
@foreach ($s->pembimbing as $p)<tr><td>{{ $p->label() }}</td><td>: {{ $p->dosen->pengguna->nama }}</td></tr>@endforeach
<tr><td>Tahap saat dicetak</td><td>: {{ $s->tahapan->nama_tahapan }}</td></tr>
</table>
<br>
<table class="isi">
<thead><tr><th style="width:22px">No</th><th style="width:62px">Tanggal</th><th style="width:110px">Pembimbing</th><th style="width:60px">Bab</th><th>Ringkasan bimbingan</th><th style="width:62px">Divalidasi</th></tr></thead>
<tbody>
@forelse ($catatan as $i => $c)
<tr><td>{{ $i + 1 }}</td><td>{{ $c->tanggal_bimbingan->format('d/m/Y') }}</td><td>{{ $c->dosen->pengguna->nama }}</td><td>{{ $c->bab_dibahas }}</td><td>{{ $c->ringkasan }}</td><td>{{ $c->tanggal_validasi?->format('d/m/Y') }}</td></tr>
@empty
<tr><td colspan="6" style="text-align:center">Belum ada bimbingan tervalidasi.</td></tr>
@endforelse
</tbody></table>
<table class="kaki"><tr>
<td>Jumlah bimbingan tervalidasi: <b>{{ $catatan->count() }}</b><br>
@foreach ($s->pembimbing as $p)&nbsp;&nbsp;{{ $p->label() }}: {{ $catatan->where('id_dosen', $p->id_dosen)->count() }} kali<br>@endforeach
Dicetak: {{ $lembar->tanggal_cetak->translatedFormat('d F Y, H:i') }} oleh {{ auth()->user()->nama }}<br><br>
<span class="kecil">Lembar ini dihasilkan otomatis oleh SIBIMTA dan hanya memuat catatan yang telah divalidasi dosen pembimbing.<br>
Keaslian lembar dapat diperiksa dengan memindai kode QR atau membuka:<br>{{ $url }}</span></td>
<td style="width:130px; text-align:center"><img src="{{ $qr }}" style="width:115px;height:115px"><br><b>{{ $lembar->kode_verifikasi }}</b></td>
</tr></table>
</body></html>

<div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Mahasiswa</th><th>Ujian</th><th>Persetujuan</th><th></th></tr></thead><tbody>
@forelse ($siap as $s)
    <tr><td class="whitespace-nowrap">{{ $s->mahasiswa->pengguna->nama }}<br><span class="text-xs text-redup">{{ $s->mahasiswa->nim }}</span></td>
        <td>{{ \App\Models\Persetujuan::JENIS[$s->jenis_ujian] }}@if($s->ujian_tertunda)<br><span class="pill pill-emas">Menunggu revisi sidang selesai</span>@endif</td>
        <td class="text-xs">
            @foreach ($s->pembimbing as $p)
                {{ $p->urutan == 1 ? 'P1' : 'P2' }}: {{ $s->persetujuan->where('id_dosen', $p->id_dosen)->where('jenis', $s->jenis_ujian)->where('status', 'berlaku')->first()?->tanggal_persetujuan->format('d/m/Y') ?? '-' }}<br>
            @endforeach
        </td>
        <td class="whitespace-nowrap">
            @if ($s->ujian_tertunda)
                <form method="post" action="{{ route('admin.ujian.revisi', $s->ujian_tertunda) }}" class="flex gap-1">@csrf<input type="date" name="tanggal_revisi_selesai" class="input !w-36 !py-1 text-xs" required aria-label="Tanggal revisi selesai"><button class="btn btn-kecil">Selesai</button></form>
            @else
                <a href="{{ route('admin.ujian.create', $s) }}" class="btn btn-utama btn-kecil">Catat hasil</a>
            @endif
        </td></tr>
@empty <tr><td colspan="4"><x-kosong>Belum ada mahasiswa yang memperoleh persetujuan kedua pembimbing.</x-kosong></td></tr> @endforelse
</tbody></table></div>

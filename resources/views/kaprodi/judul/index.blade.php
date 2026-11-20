<x-layout title="Verifikasi judul" judul="Verifikasi pengajuan judul">
    <div class="mb-4 flex gap-1 overflow-x-auto border-b border-garis">
        @foreach (['menunggu' => 'Menunggu', 'perlu_revisi' => 'Perlu revisi', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $k => $l)
            <a href="{{ route('kaprodi.judul.index', ['status' => $k]) }}" class="tab whitespace-nowrap {{ $status === $k ? 'tab-aktif' : '' }}">{{ $l }}@if($k !== 'semua') ({{ $jumlah[$k] ?? 0 }})@endif</a>
        @endforeach
    </div>
    <div class="panel overflow-x-auto">
        <table class="tbl"><thead><tr><th>Mahasiswa</th><th>Judul</th><th>Diajukan</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse ($items as $p)
            <tr><td class="whitespace-nowrap">{{ $p->mahasiswa->pengguna->nama }}<br><span class="text-xs text-redup">{{ $p->mahasiswa->nim }}</span></td>
                <td class="min-w-64">{{ $p->judul }}@if($p->id_pengajuan_asal)<br><span class="pill pill-abu mt-1">Pengajuan ulang</span>@endif</td>
                <td class="whitespace-nowrap">{{ $p->tanggal_ajuan->format('d/m/Y') }}</td><td><x-status :s="$p->status" /></td>
                <td><a href="{{ route('kaprodi.judul.show', $p) }}" class="btn btn-kecil {{ $p->status === 'menunggu' ? 'btn-utama' : '' }}">{{ $p->status === 'menunggu' ? 'Periksa' : 'Lihat' }}</a></td></tr>
        @empty <tr><td colspan="5"><x-kosong>Tidak ada pengajuan pada status ini.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</x-layout>

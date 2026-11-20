<x-layout title="Log aktivitas" judul="Log aktivitas">
    <form method="get" class="mb-4 flex gap-2"><label class="sr-only" for="cari">Cari aktivitas</label><input class="input max-w-xs" id="cari" name="cari" value="{{ request('cari') }}" placeholder="Cari aktivitas"><button class="btn">Cari</button></form>
    <div class="panel overflow-x-auto">
        <table class="tbl"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Data</th><th>Alamat IP</th></tr></thead><tbody>
        @forelse ($items as $l)
            <tr><td class="whitespace-nowrap">{{ $l->waktu->format('d/m/Y H:i') }}</td><td>{{ $l->pengguna?->nama ?? '-' }}</td><td>{{ $l->aktivitas }}</td><td class="text-xs text-redup">{{ $l->tabel_terkait }}{{ $l->id_data ? ' #'.$l->id_data : '' }}</td><td class="text-xs">{{ $l->alamat_ip }}</td></tr>
        @empty <tr><td colspan="5"><x-kosong>Belum ada aktivitas.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</x-layout>

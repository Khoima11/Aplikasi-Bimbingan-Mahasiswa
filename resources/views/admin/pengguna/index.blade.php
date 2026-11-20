<x-layout title="Pengguna" judul="Data pengguna">
    <div class="baris-tab">
        @foreach (['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'admin' => 'Admin'] as $k => $l)
            <a href="{{ route('admin.pengguna.index', ['tab' => $k]) }}" class="tab {{ $tab === $k ? 'tab-aktif' : '' }}">{{ $l }}</a>
        @endforeach
    </div>
    <div class="mb-4 flex flex-wrap items-end gap-2">
        <form method="get" class="flex flex-1 flex-wrap gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="sr-only" for="cari">Cari</label><input class="input max-w-xs" id="cari" name="cari" value="{{ request('cari') }}" placeholder="Cari nama atau username">
            <label class="sr-only" for="status">Status</label><select class="input w-auto" id="status" name="status"><option value="">Semua status</option><option value="aktif" @selected(request('status') === 'aktif')>Aktif</option><option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option></select>
            <button class="btn">Cari</button>
        </form>
        <a href="{{ route('admin.pengguna.create', ['jenis' => $tab]) }}" class="btn btn-utama">Tambah {{ $tab }}</a>
    </div>
    @if ($tab !== 'admin')
        <details class="panel mb-4 text-sm">
            <summary class="cursor-pointer px-4 py-3 font-semibold">Impor {{ $tab }} dari Excel</summary>
            <form method="post" action="{{ route('admin.pengguna.impor') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-2 border-t border-garis p-4">
                @csrf <input type="hidden" name="jenis" value="{{ $tab }}">
                <div><label class="label" for="berkas">Berkas .xlsx atau .csv</label><input class="input" type="file" id="berkas" name="berkas" accept=".xlsx,.xls,.csv" required></div>
                <button class="btn btn-utama">Impor</button>
                <a href="{{ route('admin.pengguna.templat', ['jenis' => $tab]) }}" class="btn">Unduh templat</a>
                <p class="bantuan w-full">Kata sandi awal sama dengan username dan wajib diganti saat login pertama. Username yang sudah terdaftar dilewati.</p>
            </form>
        </details>
    @endif
    <div class="panel overflow-x-auto">
        <table class="tbl"><thead><tr><th>Nama</th><th>Username</th><th>Peran</th>
            @if ($tab === 'dosen')<th>Kuota P1</th><th>Kuota P2</th>@elseif ($tab === 'mahasiswa')<th>Angkatan</th>@endif
            <th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse ($items as $u)
            <tr><td class="font-medium">{{ $u->nama }}<br><span class="text-xs text-redup">{{ $u->email }}</span></td><td>{{ $u->username }}</td>
                <td>@foreach ($u->daftarPeran() as $p)<span class="pill pill-marun mb-1 mr-1">{{ \App\Models\Peran::LABEL[$p] }}</span>@endforeach</td>
                @if ($tab === 'dosen')
                    <td>{{ $u->dosen?->terisi(1) }} / {{ $u->dosen?->kuota_pembimbing_1 }}</td><td>{{ $u->dosen?->terisi(2) }} / {{ $u->dosen?->kuota_pembimbing_2 }}</td>
                @elseif ($tab === 'mahasiswa')<td>{{ $u->mahasiswa?->angkatan }}</td>@endif
                <td><x-status :s="$u->status" /></td>
                <td class="whitespace-nowrap"><div class="flex gap-1">
                    <a href="{{ route('admin.pengguna.edit', $u) }}" class="btn btn-kecil">Ubah</a>
                    <form method="post" action="{{ route('admin.pengguna.reset', $u) }}" onsubmit="return confirm('Reset kata sandi {{ $u->nama }} menjadi username?')">@csrf<button class="btn btn-kecil">Reset sandi</button></form>
                    <form method="post" action="{{ route('admin.pengguna.status', $u) }}">@csrf<button class="btn btn-kecil">{{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                </div></td></tr>
        @empty <tr><td colspan="7"><x-kosong>Belum ada pengguna.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    <p class="mt-2 text-sm text-redup">Akun tidak dihapus agar riwayat bimbingan tetap tersimpan. Pengguna yang tidak aktif dinonaktifkan.</p>
</x-layout>

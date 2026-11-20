<x-layout title="Progres mahasiswa" judul="Progres mahasiswa">
    <form method="get" class="mb-4 flex flex-wrap gap-2">
        <label class="sr-only" for="cari">Cari</label>
        <input class="input max-w-xs" id="cari" name="cari" value="{{ request('cari') }}" placeholder="Cari nama, NIM, atau judul">
        <label class="sr-only" for="tahap">Tahap</label>
        <select class="input w-auto" id="tahap" name="tahap"><option value="">Semua tahap</option>@foreach ($tahapan->where('urutan', '>', 1) as $t)<option value="{{ $t->id_tahapan }}" @selected(request('tahap') == $t->id_tahapan)>{{ $t->nama_tahapan }}</option>@endforeach</select>
        <label class="sr-only" for="status">Status</label>
        <select class="input w-auto" id="status" name="status">@foreach (['aktif' => 'Aktif', 'selesai' => 'Selesai', 'berhenti' => 'Berhenti'] as $k => $l)<option value="{{ $k }}" @selected(request('status', 'aktif') === $k)>{{ $l }}</option>@endforeach</select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ditinjau" value="1" @checked(request('ditinjau')) class="accent-marun-700">Hanya perlu ditinjau</label>
        <button class="btn" type="submit">Tampilkan</button>
    </form>
    <div class="panel overflow-x-auto">
        <table class="tbl"><thead><tr><th>Mahasiswa</th><th>Judul</th><th>Tahap</th><th>Pembimbing</th><th>Status</th></tr></thead><tbody>
        @forelse ($items as $s)
            <tr><td class="whitespace-nowrap"><a href="{{ route('kaprodi.mahasiswa.show', $s) }}" class="font-medium">{{ $s->mahasiswa->pengguna->nama }}</a><br><span class="text-xs text-redup">{{ $s->mahasiswa->nim }}</span></td>
                <td class="min-w-56">{{ \Illuminate\Support\Str::limit($s->judul_final, 90) }}</td><td class="whitespace-nowrap">{{ $s->tahapan->nama_tahapan }}</td>
                <td class="text-xs">@foreach ($s->pembimbing as $p){{ $p->urutan == 1 ? 'P1' : 'P2' }}: {{ $p->dosen->pengguna->nama }}<br>@endforeach</td>
                <td><x-indikator :items="$s->indikator" /></td></tr>
        @empty <tr><td colspan="5"><x-kosong>Tidak ada data yang cocok.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
</x-layout>

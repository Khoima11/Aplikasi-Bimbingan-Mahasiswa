<x-layout title="Dashboard dosen">
    <h1 class="mb-4">Selamat datang, {{ $d->pengguna->nama }}</h1>
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-angka label="Mahasiswa bimbingan" :nilai="$mhs->count()" :href="route('dosen.mahasiswa.index')" />
        <x-angka label="Permintaan jadwal" :nilai="$nPermintaan" :tegas="true" :href="route('dosen.jadwal')">Tinjau</x-angka>
        <x-angka label="Catatan menunggu validasi" :nilai="$nCatatan" :tegas="true" :href="route('dosen.tindakan')">Validasi</x-angka>
        <x-angka label="Draf menunggu review" :nilai="$nDraf" :tegas="true" :href="route('dosen.review.index')">Review</x-angka>
    </div>
    <div class="grid gap-4 lg:grid-cols-[2fr_3fr]">
        <section class="panel">
            <div class="panel-kepala"><h2>Jadwal 7 hari ke depan</h2><a href="{{ route('dosen.jadwal') }}" class="text-sm font-semibold">Kelola slot</a></div>
            <ul class="divide-y divide-garis">
                @forelse ($jadwal as $j)
                    <li class="px-4 py-3 text-sm"><p class="font-semibold">{{ $j->tanggal->translatedFormat('l, d M') }} · {{ substr($j->jam, 0, 5) }}</p><p>{{ $j->skripsi->mahasiswa->pengguna->nama }} <span class="text-redup">· {{ ucfirst($j->mode) }}</span></p><p class="text-redup">{{ $j->rencana_bahasan }}</p></li>
                @empty
                    <li><x-kosong>Tidak ada jadwal yang disetujui dalam 7 hari ke depan.</x-kosong></li>
                @endforelse
            </ul>
        </section>
        <section class="panel">
            <div class="panel-kepala"><h2>Mahasiswa bimbingan</h2></div>
            <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Nama</th><th>Sebagai</th><th>Tahap</th><th>Status</th></tr></thead><tbody>
            @forelse ($mhs as $s)
                <tr><td><a href="{{ route('dosen.mahasiswa.show', $s) }}" class="font-medium">{{ $s->mahasiswa->pengguna->nama }}</a></td>
                    <td>{{ $s->pembimbing->firstWhere('id_dosen', $d->id_dosen)?->urutan == 1 ? 'P1' : 'P2' }}</td>
                    <td>{{ $s->tahapan->nama_tahapan }}</td><td><x-indikator :items="$s->indikator" /></td></tr>
            @empty <tr><td colspan="4"><x-kosong>Belum ada mahasiswa bimbingan.</x-kosong></td></tr> @endforelse
            </tbody></table></div>
        </section>
    </div>
</x-layout>

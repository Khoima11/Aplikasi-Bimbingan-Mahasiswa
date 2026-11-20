<x-layout title="Perlu tindakan">
    <h1 class="mb-4">Perlu tindakan Anda</h1>
    <div x-data="{ t: '{{ $catatan->count() ? 'catatan' : ($jadwal->count() ? 'jadwal' : 'draf') }}' }">
        <div class="baris-tab">
            <button type="button" class="tab" :class="t==='catatan' && 'tab-aktif'" @click="t='catatan'">Catatan ({{ $catatan->count() }})</button>
            <button type="button" class="tab" :class="t==='jadwal' && 'tab-aktif'" @click="t='jadwal'">Jadwal ({{ $jadwal->count() }})</button>
            <button type="button" class="tab" :class="t==='draf' && 'tab-aktif'" @click="t='draf'">Draf ({{ $draf->count() }})</button>
        </div>
        <div x-show="t==='catatan'" class="grid gap-3 md:grid-cols-2">
            @forelse ($catatan as $c)
                <article class="panel panel-isi text-sm" x-data="{ koreksi: false }">
                    <p><a href="{{ route('dosen.mahasiswa.show', $c->skripsi) }}" class="font-bold">{{ $c->skripsi->mahasiswa->pengguna->nama }}</a></p>
                    <p class="text-redup">{{ $c->tanggal_bimbingan->translatedFormat('d M Y') }} · {{ $c->bab_dibahas }} · {{ ucfirst($c->mode) }}</p>
                    <p class="mt-2 whitespace-pre-line">{{ $c->ringkasan }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-2" x-show="!koreksi">
                        <form method="post" action="{{ route('dosen.catatan.validasi', $c) }}">@csrf<button class="btn btn-hijau w-full" type="submit">Validasi</button></form>
                        <button type="button" class="btn w-full" @click="koreksi = true">Koreksi</button>
                    </div>
                    <form x-show="koreksi" x-cloak method="post" action="{{ route('dosen.catatan.koreksi', $c) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea class="input" name="catatan_dosen" rows="3" required placeholder="Apa yang perlu diperbaiki mahasiswa" aria-label="Tanggapan koreksi"></textarea>
                        <div class="flex gap-2"><button class="btn btn-utama btn-kecil" type="submit">Kirim koreksi</button><button type="button" class="btn btn-kecil" @click="koreksi = false">Batal</button></div>
                    </form>
                </article>
            @empty
                <div class="panel md:col-span-2"><x-kosong>Semua catatan sudah ditanggapi.</x-kosong></div>
            @endforelse
        </div>
        <div x-show="t==='jadwal'" x-cloak class="grid gap-3 md:grid-cols-2">
            @forelse ($jadwal as $j)
                @include('dosen._permintaan', ['j' => $j])
            @empty
                <div class="panel md:col-span-2"><x-kosong>Tidak ada permintaan jadwal.</x-kosong></div>
            @endforelse
        </div>
        <div x-show="t==='draf'" x-cloak class="panel divide-y divide-krem-2">
            @forelse ($draf as $d)
                <a href="{{ route('dosen.review.show', $d) }}" class="flex items-center justify-between gap-2 px-4 py-3 text-sm text-tinta no-underline hover:bg-krem">
                    <span><span class="font-bold">{{ $d->skripsi->mahasiswa->pengguna->nama }}</span><br><span class="text-redup">{{ $d->label() }} · {{ $d->tanggal_unggah->diffForHumans() }}</span></span>
                    <span class="btn btn-kecil">Review</span>
                </a>
            @empty
                <x-kosong>Tidak ada draf yang menunggu review.</x-kosong>
            @endforelse
        </div>
    </div>
</x-layout>

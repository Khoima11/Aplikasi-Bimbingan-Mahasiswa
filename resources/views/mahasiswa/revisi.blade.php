<x-layout title="Revisi" judul="Daftar revisi">
    <div class="mb-4 flex gap-1 overflow-x-auto border-b border-garis">
        @foreach (['belum' => 'Belum', 'dikerjakan' => 'Dikerjakan', 'diterima' => 'Diterima'] as $k => $l)
            <a href="{{ route('mahasiswa.revisi', ['tab' => $k]) }}" class="tab {{ $tab === $k ? 'tab-aktif' : '' }}">{{ $l }} ({{ $jumlah[$k] ?? 0 }})</a>
        @endforeach
    </div>
    <div class="space-y-3">
        @forelse ($items as $i)
            <article class="panel panel-isi text-sm" x-data="{ buka: false }">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p><span class="font-bold">{{ $i->bab }}</span> <span class="text-redup">dari {{ $i->dosen->pengguna->nama }}</span></p>
                    <x-status :s="$i->status" />
                </div>
                <p class="mt-2 whitespace-pre-line">{{ $i->uraian }}</p>
                <p class="mt-1 text-xs text-redup">Diberikan {{ $i->tanggal_dibuat->translatedFormat('d M Y') }}@if($i->tanggal_dikerjakan), dikerjakan {{ $i->tanggal_dikerjakan->translatedFormat('d M Y') }}@endif @if($i->tanggal_diterima), diterima {{ $i->tanggal_diterima->translatedFormat('d M Y') }}@endif</p>
                @if ($i->keterangan_mahasiswa)<p class="mt-2 rounded bg-dasar px-3 py-2">{{ $i->keterangan_mahasiswa }}</p>@endif
                @if ($i->dokumenPerbaikan)<p class="mt-1">Versi perbaikan: <a href="{{ route('berkas.dokumen', $i->dokumenPerbaikan) }}">{{ $i->dokumenPerbaikan->label() }}</a></p>@endif
                @if ($i->status === 'belum')
                    <button type="button" class="btn btn-kecil mt-3" x-show="!buka" @click="buka = true">Tandai dikerjakan</button>
                    <form x-show="buka" x-cloak method="post" action="{{ route('mahasiswa.revisi.tandai', $i) }}" class="mt-3 space-y-2">
                        @csrf
                        <label class="label" for="dok{{ $i->id_revisi }}">Versi dokumen perbaikan</label>
                        <select class="input" id="dok{{ $i->id_revisi }}" name="id_dokumen_perbaikan">
                            <option value="">Tidak ada berkas (dibahas langsung)</option>
                            @foreach ($dokumen as $d)<option value="{{ $d->id_dokumen }}">{{ $d->label() }}, {{ $d->tanggal_unggah->format('d/m/Y') }}</option>@endforeach
                        </select>
                        <input class="input" name="keterangan_mahasiswa" maxlength="255" placeholder="Keterangan perbaikan, misalnya halaman yang diubah" aria-label="Keterangan perbaikan">
                        <div class="flex gap-2"><button class="btn btn-utama btn-kecil" type="submit">Tandai dikerjakan</button><button type="button" class="btn btn-kecil" @click="buka = false">Batal</button></div>
                        <p class="bantuan">Belum mengunggah perbaikannya? <a href="{{ route('mahasiswa.dokumen') }}">Unggah versi baru</a> terlebih dahulu.</p>
                    </form>
                @endif
            </article>
        @empty
            <div class="panel"><x-kosong>{{ $tab === 'belum' ? 'Tidak ada revisi yang menunggu. Revisi dari dosen akan muncul di sini.' : 'Belum ada revisi pada status ini.' }}</x-kosong></div>
        @endforelse
    </div>
</x-layout>

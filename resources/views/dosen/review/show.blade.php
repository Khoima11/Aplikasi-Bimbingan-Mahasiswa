<x-layout title="Review {{ $dok->label() }}">
    <a href="{{ route('dosen.review.index') }}" class="text-sm">Kembali ke review</a>
    <h1 class="mb-1 mt-2">{{ $dok->label() }}</h1>
    <p class="mb-4 text-sm text-redup">{{ $dok->skripsi->mahasiswa->pengguna->nama }} · diunggah {{ $dok->tanggal_unggah->translatedFormat('d M Y, H:i') }}</p>
    <div class="grid gap-4 lg:grid-cols-[2fr_3fr]">
        <section class="panel h-fit">
            <div class="panel-kepala"><h2>Semua versi</h2></div>
            <ul class="divide-y divide-krem-2">
                @foreach ($versi as $v)
                    @php $rv = $v->review->firstWhere('id_dosen', $d->id_dosen); @endphp
                    <li class="flex items-center justify-between gap-2 px-4 py-2.5 text-sm {{ $v->id_dokumen === $dok->id_dokumen ? 'bg-krem' : '' }}">
                        <a href="{{ route('dosen.review.show', $v) }}" class="font-medium">{{ $v->label() }}</a>
                        <span class="flex items-center gap-2">@if($rv)<x-status :s="$rv->hasil" />@else<span class="pill pill-abu">Belum</span>@endif<a href="{{ route('berkas.dokumen', $v) }}" class="btn btn-kecil" aria-label="Unduh {{ $v->label() }}"><x-ikon n="unduh" c="size-3.5" /></a></span>
                    </li>
                @endforeach
            </ul>
        </section>
        <section class="panel panel-isi">
            <div class="mb-3 flex flex-wrap gap-2"><a href="{{ route('berkas.dokumen', $dok) }}" class="btn"><x-ikon n="unduh" c="size-4" />Unduh berkas</a></div>
            @if ($dok->catatan_mahasiswa)<p class="mb-3 rounded bg-krem px-3 py-2 text-sm">Catatan mahasiswa: {{ $dok->catatan_mahasiswa }}</p>@endif
            @if ($milik)
                <p class="text-sm">Anda telah mereview versi ini: <x-status :s="$milik->hasil" /></p>
                <p class="mt-2 whitespace-pre-line text-sm">{{ $milik->komentar }}</p>
                @if ($milik->file_koreksi)<a href="{{ route('berkas.koreksi', $milik) }}" class="btn btn-kecil mt-2">Unduh berkas koreksi</a>@endif
            @else
                <form method="post" action="{{ route('dosen.review.store', $dok) }}" enctype="multipart/form-data" class="space-y-3" x-data="{ items: [''] }">
                    @csrf
                    <fieldset><legend class="label">Hasil review</legend>
                        <div class="flex gap-4 text-sm"><label class="flex items-center gap-2"><input type="radio" name="hasil" value="revisi" checked class="accent-navy">Perlu revisi</label><label class="flex items-center gap-2"><input type="radio" name="hasil" value="disetujui" class="accent-navy">Disetujui</label></div>
                    </fieldset>
                    <div><label class="label" for="komentar">Komentar umum</label><textarea class="input" id="komentar" name="komentar" rows="4"></textarea></div>
                    <div><label class="label" for="file_koreksi">Berkas koreksi (opsional)</label><input class="input" type="file" id="file_koreksi" name="file_koreksi" accept=".pdf,.doc,.docx"><p class="bantuan">Draf yang sudah Anda beri komentar.</p></div>
                    <div>
                        <p class="label">Item revisi dari review ini</p>
                        <template x-for="(it, i) in items" :key="i"><input class="input mb-2" name="revisi[]" x-model="items[i]" :aria-label="'Item revisi ' + (i + 1)" placeholder="Uraian revisi"></template>
                        <button type="button" class="btn btn-kecil" @click="items.push('')">Tambah item</button>
                        <p class="bantuan">Setiap item akan muncul di daftar revisi mahasiswa dan dapat dilacak hingga diterima.</p>
                    </div>
                    <button class="btn btn-utama" type="submit">Simpan review</button>
                </form>
            @endif
            @foreach ($dok->review->where('id_dosen', '!=', $d->id_dosen) as $r)
                <div class="mt-4 rounded-2xl bg-krem p-4 text-sm"><p class="font-semibold">Review {{ $r->dosen->pengguna->nama }} <x-status :s="$r->hasil" /></p><p class="mt-1 text-redup">{{ $r->komentar }}</p></div>
            @endforeach
        </section>
    </div>
</x-layout>

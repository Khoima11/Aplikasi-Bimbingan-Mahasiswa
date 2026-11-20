<x-layout title="{{ $dok->label() }}">
    <a href="{{ route('mahasiswa.dokumen') }}" class="text-sm">Kembali ke dokumen</a>
    <div class="mb-4 mt-2 flex flex-wrap items-center justify-between gap-2">
        <h1>{{ $dok->label() }}</h1>
        <a href="{{ route('berkas.dokumen', $dok) }}" class="btn"><x-ikon n="unduh" c="size-4" />Unduh berkas</a>
    </div>
    <p class="mb-4 text-sm text-redup">Diunggah {{ $dok->tanggal_unggah->translatedFormat('d F Y, H:i') }}{{ $dok->catatan_mahasiswa ? '. '.$dok->catatan_mahasiswa : '' }}</p>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($s->pembimbing as $p)
            @php $r = $dok->reviewDari($p->id_dosen); @endphp
            <section class="panel">
                <div class="panel-kepala"><h2>{{ $p->label() }}</h2>@if($r)<x-status :s="$r->hasil" />@endif</div>
                <div class="panel-isi text-sm">
                    <p class="text-redup">{{ $p->dosen->pengguna->nama }}</p>
                    @if ($r)
                        <p class="mt-2 whitespace-pre-line">{{ $r->komentar ?: 'Tanpa komentar umum.' }}</p>
                        @if ($r->file_koreksi)<a href="{{ route('berkas.koreksi', $r) }}" class="btn btn-kecil mt-3"><x-ikon n="unduh" c="size-3.5" />Unduh berkas koreksi</a>@endif
                        @if ($r->revisi->count())
                            <p class="mt-3 font-semibold">Item revisi dari review ini</p>
                            <ul class="ml-4 list-disc">@foreach ($r->revisi as $i)<li>{{ $i->uraian }} <x-status :s="$i->status" /></li>@endforeach</ul>
                        @endif
                        <p class="mt-3 text-xs text-redup">Direview {{ $r->tanggal_review->translatedFormat('d M Y') }}</p>
                    @else
                        <p class="mt-2">Belum direview.</p>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</x-layout>

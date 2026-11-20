{{-- Jalur tahapan skripsi: benang dengan simpul; simpul emas = tahap berjalan --}}
@props(['tahapan', 'urutan' => 1, 'selesai' => false, 'ringkas' => false])
<ol class="relative flex items-start justify-between" aria-label="Tahapan skripsi">
    <span class="absolute left-3 right-3 top-[11px] h-[3px] rounded bg-garis" aria-hidden="true"></span>
    <span class="absolute left-3 top-[11px] h-[3px] rounded bg-marun-700" aria-hidden="true" style="width: calc((100% - 1.5rem) * {{ max(0, min(1, ($urutan - 1) / max(1, $tahapan->count() - 1))) }})"></span>
    @foreach ($tahapan as $t)
        @php $done = $t->urutan < $urutan || ($selesai && $t->urutan == $urutan); $now = ! $selesai && $t->urutan == $urutan; @endphp
        <li class="relative z-10 flex w-0 flex-1 flex-col items-center text-center first:items-start first:text-left last:items-end last:text-right" @if($now) aria-current="step" @endif>
            @if ($now)
                <span class="grid size-[25px] place-items-center rounded-full bg-emas ring-4 ring-emas-50"><span class="size-2 rounded-full bg-white"></span></span>
            @elseif ($done)
                <span class="grid size-[25px] place-items-center rounded-full bg-marun-700 text-white"><svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5 9-10"/></svg></span>
            @else
                <span class="size-[25px] rounded-full border-[3px] border-garis bg-white"></span>
            @endif
            <span class="mt-2 hidden text-xs leading-tight sm:block {{ $now ? 'font-bold text-tinta' : 'text-redup' }}">{{ $t->nama_tahapan }}</span>
        </li>
    @endforeach
</ol>
@unless ($ringkas)
    <p class="mt-3 text-sm sm:hidden"><span class="text-redup">Tahap {{ $urutan }} dari {{ $tahapan->count() }}:</span> <strong>{{ $tahapan->firstWhere('urutan', $urutan)?->nama_tahapan }}</strong></p>
@endunless

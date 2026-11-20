{{-- Jalur tahapan skripsi, dirancang untuk kartu navy. Simpul krem = tahap berjalan. --}}
@props(['tahapan', 'urutan' => 1, 'selesai' => false, 'ringkas' => false])
@php $n = max(1, $tahapan->count() - 1); $prog = $selesai ? 1 : max(0, min(1, ($urutan - 1) / $n)); @endphp
<ol class="relative flex items-start justify-between" aria-label="Tahapan skripsi">
    <span class="absolute left-3.5 right-3.5 top-[13px] h-[3px] rounded-full bg-white/15" aria-hidden="true"></span>
    <span class="absolute left-3.5 top-[13px] h-[3px] rounded-full bg-abu" aria-hidden="true" style="width: calc((100% - 1.75rem) * {{ $prog }})"></span>
    @foreach ($tahapan as $t)
        @php $done = $t->urutan < $urutan || ($selesai && $t->urutan == $urutan); $now = ! $selesai && $t->urutan == $urutan; @endphp
        <li class="relative z-10 flex w-0 flex-1 flex-col items-center text-center first:items-start first:text-left last:items-end last:text-right" @if($now) aria-current="step" @endif>
            @if ($now)
                <span class="grid size-7 place-items-center rounded-full bg-krem ring-[5px] ring-white/15"><span class="size-2.5 rounded-full bg-navy"></span></span>
            @elseif ($done)
                <span class="grid size-7 place-items-center rounded-full bg-slate text-white"><svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5 9-10"/></svg></span>
            @else
                <span class="size-7 rounded-full border-[3px] border-white/20 bg-navy"></span>
            @endif
            <span class="mt-2.5 hidden max-w-24 text-xs leading-tight sm:block {{ $now ? 'font-semibold text-white' : 'text-white/55' }}">{{ $t->nama_tahapan }}</span>
        </li>
    @endforeach
</ol>
@unless ($ringkas)
    <p class="mt-4 text-sm text-white/70 sm:hidden">Tahap {{ $urutan }} dari {{ $tahapan->count() }} · <span class="font-semibold text-white">{{ $tahapan->firstWhere('urutan', $urutan)?->nama_tahapan }}</span></p>
@endunless

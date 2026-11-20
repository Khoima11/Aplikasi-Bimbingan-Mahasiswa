{{-- Kartu angka bergaya kartu menu referensi: slate, angka besar, tombol panah bulat. --}}
@props(['label', 'nilai', 'href' => null, 'tegas' => false, 'terang' => false])
<div {{ $attributes->merge(['class' => ($terang ? 'bg-white text-tinta' : 'bg-slate text-white').' relative flex min-h-[120px] flex-col justify-between gap-3 rounded-[var(--radius-panel)] p-4 sm:p-5']) }}>
    <p class="text-sm {{ $terang ? 'text-redup' : 'text-white/70' }}">{{ $label }}</p>
    <div class="flex items-end justify-between gap-2">
        <p class="text-[34px] font-bold leading-none tracking-tight">{{ $nilai }}</p>
        @if ($href)
            <a href="{{ $href }}" class="{{ $terang ? 'grid size-10 shrink-0 place-items-center rounded-full bg-navy text-white no-underline' : 'bulat-panah' }}" aria-label="{{ $slot->isEmpty() ? 'Lihat' : $slot }} {{ strtolower($label) }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        @endif
    </div>
    @if ($tegas && $nilai && $nilai !== '-')<span class="absolute right-4 top-4 size-2.5 rounded-full {{ $terang ? 'bg-merah' : 'bg-krem' }}" aria-hidden="true"></span>@endif
</div>

@props(['label', 'nilai', 'href' => null, 'tegas' => false])
<div {{ $attributes->merge(['class' => 'panel flex flex-col justify-between gap-2 p-4']) }}>
    <p class="text-sm text-redup">{{ $label }}</p>
    <div class="flex items-end justify-between gap-2">
        <p class="text-3xl font-bold leading-none {{ $tegas && $nilai ? 'text-marun-700' : '' }}">{{ $nilai }}</p>
        @if ($href)<a href="{{ $href }}" class="text-sm font-semibold">{{ $slot->isEmpty() ? 'Lihat' : $slot }}</a>@endif
    </div>
</div>

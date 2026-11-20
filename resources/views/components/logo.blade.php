{{-- Logo SIBIMTA. Bila public/logo.png ada (logo resmi), berkas itu yang dipakai. --}}
@props(['terang' => false])
@if (file_exists(public_path('logo.png')))
    <img src="{{ asset('logo.png') }}?v={{ filemtime(public_path('logo.png')) }}" alt="" {{ $attributes->merge(['class' => 'object-contain']) }}>
@else
    <svg {{ $attributes }} viewBox="0 0 40 40" aria-hidden="true">
        <rect width="40" height="40" rx="12" fill="{{ $terang ? '#F2F1EE' : '#1A2235' }}"/>
        <path d="M9 13.5c3.7-1.8 7.4-1.5 11 1 3.6-2.5 7.3-2.8 11-1v15c-3.7-1.8-7.4-1.5-11 1-3.6-2.5-7.3-2.8-11-1z" fill="none" stroke="{{ $terang ? '#1A2235' : '#F2F1EE' }}" stroke-width="2.2" stroke-linejoin="round"/>
        <path d="M20 14.5v15" stroke="{{ $terang ? '#1A2235' : '#F2F1EE' }}" stroke-width="2.2"/>
        <circle cx="20" cy="21.5" r="3" fill="#A8AAAD"/>
    </svg>
@endif

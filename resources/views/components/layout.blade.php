@props(['title' => null, 'judul' => null])
@php
    $u = auth()->user();
    $peranAktif = session('peran_aktif');
    $menu = \App\Support\Menu::untuk($peranAktif);
    $daftarPeran = $u?->daftarPeran() ?? [];
    $belumDibaca = $u ? $u->notifikasi()->where('sudah_dibaca', false)->count() : 0;
    $aktif = fn ($pola) => request()->routeIs($pola);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7A1F3D">
    <title>{{ $title ? $title.' · ' : '' }}SIBIMTA</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh" x-data="{ laci: false }">
<a href="#isi" class="sr-only focus:not-sr-only focus:fixed focus:left-2 focus:top-2 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2">Lewati ke isi</a>

{{-- Bilah atas --}}
<header class="sticky top-0 z-30 border-b border-garis bg-white/95 backdrop-blur">
    <div class="flex h-14 items-center gap-3 px-4 lg:px-6">
        <a href="{{ route('beranda') }}" class="flex items-center gap-2 text-tinta no-underline">
            <x-logo class="size-8" />
            <span class="text-[17px] font-bold tracking-tight">SIBIMTA</span>
        </a>
        <div class="flex-1"></div>
        @if (count($daftarPeran) > 1)
            <form method="post" action="{{ route('peran.ganti') }}" class="hidden sm:block">
                @csrf
                <label class="sr-only" for="peran">Peran aktif</label>
                <select id="peran" name="peran" onchange="this.form.submit()" class="rounded-full border border-garis bg-dasar py-1 pl-3 pr-8 text-xs font-semibold">
                    @foreach ($daftarPeran as $p)
                        <option value="{{ $p }}" @selected($p === $peranAktif)>Sebagai {{ \App\Models\Peran::LABEL[$p] }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <a href="{{ route('notifikasi') }}" class="relative rounded-md p-2 text-redup hover:bg-dasar hover:text-tinta" aria-label="Notifikasi, {{ $belumDibaca }} belum dibaca">
            <x-ikon n="lonceng" />
            @if ($belumDibaca)
                <span class="absolute right-1 top-1 grid min-w-4 place-items-center rounded-full bg-marun-700 px-1 text-[10px] font-bold leading-4 text-white">{{ $belumDibaca > 99 ? '99+' : $belumDibaca }}</span>
            @endif
        </a>
        <a href="{{ route('akun') }}" class="hidden items-center gap-2 rounded-md px-2 py-1 text-sm font-medium text-tinta no-underline hover:bg-dasar md:flex">
            <span class="grid size-8 place-items-center rounded-full bg-marun-50 text-xs font-bold text-marun-700">{{ collect(explode(' ', \Illuminate\Support\Str::before($u->nama, ',')))->filter(fn($w)=>$w !== '' && ! str_ends_with($w, '.') && ctype_alpha($w[0]))->take(2)->map(fn($w)=>mb_strtoupper(mb_substr($w,0,1)))->implode('') }}</span>
            <span class="max-w-44 truncate">{{ $u->nama }}</span>
        </a>
        <form method="post" action="{{ route('logout') }}" class="hidden md:block">@csrf<button class="btn btn-kecil" type="submit"><x-ikon n="keluar" c="size-4" />Keluar</button></form>
    </div>
</header>

<div class="lg:flex">
    {{-- Sidebar laptop --}}
    <nav class="sticky top-14 hidden h-[calc(100dvh-3.5rem)] w-60 shrink-0 flex-col gap-1 overflow-y-auto border-r border-garis bg-white p-3 lg:flex" aria-label="Menu utama">
        <p class="px-3 pb-1 pt-2 text-xs font-semibold text-redup">{{ \App\Models\Peran::LABEL[$peranAktif] ?? '' }}</p>
        @foreach ($menu as [$label, $route, $ikon, $pola, $pendek])
            <a href="{{ route($route) }}" class="nav-item {{ $aktif($pola) ? 'nav-aktif' : '' }}" @if($aktif($pola)) aria-current="page" @endif><x-ikon :n="$ikon" />{{ $label }}</a>
        @endforeach
        <div class="mt-auto border-t border-garis pt-3 text-xs text-redup">
            <p class="px-3">{{ \App\Models\Pengaturan::nilai('nama_prodi') }}</p>
        </div>
    </nav>

    <main id="isi" class="min-w-0 flex-1 px-4 pb-28 pt-5 lg:px-8 lg:pb-10">
        <div class="mx-auto max-w-6xl">
            @if ($judul)
                <h1 class="mb-4">{{ $judul }}</h1>
            @endif
            @if (session('ok'))
                <div role="status" class="mb-4 rounded-md border border-hijau/30 bg-hijau-50 px-4 py-3 text-sm text-hijau">{{ session('ok') }}</div>
            @endif
            @if (session('gagal'))
                <div role="alert" class="mb-4 rounded-md border border-merah/30 bg-merah-50 px-4 py-3 text-sm text-merah">{{ session('gagal') }}</div>
            @endif
            @if ($errors->any() && ! ($sembunyikanGalat ?? false))
                <div role="alert" class="mb-4 rounded-md border border-merah/30 bg-merah-50 px-4 py-3 text-sm text-merah">
                    <p class="font-semibold">Periksa kembali isian berikut:</p>
                    <ul class="ml-4 list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            {{ $slot }}
        </div>
    </main>
</div>

{{-- Navigasi bawah HP --}}
<nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t border-garis bg-white pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Menu utama">
    @foreach (array_slice($menu, 0, 4) as [$label, $route, $ikon, $pola, $pendek])
        <a href="{{ route($route) }}" class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium no-underline {{ $aktif($pola) ? 'text-marun-700' : 'text-redup' }}">
            <x-ikon :n="$ikon" /><span class="max-w-full truncate px-1">{{ $pendek }}</span>
        </a>
    @endforeach
    <button type="button" @click="laci = true" class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium text-redup"><x-ikon n="lainnya" /><span>Lainnya</span></button>
</nav>

{{-- Laci menu HP --}}
<div x-cloak x-show="laci" class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="absolute inset-0 bg-tinta/40" @click="laci = false"></div>
    <div class="absolute inset-x-0 bottom-0 max-h-[85dvh] overflow-y-auto rounded-t-2xl bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))]" x-show="laci" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-full" @keydown.escape.window="laci = false">
        <div class="mb-3 flex items-center justify-between">
            <div><p class="font-semibold">{{ $u->nama }}</p><p class="text-xs text-redup">{{ $u->username }}</p></div>
            <button type="button" class="btn btn-kecil" @click="laci = false" aria-label="Tutup menu"><x-ikon n="tutup" c="size-4" /></button>
        </div>
        @if (count($daftarPeran) > 1)
            <form method="post" action="{{ route('peran.ganti') }}" class="mb-3">
                @csrf
                <p class="mb-1 text-xs font-semibold text-redup">Masuk sebagai</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($daftarPeran as $p)
                        <button name="peran" value="{{ $p }}" class="btn btn-kecil {{ $p === $peranAktif ? 'btn-utama' : '' }}">{{ \App\Models\Peran::LABEL[$p] }}</button>
                    @endforeach
                </div>
            </form>
        @endif
        <div class="grid gap-1">
            @foreach ($menu as [$label, $route, $ikon, $pola, $pendek])
                <a href="{{ route($route) }}" class="nav-item {{ $aktif($pola) ? 'nav-aktif' : '' }}"><x-ikon :n="$ikon" />{{ $label }}</a>
            @endforeach
            <a href="{{ route('notifikasi') }}" class="nav-item"><x-ikon n="lonceng" />Notifikasi</a>
            <a href="{{ route('akun') }}" class="nav-item"><x-ikon n="orang" />Akun dan kata sandi</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-item w-full" type="submit"><x-ikon n="keluar" />Keluar</button></form>
        </div>
    </div>
</div>
@stack('skrip')
</body>
</html>

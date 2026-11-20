@props(['title' => null, 'judul' => null])
@php
    $u = auth()->user();
    $peranAktif = session('peran_aktif');
    $menu = \App\Support\Menu::untuk($peranAktif);
    $daftarPeran = $u?->daftarPeran() ?? [];
    $belumDibaca = $u ? $u->notifikasi()->where('sudah_dibaca', false)->count() : 0;
    $aktif = fn ($pola) => request()->routeIs($pola);
    $inisial = collect(explode(' ', \Illuminate\Support\Str::before($u->nama, ',')))->filter(fn($w)=>$w !== '' && ! str_ends_with($w, '.') && ctype_alpha($w[0]))->take(2)->map(fn($w)=>mb_strtoupper(mb_substr($w,0,1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F2F1EE">
    <title>{{ $title ? $title.' · ' : '' }}SIBIMTA</title>
    <link rel="icon" href="{{ asset('favicon-192.png') }}" type="image/png"><link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh" x-data="{ laci: false }">
<a href="#isi" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-2">Lewati ke isi</a>

<div class="lg:flex lg:gap-6 lg:p-4">
    {{-- ============ Sidebar laptop ============ --}}
    <aside class="kartu-navy sticky top-4 hidden h-[calc(100dvh-2rem)] w-[260px] shrink-0 flex-col p-5 lg:flex" aria-label="Menu utama">
        <a href="{{ route('beranda') }}" class="mb-8 flex items-center gap-3 px-2 text-white no-underline">
            <span class="grid size-11 place-items-center rounded-2xl bg-krem p-1"><x-logo class="size-full" /></span>
            <span class="text-[22px] font-extrabold tracking-tight">SIBIMTA</span>
        </a>
        <p class="mb-2 px-4 text-xs text-white/45">{{ \App\Models\Peran::LABEL[$peranAktif] ?? '' }}</p>
        <nav class="flex flex-col gap-1">
            @foreach ($menu as [$label, $route, $ikon, $pola, $pendek])
                <a href="{{ route($route) }}" class="nav-item {{ $aktif($pola) ? 'nav-aktif' : '' }}" @if($aktif($pola)) aria-current="page" @endif><x-ikon :n="$ikon" />{{ $label }}</a>
            @endforeach
            <a href="{{ route('notifikasi') }}" class="nav-item {{ $aktif('notifikasi') ? 'nav-aktif' : '' }}"><x-ikon n="lonceng" />Notifikasi
                @if ($belumDibaca)<span class="ml-auto rounded-full bg-white px-2 text-xs font-bold text-navy">{{ $belumDibaca }}</span>@endif</a>
        </nav>
        <div class="mt-auto rounded-[20px] bg-white/[.06] p-3">
            <a href="{{ route('akun') }}" class="flex items-center gap-3 text-white no-underline">
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-slate text-sm font-semibold">{{ $inisial }}</span>
                <span class="min-w-0"><span class="block truncate text-sm font-medium">{{ $u->nama }}</span><span class="block text-xs text-white/50">{{ $u->username }}</span></span>
            </a>
            @if (count($daftarPeran) > 1)
                <form method="post" action="{{ route('peran.ganti') }}" class="mt-3">
                    @csrf
                    <label class="sr-only" for="peran-d">Peran aktif</label>
                    <select id="peran-d" name="peran" onchange="this.form.submit()" class="w-full rounded-full border-0 bg-white/10 px-3 py-1.5 text-xs text-white focus:ring-2 focus:ring-white/40">
                        @foreach ($daftarPeran as $p)<option class="text-navy" value="{{ $p }}" @selected($p === $peranAktif)>Sebagai {{ \App\Models\Peran::LABEL[$p] }}</option>@endforeach
                    </select>
                </form>
            @endif
            <form method="post" action="{{ route('logout') }}" class="mt-2">@csrf<button class="flex w-full items-center gap-2 rounded-full px-3 py-1.5 text-xs text-white/60 hover:bg-white/10 hover:text-white" type="submit"><x-ikon n="keluar" c="size-4" />Keluar</button></form>
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        {{-- ============ Bilah atas HP ============ --}}
        <header class="sticky top-0 z-30 flex items-center gap-3 bg-krem/90 px-5 pb-3 pt-[max(.9rem,env(safe-area-inset-top))] backdrop-blur lg:hidden">
            <button type="button" class="-ml-1 rounded-full p-1.5 text-navy" @click="laci = true" aria-label="Buka menu"><x-ikon n="menu" c="size-6" /></button>
            <a href="{{ route('beranda') }}" class="flex items-center gap-2 text-navy no-underline"><x-logo class="size-8" /><span class="text-xl font-extrabold tracking-tight">SIBIMTA</span></a>
            <div class="flex-1"></div>
            <a href="{{ route('notifikasi') }}" class="relative rounded-full p-1.5 text-navy" aria-label="Notifikasi, {{ $belumDibaca }} belum dibaca">
                <x-ikon n="lonceng" c="size-6" />
                @if ($belumDibaca)<span class="absolute right-0.5 top-0.5 grid min-w-4 place-items-center rounded-full bg-merah px-1 text-[10px] font-bold leading-4 text-white">{{ $belumDibaca > 9 ? '9+' : $belumDibaca }}</span>@endif
            </a>
        </header>

        {{-- ============ Bilah atas laptop ============ --}}
        <header class="hidden items-center gap-3 px-2 pb-2 pt-3 lg:flex">
            <p class="text-sm text-redup">{{ now()->translatedFormat('l, d F Y') }}</p>
            <div class="flex-1"></div>
            <a href="{{ route('notifikasi') }}" class="relative grid size-11 place-items-center rounded-full bg-white text-navy no-underline" aria-label="Notifikasi, {{ $belumDibaca }} belum dibaca">
                <x-ikon n="lonceng" />
                @if ($belumDibaca)<span class="absolute right-1.5 top-1.5 grid min-w-4 place-items-center rounded-full bg-merah px-1 text-[10px] font-bold leading-4 text-white">{{ $belumDibaca > 99 ? '99+' : $belumDibaca }}</span>@endif
            </a>
            <a href="{{ route('akun') }}" class="flex items-center gap-2 rounded-full bg-white py-1 pl-1 pr-4 text-sm font-medium text-navy no-underline">
                <span class="grid size-9 place-items-center rounded-full bg-navy text-xs font-semibold text-white">{{ $inisial }}</span>{{ \Illuminate\Support\Str::limit($u->nama, 26) }}
            </a>
        </header>

        <main id="isi" class="px-5 pb-32 pt-2 lg:px-2 lg:pb-10 lg:pt-4">
            <div class="mx-auto max-w-6xl">
                @if ($judul)<h1 class="mb-5">{{ $judul }}</h1>@endif
                @if (session('ok'))
                    <div role="status" class="mb-4 flex items-start gap-3 rounded-2xl bg-hijau-50 px-4 py-3 text-sm text-hijau"><svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5 9-10"/></svg><span>{{ session('ok') }}</span></div>
                @endif
                @if (session('gagal'))
                    <div role="alert" class="mb-4 rounded-2xl bg-merah-50 px-4 py-3 text-sm text-merah">{{ session('gagal') }}</div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="mb-4 rounded-2xl bg-merah-50 px-4 py-3 text-sm text-merah">
                        <p class="font-semibold">Periksa kembali isian berikut:</p>
                        <ul class="ml-4 list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

{{-- ============ Navigasi bawah HP (navy, ikon aktif di lingkaran krem) ============ --}}
<nav class="fixed inset-x-0 bottom-0 z-30 rounded-t-[28px] bg-navy px-3 pb-[max(.6rem,env(safe-area-inset-bottom))] pt-2.5 lg:hidden" aria-label="Menu utama">
    <div class="mx-auto grid max-w-md grid-cols-5">
        @foreach (array_slice($menu, 0, 4) as [$label, $route, $ikon, $pola, $pendek])
            @php $on = $aktif($pola); @endphp
            <a href="{{ route($route) }}" class="group flex flex-col items-center gap-1 text-[11px] no-underline {{ $on ? 'text-white' : 'text-white/55' }}" @if($on) aria-current="page" @endif>
                <span class="grid size-11 place-items-center rounded-full transition {{ $on ? '-translate-y-4 bg-krem text-navy ring-[5px] ring-navy' : '' }}"><x-ikon :n="$ikon" c="size-[22px]" /></span>
                <span class="-mt-1 {{ $on ? '-translate-y-2 font-semibold' : '' }}">{{ $pendek }}</span>
            </a>
        @endforeach
        <button type="button" @click="laci = true" class="flex flex-col items-center gap-1 text-[11px] text-white/55"><span class="grid size-11 place-items-center"><x-ikon n="orang" c="size-[22px]" /></span><span class="-mt-1">Akun</span></button>
    </div>
</nav>

{{-- ============ Laci menu HP ============ --}}
<div x-cloak x-show="laci" class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="absolute inset-0 bg-navy/50" @click="laci = false" x-show="laci" x-transition.opacity></div>
    <div class="absolute inset-x-0 bottom-0 max-h-[88dvh] overflow-y-auto rounded-t-[28px] bg-white p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))]" x-show="laci" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full" @keydown.escape.window="laci = false">
        <div class="mx-auto mb-4 h-1.5 w-10 rounded-full bg-krem-2"></div>
        <div class="mb-4 flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-full bg-navy font-semibold text-white">{{ $inisial }}</span>
            <div class="min-w-0 flex-1"><p class="truncate font-semibold">{{ $u->nama }}</p><p class="text-sm text-redup">{{ $u->username }}</p></div>
            <button type="button" class="grid size-9 place-items-center rounded-full bg-krem" @click="laci = false" aria-label="Tutup menu"><x-ikon n="tutup" c="size-4" /></button>
        </div>
        @if (count($daftarPeran) > 1)
            <form method="post" action="{{ route('peran.ganti') }}" class="mb-4">
                @csrf
                <p class="mb-2 text-sm text-redup">Masuk sebagai</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($daftarPeran as $p)<button name="peran" value="{{ $p }}" class="btn btn-kecil {{ $p === $peranAktif ? 'btn-utama' : '' }}">{{ \App\Models\Peran::LABEL[$p] }}</button>@endforeach
                </div>
            </form>
        @endif
        <div class="grid gap-0.5">
            @foreach ($menu as [$label, $route, $ikon, $pola, $pendek])
                <a href="{{ route($route) }}" class="nav-terang {{ $aktif($pola) ? 'bg-krem font-semibold' : '' }}"><x-ikon :n="$ikon" />{{ $label }}</a>
            @endforeach
            <a href="{{ route('notifikasi') }}" class="nav-terang"><x-ikon n="lonceng" />Notifikasi @if($belumDibaca)<span class="pill pill-merah ml-auto">{{ $belumDibaca }} baru</span>@endif</a>
            <a href="{{ route('akun') }}" class="nav-terang"><x-ikon n="orang" />Akun dan kata sandi</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-terang w-full text-merah" type="submit"><x-ikon n="keluar" />Keluar</button></form>
        </div>
    </div>
</div>
@stack('skrip')
</body>
</html>

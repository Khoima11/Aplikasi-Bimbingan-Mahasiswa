@extends('layouts.polos', ['title' => 'Masuk · SIBIMTA'])
@section('isi')
<div class="min-h-dvh p-4 lg:grid lg:grid-cols-[1.05fr_1fr] lg:gap-4">
    {{-- Panel navy (laptop) --}}
    <section class="kartu-navy relative hidden overflow-hidden p-12 lg:flex lg:flex-col lg:justify-between">
        <svg class="pointer-events-none absolute -bottom-24 -right-24 size-[420px] text-white/[.04]" viewBox="0 0 200 200" aria-hidden="true"><circle cx="100" cy="100" r="100" fill="currentColor"/></svg>
        <div class="flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-2xl bg-krem p-1"><x-logo class="size-full" /></span>
            <span class="text-2xl font-extrabold tracking-tight">SIBIMTA</span>
        </div>
        <div class="relative max-w-lg">
            <p class="text-[44px] font-bold leading-[1.1] tracking-tight">Bimbingan skripsi yang tercatat, bukan tercecer.</p>
            <p class="mt-5 text-lg text-white/65">Ajukan judul, atur jadwal dengan pembimbing, catat setiap pertemuan, dan pantau revisi sampai tuntas, dari laptop atau HP.</p>
        </div>
        <div class="relative grid grid-cols-3 gap-3 text-sm">
            @foreach (['Judul & pembimbing', 'Jadwal & catatan', 'Revisi & laporan'] as $i => $t)
                <div class="rounded-[20px] {{ $i === 1 ? 'bg-krem text-navy' : 'bg-white/[.07] text-white/80' }} p-4"><p class="text-2xl font-bold">{{ $i + 1 }}</p><p class="mt-1">{{ $t }}</p></div>
            @endforeach
        </div>
    </section>

    {{-- Formulir --}}
    <section class="flex min-h-[calc(100dvh-2rem)] items-center justify-center">
        <div class="w-full max-w-[400px]">
            <img src="{{ asset('logo-sibimta.png') }}" alt="SIBIMTA" class="mx-auto mb-6 w-36 sm:w-44 lg:w-56">
            <div class="panel p-6 sm:p-8">
                <h1 class="!text-2xl">Masuk</h1>
                <p class="mt-1 text-sm text-redup">Gunakan NIM (mahasiswa) atau NIP/NIDN (dosen).</p>
                <form method="post" action="{{ url('/login') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="username">Username</label>
                        <input class="input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus placeholder="NIM atau NIP">
                        @error('username')<p class="galat">{{ $message }}</p>@enderror
                    </div>
                    <div x-data="{ lihat: false }">
                        <label class="label" for="password">Kata sandi</label>
                        <div class="relative">
                            <input class="input pr-24" id="password" name="password" :type="lihat ? 'text' : 'password'" type="password" autocomplete="current-password" required>
                            <button type="button" class="absolute inset-y-0 right-3 text-xs font-medium text-slate" @click="lihat = !lihat" x-text="lihat ? 'Sembunyikan' : 'Lihat'">Lihat</button>
                        </div>
                        @error('password')<p class="galat">{{ $message }}</p>@enderror
                    </div>
                    <button class="btn btn-utama w-full py-3 text-base" type="submit">Masuk</button>
                </form>
            </div>
            <x-pasang varian="kartu" class="mt-4" />
            <p class="mt-6 text-center text-sm text-redup">Lupa kata sandi? Hubungi admin program studi.</p>
            <p class="mt-1 text-center text-sm"><a href="{{ route('verifikasi') }}">Periksa keaslian lembar bimbingan</a></p>
        </div>
    </section>
</div>
@endsection

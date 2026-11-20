@extends('layouts.polos', ['title' => 'Masuk · SIBIMTA'])
@section('isi')
<div class="grid min-h-dvh lg:grid-cols-[1.1fr_1fr]">
    <section class="relative hidden overflow-hidden bg-marun-800 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="flex items-center gap-3"><x-logo class="size-10" /><span class="text-lg font-bold">SIBIMTA</span></div>
        <div class="max-w-md">
            <p class="text-3xl font-bold leading-tight">Bimbingan skripsi yang tercatat, bukan tercecer.</p>
            <p class="mt-4 text-white/75">Ajukan judul, atur jadwal dengan pembimbing, catat setiap pertemuan, dan pantau revisi sampai tuntas, dari laptop atau HP.</p>
        </div>
        {{-- benang tahapan sebagai ornamen --}}
        <svg class="w-full max-w-lg" viewBox="0 0 520 60" aria-hidden="true">
            <path d="M10 30 C 90 5, 150 55, 230 30 S 370 5, 510 30" fill="none" stroke="#C8962E" stroke-width="3" opacity=".9"/>
            @foreach ([10, 110, 210, 310, 410, 510] as $i => $x)
                <circle cx="{{ $x }}" cy="{{ [30,22,33,27,20,30][$i] }}" r="{{ $i == 3 ? 9 : 6 }}" fill="{{ $i < 3 ? '#C8962E' : ($i == 3 ? '#fff' : '#5F1730') }}" stroke="#C8962E" stroke-width="3"/>
            @endforeach
        </svg>
        <p class="text-sm text-white/60">{{ \App\Models\Pengaturan::nilai('nama_prodi') }} · {{ \App\Models\Pengaturan::nilai('nama_institusi') }}</p>
    </section>
    <section class="flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex items-center gap-3 lg:hidden"><x-logo class="size-10" /><div><p class="text-lg font-bold">SIBIMTA</p><p class="text-xs text-redup">Sistem Informasi Bimbingan Skripsi</p></div></div>
            <h1>Masuk</h1>
            <p class="mt-1 text-sm text-redup">Gunakan NIM (mahasiswa) atau NIP/NIDN (dosen).</p>
            <form method="post" action="{{ url('/login') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="label" for="username">Username</label>
                    <input class="input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus inputmode="text">
                    @error('username')<p class="galat">{{ $message }}</p>@enderror
                </div>
                <div x-data="{ lihat: false }">
                    <label class="label" for="password">Kata sandi</label>
                    <div class="relative">
                        <input class="input pr-16" id="password" name="password" :type="lihat ? 'text' : 'password'" type="password" autocomplete="current-password" required>
                        <button type="button" class="absolute inset-y-0 right-2 text-xs font-semibold text-redup" @click="lihat = !lihat" x-text="lihat ? 'Sembunyikan' : 'Lihat'">Lihat</button>
                    </div>
                    @error('password')<p class="galat">{{ $message }}</p>@enderror
                </div>
                <button class="btn btn-utama w-full py-2.5" type="submit">Masuk</button>
            </form>
            <p class="mt-6 text-sm text-redup">Lupa kata sandi? Hubungi admin program studi untuk mengatur ulang.</p>
            <p class="mt-2 text-sm"><a href="{{ route('verifikasi') }}">Periksa keaslian lembar bimbingan</a></p>
        </div>
    </section>
</div>
@endsection

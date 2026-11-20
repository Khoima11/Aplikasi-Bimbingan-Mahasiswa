@extends('layouts.polos', ['title' => $judul.' · SIBIMTA'])
@section('isi')
<div class="grid min-h-dvh place-items-center p-6">
    <div class="max-w-md text-center">
        <x-logo class="mx-auto size-12" />
        <p class="mt-6 text-5xl font-bold text-marun-700">{{ $kode }}</p>
        <h1 class="mt-2">{{ $judul }}</h1>
        <p class="mt-2 text-sm text-redup">{{ $pesan }}</p>
        <a href="{{ url('/') }}" class="btn btn-utama mt-6">Kembali ke beranda</a>
    </div>
</div>
@endsection

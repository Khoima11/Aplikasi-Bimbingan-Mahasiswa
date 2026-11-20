@extends('layouts.polos', ['title' => 'Verifikasi lembar bimbingan · SIBIMTA'])
@section('isi')
<div class="mx-auto max-w-lg p-5 pt-10">
    <img src="{{ asset('logo-sibimta.png') }}" alt="SIBIMTA" class="mx-auto mb-6 w-36">
    <h1 class="text-center">Verifikasi lembar bimbingan</h1>
    <form method="get" action="{{ route('verifikasi') }}" class="mt-4 flex gap-2">
        <label for="kode" class="sr-only">Kode verifikasi</label>
        <input id="kode" name="kode" value="{{ $kode }}" placeholder="Contoh: AB12-CD34-EF56" class="input uppercase">
        <button class="btn btn-utama" type="submit">Periksa</button>
    </form>
    @if ($kode)
        @if ($lembar)
            <div class="panel mt-6 overflow-hidden">
                <div class="flex items-center gap-3 bg-hijau-50 px-5 py-5 text-hijau">
                    <svg class="size-9 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m7.5 12 3 3 6-6"/></svg>
                    <div><p class="text-lg font-bold">Lembar bimbingan asli</p><p class="text-sm">Kode {{ $lembar->kode_verifikasi }} tercatat di SIBIMTA.</p></div>
                </div>
                <dl class="grid grid-cols-[9rem_1fr] gap-y-2 p-4 text-sm">
                    <dt class="text-redup">Nama</dt><dd class="font-medium">{{ $lembar->skripsi->mahasiswa->pengguna->nama }}</dd>
                    <dt class="text-redup">NIM</dt><dd>{{ $lembar->skripsi->mahasiswa->nim }}</dd>
                    <dt class="text-redup">Judul</dt><dd>{{ $lembar->skripsi->judul_final }}</dd>
                    <dt class="text-redup">Bimbingan tervalidasi</dt><dd>{{ $lembar->jumlah_bimbingan }} kali</dd>
                    <dt class="text-redup">Tahap saat dicetak</dt><dd>{{ $lembar->tahap_saat_cetak }}</dd>
                    <dt class="text-redup">Tanggal cetak</dt><dd>{{ $lembar->tanggal_cetak->translatedFormat('d F Y, H:i') }}</dd>
                </dl>
                <p class="border-t border-garis px-4 py-3 text-xs text-redup">Halaman ini hanya menampilkan data ringkas. Isi catatan bimbingan tidak ditampilkan untuk menjaga kerahasiaan data mahasiswa. Pastikan isi lembar cetak sesuai dengan data di atas.</p>
            </div>
        @else
            <div class="mt-6 rounded-xl border border-merah/30 bg-merah-50 p-4 text-sm text-merah">
                <p class="font-bold">Kode tidak ditemukan</p>
                <p>Lembar dengan kode {{ $kode }} tidak tercatat di SIBIMTA. Periksa kembali penulisan kode, atau minta mahasiswa mencetak ulang lembar bimbingan.</p>
            </div>
        @endif
    @endif
</div>
@endsection

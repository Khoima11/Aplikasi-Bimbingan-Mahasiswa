<x-layout title="Dashboard admin" judul="Dashboard admin prodi">
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-angka label="Skripsi aktif" :nilai="$aktif" />
        <x-angka label="Dosen aktif" :nilai="$dosen" :href="route('admin.pengguna.index', ['tab' => 'dosen'])" />
        <x-angka label="Siap seminar proposal" :nilai="$siap->where('jenis_ujian', 'seminar_proposal')->count()" :tegas="true" />
        <x-angka label="Siap sidang" :nilai="$siap->where('jenis_ujian', 'sidang')->count()" :tegas="true" />
    </div>
    <div class="grid gap-4 lg:grid-cols-[2.2fr_1fr]">
        <section class="panel">
            <div class="panel-kepala"><h2>Mahasiswa siap ujian</h2><a href="{{ route('admin.ujian.index') }}" class="text-sm font-semibold">Semua</a></div>
            @include('admin.ujian._siap', ['siap' => $siap])
        </section>
        <div class="space-y-4">
            <section class="panel panel-isi text-sm">
                <h2>Periode aktif</h2>
                @if ($periode)<p class="mt-1 text-lg font-bold">{{ $periode->nama_periode }}</p><p class="text-redup">{{ $periode->tanggal_mulai->translatedFormat('d M Y') }} s.d. {{ $periode->tanggal_selesai->translatedFormat('d M Y') }}</p>
                @else <p class="mt-1 text-merah">Belum ada periode aktif. Pengajuan judul tidak akan tercatat pada periode mana pun.</p> @endif
                <a href="{{ route('admin.master') }}" class="mt-2 inline-block font-semibold">Kelola periode</a>
            </section>
            <section class="panel">
                <div class="panel-kepala"><h2>Akun</h2></div>
                <dl class="divide-y divide-krem-2 text-sm">@foreach ($akun as $k => $v)<div class="flex justify-between px-4 py-2"><dt>{{ $k }}</dt><dd class="font-semibold">{{ $v }}</dd></div>@endforeach</dl>
            </section>
        </div>
    </div>
</x-layout>

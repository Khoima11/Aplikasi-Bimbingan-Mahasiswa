<x-layout title="Akun" judul="Akun">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="panel">
            <div class="panel-kepala"><h2>Data akun</h2></div>
            <dl class="grid grid-cols-[8rem_1fr] gap-y-2 p-4 text-sm">
                <dt class="text-redup">Nama</dt><dd class="font-medium">{{ $u->nama }}</dd>
                <dt class="text-redup">Username</dt><dd>{{ $u->username }}</dd>
                <dt class="text-redup">Email</dt><dd class="break-all">{{ $u->email }}</dd>
                <dt class="text-redup">Peran</dt><dd>@foreach ($u->daftarPeran() as $p)<span class="pill pill-marun mr-1">{{ \App\Models\Peran::LABEL[$p] }}</span>@endforeach</dd>
                @if ($u->mahasiswa)<dt class="text-redup">Angkatan</dt><dd>{{ $u->mahasiswa->angkatan }} (semester {{ $u->mahasiswa->semester() }})</dd>@endif
                @if ($u->dosen)<dt class="text-redup">Jabatan</dt><dd>{{ $u->dosen->jabatan_fungsional ?? '-' }}</dd>@endif
                <dt class="text-redup">Login terakhir</dt><dd>{{ $u->terakhir_login?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
            </dl>
            <p class="px-4 pb-4 text-xs text-redup">Perubahan nama atau email dilakukan oleh admin program studi.</p>
        </div>
        <div class="panel panel-isi space-y-3">
            <h2>Keamanan</h2>
            <p class="text-sm text-redup">Ganti kata sandi secara berkala dan jangan bagikan kepada orang lain.</p>
            <a href="{{ route('password.form') }}" class="btn">Ganti kata sandi</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-bahaya" type="submit">Keluar dari SIBIMTA</button></form>
        </div>
    </div>
</x-layout>

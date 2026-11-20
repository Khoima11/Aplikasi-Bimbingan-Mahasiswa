@php $baru = ! $u->exists; $m = $u->mahasiswa; $d = $u->dosen; $peran = $baru ? [$jenis] : $u->daftarPeran(); @endphp
<x-layout :title="$baru ? 'Tambah pengguna' : 'Ubah pengguna'" :judul="($baru ? 'Tambah ' : 'Ubah ').$jenis">
    <form method="post" action="{{ $baru ? route('admin.pengguna.store') : route('admin.pengguna.update', $u) }}" class="panel panel-isi max-w-2xl space-y-4">
        @csrf @unless($baru) @method('put') @endunless
        <input type="hidden" name="jenis" value="{{ $jenis }}">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="label" for="nama">Nama lengkap dan gelar</label><input class="input" id="nama" name="nama" value="{{ old('nama', $u->nama) }}" required maxlength="100"></div>
            <div><label class="label" for="username">{{ $jenis === 'mahasiswa' ? 'NIM' : ($jenis === 'dosen' ? 'NIP/NIDN' : 'Username') }}</label><input class="input" id="username" name="username" value="{{ old('username', $u->username) }}" required maxlength="30"><p class="bantuan">Dipakai untuk login.</p></div>
            <div><label class="label" for="email">Email</label><input class="input" type="email" id="email" name="email" value="{{ old('email', $u->email) }}" required maxlength="100"></div>
            @if ($jenis === 'mahasiswa')
                <div><label class="label" for="angkatan">Angkatan</label><input class="input" type="number" id="angkatan" name="angkatan" value="{{ old('angkatan', $m?->angkatan ?? date('Y')) }}" required></div>
                <div><label class="label" for="no_hp">No. HP</label><input class="input" id="no_hp" name="no_hp" value="{{ old('no_hp', $m?->no_hp) }}" maxlength="15"></div>
                <div><label class="label" for="status_mahasiswa">Status mahasiswa</label><select class="input" id="status_mahasiswa" name="status_mahasiswa">@foreach (['aktif','cuti','lulus','keluar'] as $s)<option @selected(old('status_mahasiswa', $m?->status_mahasiswa ?? 'aktif') === $s)>{{ $s }}</option>@endforeach</select></div>
            @elseif ($jenis === 'dosen')
                <div><label class="label" for="jabatan_fungsional">Jabatan fungsional</label><input class="input" id="jabatan_fungsional" name="jabatan_fungsional" value="{{ old('jabatan_fungsional', $d?->jabatan_fungsional) }}" maxlength="50"></div>
                <div><label class="label" for="status_dosen">Status dosen</label><select class="input" id="status_dosen" name="status_dosen">@foreach (['aktif' => 'Aktif','tugas_belajar' => 'Tugas belajar','nonaktif' => 'Nonaktif'] as $k => $l)<option value="{{ $k }}" @selected(old('status_dosen', $d?->status_dosen ?? 'aktif') === $k)>{{ $l }}</option>@endforeach</select></div>
                <div><label class="label" for="kuota_pembimbing_1">Kuota Pembimbing I</label><input class="input" type="number" min="0" max="99" id="kuota_pembimbing_1" name="kuota_pembimbing_1" value="{{ old('kuota_pembimbing_1', $d?->kuota_pembimbing_1 ?? 10) }}" required></div>
                <div><label class="label" for="kuota_pembimbing_2">Kuota Pembimbing II</label><input class="input" type="number" min="0" max="99" id="kuota_pembimbing_2" name="kuota_pembimbing_2" value="{{ old('kuota_pembimbing_2', $d?->kuota_pembimbing_2 ?? 10) }}" required></div>
            @endif
        </div>
        <fieldset><legend class="label">Peran tambahan</legend>
            <div class="flex flex-wrap gap-4 text-sm">
                @foreach (\App\Models\Peran::LABEL as $k => $l)
                    @continue($k === $jenis || ($k === 'mahasiswa') || ($k === 'dosen' && $jenis !== 'dosen'))
                    <label class="flex items-center gap-2"><input type="checkbox" name="peran[]" value="{{ $k }}" @checked(in_array($k, old('peran', $peran))) class="accent-marun-700">{{ $l }}</label>
                @endforeach
            </div>
            <p class="bantuan">Contoh: dosen yang menjabat kaprodi diberi peran Kaprodi tambahan, lalu dapat berpindah peran tanpa akun baru.</p>
        </fieldset>
        @if ($baru)
            <div><label class="label" for="password_awal">Kata sandi awal</label><input class="input" id="password_awal" name="password_awal" placeholder="Kosongkan untuk memakai username"><p class="bantuan">Pengguna wajib menggantinya saat login pertama.</p></div>
        @endif
        <div class="flex gap-2"><a href="{{ route('admin.pengguna.index', ['tab' => $jenis]) }}" class="btn">Batal</a><button class="btn btn-utama" type="submit">Simpan</button></div>
    </form>
</x-layout>

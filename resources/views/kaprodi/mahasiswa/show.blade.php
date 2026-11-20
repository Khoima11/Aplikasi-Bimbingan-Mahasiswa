<x-layout title="{{ $s->mahasiswa->pengguna->nama }}">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('kaprodi.mahasiswa.index') }}" class="text-sm">Kembali ke progres mahasiswa</a>
        <a href="{{ route('lembar', $s) }}" class="btn btn-kecil"><x-ikon n="cetak" c="size-4" />Lembar bimbingan</a>
    </div>
    <div class="mt-2">@include('partials.detail-skripsi', ['s' => $s])</div>
    @if ($s->status_skripsi === 'aktif')
        <form method="post" action="{{ route('kaprodi.mahasiswa.pembimbing', $s) }}" class="panel panel-isi mt-4 grid gap-3 md:grid-cols-[auto_1fr_1fr_auto] md:items-end">
            @csrf
            <h2 class="md:col-span-4">Ganti pembimbing</h2>
            <div><label class="label" for="urutan">Urutan</label><select class="input" id="urutan" name="urutan"><option value="1">Pembimbing I</option><option value="2">Pembimbing II</option></select></div>
            <div><label class="label" for="id_dosen">Dosen pengganti</label><select class="input" id="id_dosen" name="id_dosen">@foreach ($dosen as $d)<option value="{{ $d->id_dosen }}">{{ $d->pengguna->nama }} (P1 {{ $d->t1 }}/{{ $d->kuota_pembimbing_1 }}, P2 {{ $d->t2 }}/{{ $d->kuota_pembimbing_2 }})</option>@endforeach</select></div>
            <div><label class="label" for="alasan">Alasan</label><input class="input" id="alasan" name="alasan" required maxlength="255"></div>
            <button class="btn btn-utama" onclick="return confirm('Ganti pembimbing? Riwayat bimbingan lama tetap tersimpan.')">Simpan</button>
        </form>
    @endif
</x-layout>

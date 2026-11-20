<x-layout title="Catat hasil ujian" judul="Catat hasil ujian">
    @php $jenis = $s->tahapan->urutan === 5 ? 'sidang' : 'seminar_proposal'; @endphp
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="panel">
            <div class="panel-kepala"><h2>Data mahasiswa</h2></div>
            <dl class="grid grid-cols-[9rem_1fr] gap-y-2 p-4 text-sm">
                <dt class="text-redup">Nama / NIM</dt><dd>{{ $s->mahasiswa->pengguna->nama }} / {{ $s->mahasiswa->nim }}</dd>
                <dt class="text-redup">Judul</dt><dd>{{ $s->judul_final }}</dd>
                <dt class="text-redup">Tahap</dt><dd>{{ $s->tahapan->nama_tahapan }}</dd>
                @foreach ($s->pembimbing as $p)
                    @php $ps = $s->persetujuan->where('id_dosen', $p->id_dosen)->where('jenis', $jenis)->where('status', 'berlaku')->first(); @endphp
                    <dt class="text-redup">{{ $p->label() }}</dt><dd>{{ $p->dosen->pengguna->nama }}<br><span class="text-xs {{ $ps ? 'text-hijau' : 'text-merah' }}">{{ $ps ? 'Persetujuan '.$ps->tanggal_persetujuan->format('d/m/Y') : 'Belum ada persetujuan' }}</span></dd>
                @endforeach
                <dt class="text-redup">Bimbingan tervalidasi</dt><dd>@foreach ($s->pembimbing as $p){{ $p->urutan == 1 ? 'P1' : 'P2' }}: {{ $s->jumlahTervalidasi($p->id_dosen) }}&nbsp; @endforeach</dd>
            </dl>
        </section>
        <form method="post" action="{{ route('admin.ujian.store', $s) }}" class="panel panel-isi space-y-3" x-data="{ h: '{{ old('hasil', 'lulus') }}' }">
            @csrf
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <h2>{{ \App\Models\Persetujuan::JENIS[$jenis] }}</h2>
            <div><label class="label" for="tanggal_pelaksanaan">Tanggal pelaksanaan</label><input class="input" type="date" id="tanggal_pelaksanaan" name="tanggal_pelaksanaan" max="{{ today()->toDateString() }}" value="{{ old('tanggal_pelaksanaan', today()->toDateString()) }}" required></div>
            <fieldset><legend class="label">Hasil</legend><div class="flex flex-wrap gap-4 text-sm">
                @foreach (\App\Models\Ujian::HASIL as $k => $l)<label class="flex items-center gap-2"><input type="radio" name="hasil" value="{{ $k }}" x-model="h" class="accent-marun-700">{{ $l }}</label>@endforeach
            </div></fieldset>
            <div><label class="label" for="catatan">Catatan</label><input class="input" id="catatan" name="catatan" maxlength="255" value="{{ old('catatan') }}"></div>
            <div x-show="h === 'lulus_dengan_revisi'" x-cloak><label class="label" for="tanggal_revisi_selesai">Tanggal revisi selesai</label><input class="input" type="date" id="tanggal_revisi_selesai" name="tanggal_revisi_selesai" value="{{ old('tanggal_revisi_selesai') }}">
                <p class="bantuan">{{ $jenis === 'sidang' ? 'Kosongkan bila revisi belum selesai. Skripsi dinyatakan selesai setelah tanggal ini diisi.' : 'Opsional.' }}</p></div>
            <div x-show="h === 'tidak_lulus'" x-cloak class="rounded-md border border-merah/30 bg-merah-50 p-3 text-sm text-merah">Hasil tidak lulus mengembalikan mahasiswa ke tahap sebelumnya dan membatalkan persetujuan pembimbing.</div>
            <div class="flex gap-2"><a href="{{ route('admin.ujian.index') }}" class="btn">Batal</a><button class="btn btn-utama" type="submit">Simpan hasil</button></div>
        </form>
    </div>
</x-layout>

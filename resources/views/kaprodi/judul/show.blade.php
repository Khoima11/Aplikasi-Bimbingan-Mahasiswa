<x-layout title="Periksa pengajuan judul">
    <a href="{{ route('kaprodi.judul.index') }}" class="text-sm">Kembali ke daftar</a>
    <div class="mt-2 grid gap-4 lg:grid-cols-[1fr_2.2fr]">
        <aside class="panel hidden h-fit lg:block">
            <div class="panel-kepala"><h2>Antrean menunggu</h2></div>
            <ul class="divide-y divide-garis">
                @forelse ($antrian as $a)
                    <li><a href="{{ route('kaprodi.judul.show', $a) }}" class="block px-4 py-2.5 text-sm no-underline {{ $a->id_pengajuan === $p->id_pengajuan ? 'bg-marun-50 text-marun-700' : 'text-tinta hover:bg-dasar' }}"><span class="font-semibold">{{ $a->mahasiswa->pengguna->nama }}</span><br><span class="line-clamp-2 text-redup">{{ $a->judul }}</span></a></li>
                @empty <li><x-kosong>Antrean kosong.</x-kosong></li> @endforelse
            </ul>
        </aside>
        <section class="panel panel-isi">
            <div class="flex flex-wrap items-center gap-2"><h1 class="!text-lg">{{ $p->mahasiswa->pengguna->nama }}</h1><span class="text-sm text-redup">{{ $p->mahasiswa->nim }} · angkatan {{ $p->mahasiswa->angkatan }}</span><x-status :s="$p->status" /></div>
            <p class="text-sm text-redup">Diajukan {{ $p->tanggal_ajuan->translatedFormat('d F Y, H:i') }} · pengajuan ke-{{ $p->pengajuanKe() }}</p>
            <h2 class="mt-4">Judul</h2><p class="text-[15px]">{{ $p->judul }}</p>
            <p class="mt-1 text-sm text-redup">Kata kunci: {{ $p->kata_kunci }}</p>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div><h2>Latar masalah</h2><p class="mt-1 whitespace-pre-line text-sm">{{ $p->latar_masalah }}</p></div>
                <div class="rounded-md border border-dashed border-emas bg-emas-50 p-3 text-sm">
                    <h2>Judul serupa</h2>
                    @forelse ($serupa as $x)<p class="mt-2">{{ $x['judul'] }}<br><span class="text-redup">{{ $x['nama'] }} ({{ $x['nim'] }}), {{ $x['tahun'] }} · {{ $x['skor'] }} kata kunci sama</span></p>@empty<p class="mt-1 text-redup">Tidak ditemukan judul dengan kata kunci serupa.</p>@endforelse
                    <p class="bantuan">Berdasarkan kesamaan kata kunci, bukan pemeriksaan plagiarisme.</p>
                </div>
            </div>
            @if ($p->asal)
                <div class="mt-4 rounded-md bg-dasar p-3 text-sm"><p class="font-semibold">Pengajuan sebelumnya</p><p>{{ $p->asal->judul }}</p><p class="text-redup">Catatan: {{ $p->asal->catatan_kaprodi }}</p></div>
            @endif

            @if ($p->status === 'menunggu')
                <form method="post" action="{{ route('kaprodi.judul.putuskan', $p) }}" class="mt-6 space-y-4 border-t border-garis pt-4" x-data="{ k: '{{ old('keputusan', 'disetujui') }}' }">
                    @csrf
                    <fieldset><legend class="label">Keputusan</legend>
                        <div class="flex flex-wrap gap-4 text-sm">
                            @foreach (['disetujui' => 'Setujui', 'perlu_revisi' => 'Perlu revisi', 'ditolak' => 'Tolak'] as $v => $l)
                                <label class="flex items-center gap-2"><input type="radio" name="keputusan" value="{{ $v }}" x-model="k" class="accent-marun-700">{{ $l }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div><label class="label" for="catatan_kaprodi">Catatan untuk mahasiswa <span x-show="k !== 'disetujui'" class="text-merah">(wajib)</span></label><textarea class="input" id="catatan_kaprodi" name="catatan_kaprodi" rows="3">{{ old('catatan_kaprodi') }}</textarea></div>
                    <div x-show="k === 'disetujui'" class="space-y-3">
                        <div><label class="label" for="judul_final">Judul yang ditetapkan</label><input class="input" id="judul_final" name="judul_final" value="{{ old('judul_final', $p->judul) }}" maxlength="255"><p class="bantuan">Ubah bila ada penyempurnaan redaksi judul.</p></div>
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ([1 => 'I', 2 => 'II'] as $u => $r)
                                <div><label class="label" for="pembimbing_{{ $u }}">Pembimbing {{ $r }}</label>
                                    <select class="input" id="pembimbing_{{ $u }}" name="pembimbing_{{ $u }}">
                                        <option value="">Pilih dosen</option>
                                        @foreach ($dosen as $d)
                                            @php $t = $u == 1 ? $d->t1 : $d->t2; $k = $d->kuota($u); @endphp
                                            <option value="{{ $d->id_dosen }}" @selected(old('pembimbing_'.$u) == $d->id_dosen)>{{ $d->pengguna->nama }} ({{ $t }}/{{ $k }}{{ $t >= $k ? ', penuh' : '' }})</option>
                                        @endforeach
                                    </select></div>
                            @endforeach
                        </div>
                        <div><label class="label" for="alasan">Alasan bila melebihi kuota</label><input class="input" id="alasan" name="alasan" value="{{ old('alasan') }}" maxlength="255"><p class="bantuan">Angka dalam kurung menunjukkan jumlah bimbingan aktif dibandingkan kuota.</p></div>
                    </div>
                    <button class="btn btn-utama" type="submit">Simpan keputusan</button>
                </form>
            @else
                <div class="mt-6 rounded-md bg-dasar p-3 text-sm"><p>Diputuskan {{ $p->tanggal_putusan?->translatedFormat('d M Y') }} oleh {{ $p->kaprodi?->nama }}.</p>@if($p->catatan_kaprodi)<p class="mt-1">Catatan: {{ $p->catatan_kaprodi }}</p>@endif</div>
            @endif
            @if ($riwayat->count())
                <div class="mt-4"><h2>Riwayat pengajuan mahasiswa ini</h2>
                    <ul class="mt-1 space-y-1 text-sm">@foreach ($riwayat as $r)<li><x-status :s="$r->status" /> {{ $r->judul }} <span class="text-redup">({{ $r->tanggal_ajuan->format('d/m/Y') }})</span></li>@endforeach</ul></div>
            @endif
        </section>
    </div>
</x-layout>

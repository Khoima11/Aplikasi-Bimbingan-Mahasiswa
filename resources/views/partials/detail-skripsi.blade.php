{{-- Ringkasan + tab riwayat satu skripsi. Dipakai kaprodi dan dosen. Variabel: $s --}}
@php
    $s->loadMissing(['mahasiswa.pengguna', 'tahapan', 'semuaPembimbing.dosen.pengguna']);
    $ind = \App\Services\Peringatan::hitung($s);
    $tahapan = \App\Models\Tahapan::semua();
    $catatan = $s->catatan()->with('dosen.pengguna')->orderByDesc('tanggal_bimbingan')->get();
    $revisi = $s->revisi()->with('dosen.pengguna', 'dokumenPerbaikan')->orderByDesc('id_revisi')->get();
    $dok = $s->dokumen()->with('review.dosen.pengguna')->orderByDesc('tanggal_unggah')->get();
    $pers = $s->persetujuan()->with('dosen.pengguna')->orderByDesc('tanggal_persetujuan')->get();
    $ujian = $s->ujian()->orderByDesc('tanggal_pelaksanaan')->get();
    $terakhir = $catatan->where('status_validasi', 'divalidasi')->first();
@endphp
<section class="kartu-navy mb-5 p-5 sm:p-7">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xl font-semibold">{{ $s->mahasiswa->pengguna->nama }} <span class="text-base font-normal text-white/55">{{ $s->mahasiswa->nim }}</span></p>
            <p class="mt-1 max-w-3xl text-white/85">{{ $s->judul_final }}</p>
            <p class="mt-2 text-sm text-white/55">Angkatan {{ $s->mahasiswa->angkatan }} · semester {{ $s->mahasiswa->semester() }}@if($s->mahasiswa->no_hp) · <a class="text-white underline decoration-white/40" href="https://wa.me/62{{ ltrim($s->mahasiswa->no_hp, '0') }}" target="_blank" rel="noopener">WhatsApp {{ $s->mahasiswa->no_hp }}</a>@endif</p>
        </div>
        <div class="text-right">
            @forelse ($ind as $i)<span class="mb-1 mr-1 inline-flex rounded-full bg-merah-50 px-2.5 py-0.5 text-xs font-medium text-merah">{{ $i['label'] }}</span>@empty<span class="inline-flex rounded-full bg-white/10 px-2.5 py-0.5 text-xs text-white/80">Progres normal</span>@endforelse
        </div>
    </div>
    <x-stepper :tahapan="$tahapan" :urutan="$s->tahapan->urutan" :selesai="$s->status_skripsi === 'selesai'" />
</section>
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ($s->semuaPembimbing->where('status', 'aktif') as $p)
        <x-angka :label="'Bimbingan '.$p->label()" :nilai="$catatan->where('id_dosen', $p->id_dosen)->where('status_validasi', 'divalidasi')->count()" />
    @endforeach
    <x-angka label="Revisi terbuka" :nilai="$revisi->where('status', '!=', 'diterima')->count()" />
    <x-angka label="Bimbingan terakhir" :nilai="$terakhir?->tanggal_bimbingan->format('d/m/y') ?? '-'" />
</div>
<section class="panel" x-data="{ t: 'catatan' }">
    <div class="baris-tab mb-0 px-3 pt-2">
        @foreach (['catatan' => 'Riwayat bimbingan', 'revisi' => 'Item revisi', 'dokumen' => 'Dokumen', 'ujian' => 'Pembimbing, persetujuan & ujian'] as $k => $l)
            <button type="button" class="tab whitespace-nowrap" :class="t === '{{ $k }}' && 'tab-aktif'" @click="t = '{{ $k }}'">{{ $l }}</button>
        @endforeach
    </div>
    <div x-show="t === 'catatan'" class="overflow-x-auto">
        <table class="tbl"><thead><tr><th>Tanggal</th><th>Pembimbing</th><th>Mode</th><th>Bab</th><th>Ringkasan</th><th>Status</th></tr></thead><tbody>
        @forelse ($catatan as $c)
            <tr><td class="whitespace-nowrap">{{ $c->tanggal_bimbingan->format('d/m/Y') }}</td><td>{{ $c->dosen->pengguna->nama }}</td><td>{{ ucfirst($c->mode) }}</td><td>{{ $c->bab_dibahas }}</td><td class="min-w-72">{{ $c->ringkasan }}@if($c->catatan_dosen)<br><span class="text-redup">Dosen: {{ $c->catatan_dosen }}</span>@endif</td><td><x-status :s="$c->status_validasi" /></td></tr>
        @empty <tr><td colspan="6"><x-kosong>Belum ada catatan.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
    <div x-show="t === 'revisi'" x-cloak class="overflow-x-auto">
        <table class="tbl"><thead><tr><th>Bab</th><th>Uraian</th><th>Dari</th><th>Status</th><th>Perbaikan</th></tr></thead><tbody>
        @forelse ($revisi as $i)
            <tr><td>{{ $i->bab }}</td><td class="min-w-64">{{ $i->uraian }}</td><td>{{ $i->dosen->pengguna->nama }}</td><td><x-status :s="$i->status" /></td><td>@if($i->dokumenPerbaikan)<a href="{{ route('berkas.dokumen', $i->dokumenPerbaikan) }}">{{ $i->dokumenPerbaikan->label() }}</a>@endif {{ $i->keterangan_mahasiswa }}</td></tr>
        @empty <tr><td colspan="5"><x-kosong>Belum ada item revisi.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
    <div x-show="t === 'dokumen'" x-cloak class="overflow-x-auto">
        <table class="tbl"><thead><tr><th>Dokumen</th><th>Diunggah</th><th>Review</th><th></th></tr></thead><tbody>
        @forelse ($dok as $d)
            <tr><td class="font-medium">{{ $d->label() }}<br><span class="text-xs text-redup">{{ $d->catatan_mahasiswa }}</span></td><td class="whitespace-nowrap">{{ $d->tanggal_unggah->format('d/m/Y') }}</td>
                <td>@forelse ($d->review as $r)<span class="mr-1 text-xs">{{ \Illuminate\Support\Str::words($r->dosen->pengguna->nama, 2, '') }}</span><x-status :s="$r->hasil" class="mr-2" />@empty<span class="pill pill-abu">Belum direview</span>@endforelse</td>
                <td><a class="btn btn-kecil" href="{{ route('berkas.dokumen', $d) }}">Unduh</a></td></tr>
        @empty <tr><td colspan="4"><x-kosong>Belum ada dokumen.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
    <div x-show="t === 'ujian'" x-cloak class="grid gap-4 p-4 md:grid-cols-3 text-sm">
        <div><h2 class="mb-2">Riwayat pembimbing</h2>
            <ul class="space-y-2">@foreach ($s->semuaPembimbing as $p)<li>{{ $p->label() }}: {{ $p->dosen->pengguna->nama }} <x-status :s="$p->status" /><br><span class="text-xs text-redup">sejak {{ $p->tanggal_penetapan->format('d/m/Y') }}{{ $p->tanggal_berakhir ? ' s.d. '.$p->tanggal_berakhir->format('d/m/Y') : '' }}{{ $p->alasan ? '. '.$p->alasan : '' }}</span></li>@endforeach</ul></div>
        <div><h2 class="mb-2">Persetujuan</h2>
            <ul class="space-y-2">@forelse ($pers as $p)<li>{{ \App\Models\Persetujuan::JENIS[$p->jenis] }}: {{ $p->dosen->pengguna->nama }} <x-status :s="$p->status" /><br><span class="text-xs text-redup">{{ $p->tanggal_persetujuan->format('d/m/Y') }}</span></li>@empty<li class="text-redup">Belum ada.</li>@endforelse</ul></div>
        <div><h2 class="mb-2">Ujian</h2>
            <ul class="space-y-2">@forelse ($ujian as $u)<li>{{ \App\Models\Persetujuan::JENIS[$u->jenis] }}: <x-status :s="$u->hasil" /><br><span class="text-xs text-redup">{{ $u->tanggal_pelaksanaan->format('d/m/Y') }}{{ $u->catatan ? '. '.$u->catatan : '' }}</span></li>@empty<li class="text-redup">Belum ada.</li>@endforelse</ul></div>
    </div>
</section>

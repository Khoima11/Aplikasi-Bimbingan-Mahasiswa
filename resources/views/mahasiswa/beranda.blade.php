<x-layout title="Beranda">
    <p class="text-sm text-redup">Assalamu'alaikum,</p>
    <h1 class="mb-4">{{ $m->pengguna->nama }}</h1>

    <section class="panel mb-4 p-4 sm:p-5">
        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
            <h2>Jalur skripsi</h2>
            @if ($s)<span class="text-sm text-redup">Di tahap ini sejak {{ $s->tanggal_masuk_tahap->translatedFormat('d M Y') }}</span>@endif
        </div>
        <x-stepper :tahapan="$tahapan" :urutan="$s?->tahapan->urutan ?? 1" :selesai="$s?->status_skripsi === 'selesai'" />
        @if ($s)
            <p class="mt-4 text-sm font-medium">{{ $s->judul_final }}</p>
        @endif
    </section>

    @if (! $s)
        <section class="panel panel-isi mb-4">
            @if (! $pengajuan)
                <h2>Mulai dengan mengajukan judul</h2>
                <p class="mt-1 text-sm text-redup">Kaprodi akan memeriksa usulan Anda dan menetapkan dua dosen pembimbing.</p>
                <a href="{{ route('mahasiswa.judul') }}" class="btn btn-utama mt-3">Ajukan judul</a>
            @else
                <div class="flex flex-wrap items-center gap-2"><h2>Pengajuan judul terakhir</h2><x-status :s="$pengajuan->status" /></div>
                <p class="mt-2 text-sm">{{ $pengajuan->judul }}</p>
                @if ($pengajuan->catatan_kaprodi)<p class="mt-2 rounded-md bg-dasar p-3 text-sm"><span class="font-semibold">Catatan kaprodi:</span> {{ $pengajuan->catatan_kaprodi }}</p>@endif
                @if (in_array($pengajuan->status, ['perlu_revisi', 'ditolak']))
                    <a href="{{ route('mahasiswa.judul') }}" class="btn btn-utama mt-3">{{ $pengajuan->status === 'perlu_revisi' ? 'Perbaiki dan ajukan ulang' : 'Ajukan judul baru' }}</a>
                @else
                    <p class="mt-2 text-sm text-redup">Anda akan menerima notifikasi setelah kaprodi memutuskan.</p>
                @endif
            @endif
        </section>
    @else
        @php $tindakan = array_filter(['revisi' => $revisiBelum, 'koreksi' => $dikoreksi, 'usulan' => $usulan]); @endphp
        @if ($tindakan)
            <section class="mb-4 rounded-lg border border-emas/50 bg-emas-50 p-4">
                <h2 class="mb-2">Perlu tindakan Anda</h2>
                <ul class="space-y-1.5 text-sm">
                    @if ($revisiBelum)<li><a href="{{ route('mahasiswa.revisi') }}">{{ $revisiBelum }} item revisi belum dikerjakan</a></li>@endif
                    @if ($dikoreksi)<li><a href="{{ route('mahasiswa.bimbingan') }}#catatan">{{ $dikoreksi }} catatan bimbingan dikoreksi dosen</a></li>@endif
                    @if ($usulan)<li><a href="{{ route('mahasiswa.bimbingan') }}">{{ $usulan }} usulan waktu bimbingan dari dosen</a></li>@endif
                </ul>
            </section>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <section class="panel">
                <div class="panel-kepala"><h2>Bimbingan pada tahap ini</h2></div>
                <ul class="divide-y divide-garis">
                    @foreach ($progres as $p)
                        @php $min = (int) ($s->tahapan->min_bimbingan ?? 0); @endphp
                        <li class="px-4 py-3">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span><span class="text-redup">{{ $p['label'] }}</span><br><span class="font-medium">{{ $p['nama'] }}</span></span>
                                <span class="text-right"><span class="text-lg font-bold">{{ $p['n'] }}</span>@if($min)<span class="text-redup">/{{ $min }}</span>@endif</span>
                            </div>
                            @if ($min)
                                <div class="mt-2 h-1.5 rounded bg-dasar"><div class="h-1.5 rounded bg-marun-700" style="width: {{ min(100, $p['n'] / $min * 100) }}%"></div></div>
                            @endif
                            @if (! is_null($p['setuju']))
                                <p class="mt-1.5 text-xs {{ $p['setuju'] ? 'text-hijau' : 'text-redup' }}">{{ $p['setuju'] ? 'Sudah memberi persetujuan maju ujian' : 'Belum memberi persetujuan maju ujian' }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
            <section class="panel">
                <div class="panel-kepala"><h2>Jadwal terdekat</h2><a href="{{ route('mahasiswa.bimbingan') }}" class="text-sm font-semibold">Atur jadwal</a></div>
                <div class="p-4">
                    @if ($jadwal)
                        <p class="text-lg font-bold">{{ $jadwal->tanggal->translatedFormat('l, d M') }} · {{ substr($jadwal->jam, 0, 5) }}</p>
                        <p class="text-sm text-redup">{{ $jadwal->dosen->pengguna->nama }} · {{ ucfirst($jadwal->mode) }}</p>
                        <p class="mt-1 text-sm">{{ $jadwal->rencana_bahasan }}</p>
                    @else
                        <p class="text-sm text-redup">Belum ada jadwal yang disetujui. Pilih slot yang dibuka pembimbing.</p>
                    @endif
                </div>
            </section>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('lembar', $s) }}" class="btn"><x-ikon n="cetak" c="size-4" />Lembar bimbingan</a>
            <a href="{{ route('mahasiswa.dokumen') }}" class="btn"><x-ikon n="unggah" c="size-4" />Unggah draf</a>
        </div>
    @endif

    <section class="panel mt-4">
        <div class="panel-kepala"><h2>Notifikasi terbaru</h2><a href="{{ route('notifikasi') }}" class="text-sm font-semibold">Semua</a></div>
        <ul class="divide-y divide-garis">
            @forelse ($notif as $n)
                <li><a href="{{ route('notifikasi.baca', $n) }}" class="block px-4 py-3 text-sm text-tinta no-underline hover:bg-dasar"><span class="{{ $n->sudah_dibaca ? '' : 'font-bold' }}">{{ $n->judul }}</span><br><span class="text-redup">{{ $n->pesan }}</span></a></li>
            @empty
                <li><x-kosong>Belum ada notifikasi.</x-kosong></li>
            @endforelse
        </ul>
    </section>
</x-layout>

<x-layout title="Beranda">
    <p class="text-redup">Assalamu'alaikum,</p>
    <h1 class="mb-5">{{ \Illuminate\Support\Str::words($m->pengguna->nama, 2, '') }}</h1>

    <section class="kartu-navy relative mb-5 overflow-hidden p-5 sm:p-7">
        <svg class="pointer-events-none absolute -right-10 -top-10 size-56 text-white/[.04]" viewBox="0 0 200 200" aria-hidden="true"><circle cx="100" cy="100" r="98" fill="currentColor"/></svg>
        <div class="relative">
            <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-white/60">Jalur skripsi</p>
                @if ($s)<span class="rounded-full bg-white/10 px-3 py-1 text-xs text-white/80">Di tahap ini sejak {{ $s->tanggal_masuk_tahap->translatedFormat('d M Y') }}</span>@endif
            </div>
            <p class="mb-6 max-w-2xl text-xl font-semibold leading-snug sm:text-2xl">{{ $s?->judul_final ?? ($pengajuan?->judul ?? 'Belum ada judul') }}</p>
            <x-stepper :tahapan="$tahapan" :urutan="$s?->tahapan->urutan ?? 1" :selesai="$s?->status_skripsi === 'selesai'" />
            @if ($s)
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('mahasiswa.bimbingan') }}" class="btn border-krem bg-krem text-navy hover:bg-white">Atur bimbingan</a>
                    <a href="{{ route('lembar', $s) }}" class="btn btn-terang"><x-ikon n="cetak" c="size-4" />Lembar bimbingan</a>
                </div>
            @endif
        </div>
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
                @if ($pengajuan->catatan_kaprodi)<p class="mt-2 rounded-2xl bg-krem p-4 text-sm"><span class="font-semibold">Catatan kaprodi:</span> {{ $pengajuan->catatan_kaprodi }}</p>@endif
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
            <section class="mb-5 rounded-[var(--radius-panel)] bg-slate p-5 text-white">
                <h2 class="mb-2">Perlu tindakan Anda</h2>
                <ul class="space-y-2 text-sm [&_a]:text-white [&_a]:underline [&_a]:decoration-white/40">
                    @if ($revisiBelum)<li><a href="{{ route('mahasiswa.revisi') }}">{{ $revisiBelum }} item revisi belum dikerjakan</a></li>@endif
                    @if ($dikoreksi)<li><a href="{{ route('mahasiswa.bimbingan') }}#catatan">{{ $dikoreksi }} catatan bimbingan dikoreksi dosen</a></li>@endif
                    @if ($usulan)<li><a href="{{ route('mahasiswa.bimbingan') }}">{{ $usulan }} usulan waktu bimbingan dari dosen</a></li>@endif
                </ul>
            </section>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <section class="panel">
                <div class="panel-kepala"><h2>Bimbingan pada tahap ini</h2></div>
                <ul class="divide-y divide-krem-2">
                    @foreach ($progres as $p)
                        @php $min = (int) ($s->tahapan->min_bimbingan ?? 0); @endphp
                        <li class="px-4 py-3">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span><span class="text-redup">{{ $p['label'] }}</span><br><span class="font-medium">{{ $p['nama'] }}</span></span>
                                <span class="text-right"><span class="text-lg font-bold">{{ $p['n'] }}</span>@if($min)<span class="text-redup">/{{ $min }}</span>@endif</span>
                            </div>
                            @if ($min)
                                <div class="mt-2 h-1.5 rounded bg-krem"><div class="h-1.5 rounded bg-navy" style="width: {{ min(100, $p['n'] / $min * 100) }}%"></div></div>
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

    @endif

    <x-pasang varian="kartu" class="mt-4 lg:hidden" />

    <section class="panel mt-4">
        <div class="panel-kepala"><h2>Notifikasi terbaru</h2><a href="{{ route('notifikasi') }}" class="text-sm font-semibold">Semua</a></div>
        <ul class="divide-y divide-krem-2">
            @forelse ($notif as $n)
                <li><a href="{{ route('notifikasi.baca', $n) }}" class="block px-4 py-3 text-sm text-tinta no-underline hover:bg-krem"><span class="{{ $n->sudah_dibaca ? '' : 'font-bold' }}">{{ $n->judul }}</span><br><span class="text-redup">{{ $n->pesan }}</span></a></li>
            @empty
                <li><x-kosong>Belum ada notifikasi.</x-kosong></li>
            @endforelse
        </ul>
    </section>
</x-layout>

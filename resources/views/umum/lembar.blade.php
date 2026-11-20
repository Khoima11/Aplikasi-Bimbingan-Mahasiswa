<x-layout title="Lembar bimbingan">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div><h1>Lembar bimbingan</h1><p class="text-sm text-redup">{{ $s->mahasiswa->pengguna->nama }} ({{ $s->mahasiswa->nim }})</p></div>
        <a href="{{ route('lembar.pdf', $s) }}" class="btn btn-utama"><x-ikon n="unduh" c="size-4" />Unduh PDF</a>
    </div>
    <div class="panel overflow-hidden">
        <div class="panel-isi space-y-1 text-sm">
            <p><span class="text-redup">Judul:</span> {{ $s->judul_final }}</p>
            @foreach ($s->pembimbing as $p)<p><span class="text-redup">{{ $p->label() }}:</span> {{ $p->dosen->pengguna->nama }}</p>@endforeach
            <p><span class="text-redup">Tahap:</span> {{ $s->tahapan->nama_tahapan }}</p>
        </div>
        <div class="overflow-x-auto border-t border-garis">
            <table class="tbl">
                <thead><tr><th>No</th><th>Tanggal</th><th>Pembimbing</th><th>Bab</th><th>Ringkasan</th><th>Divalidasi</th></tr></thead>
                <tbody>
                @forelse ($catatan as $i => $c)
                    <tr><td>{{ $i + 1 }}</td><td class="whitespace-nowrap">{{ $c->tanggal_bimbingan->format('d/m/Y') }}</td><td>{{ $c->dosen->pengguna->nama }}</td><td>{{ $c->bab_dibahas }}</td><td class="min-w-64">{{ $c->ringkasan }}</td><td class="whitespace-nowrap">{{ $c->tanggal_validasi?->format('d/m/Y') }}</td></tr>
                @empty
                    <tr><td colspan="6"><x-kosong>Belum ada catatan bimbingan yang divalidasi.</x-kosong></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="mt-3 text-sm text-redup">Hanya catatan yang sudah divalidasi dosen yang dicetak. Setiap unduhan PDF memiliki kode QR unik yang dapat diperiksa panitia ujian.</p>
    @if ($riwayat->count())
        <div class="panel mt-4"><div class="panel-kepala"><h2>Riwayat cetak</h2></div>
            <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Kode</th><th>Tanggal cetak</th><th>Jumlah bimbingan</th><th>Tahap</th><th>Dicetak oleh</th></tr></thead><tbody>
            @foreach ($riwayat as $l)<tr><td class="font-mono text-xs">{{ $l->kode_verifikasi }}</td><td>{{ $l->tanggal_cetak->format('d/m/Y H:i') }}</td><td>{{ $l->jumlah_bimbingan }}</td><td>{{ $l->tahap_saat_cetak }}</td><td>{{ $l->pencetak->nama }}</td></tr>@endforeach
            </tbody></table></div></div>
    @endif
</x-layout>

<x-layout title="{{ $s->mahasiswa->pengguna->nama }}">
    <a href="{{ route('dosen.mahasiswa.index') }}" class="text-sm">Kembali ke daftar mahasiswa</a>
    <div class="mt-2">@include('partials.detail-skripsi', ['s' => $s])</div>
    @if ($aktif)
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="panel">
            <div class="panel-kepala"><h2>Catatan menunggu validasi</h2><span class="pill pill-jingga">{{ $menunggu->count() }}</span></div>
            <div class="divide-y divide-krem-2">
                @forelse ($menunggu as $c)
                    <div class="p-4 text-sm" x-data="{ koreksi: false }">
                        <p class="font-semibold">{{ $c->tanggal_bimbingan->translatedFormat('d M Y') }} · {{ $c->bab_dibahas }} · {{ ucfirst($c->mode) }}</p>
                        <p class="mt-1 whitespace-pre-line">{{ $c->ringkasan }}</p>
                        <form method="post" action="{{ route('dosen.catatan.validasi', $c) }}" class="mt-3 space-y-2" x-show="!koreksi">
                            @csrf
                            <input class="input" name="catatan_dosen" placeholder="Tanggapan (opsional)" aria-label="Tanggapan">
                            <div class="flex gap-2"><button class="btn btn-hijau btn-kecil" type="submit">Validasi</button><button type="button" class="btn btn-kecil" @click="koreksi = true">Koreksi</button></div>
                        </form>
                        <form method="post" action="{{ route('dosen.catatan.koreksi', $c) }}" class="mt-3 space-y-2" x-show="koreksi" x-cloak>
                            @csrf
                            <textarea class="input" name="catatan_dosen" rows="3" required placeholder="Apa yang perlu diperbaiki" aria-label="Tanggapan koreksi"></textarea>
                            <div class="flex gap-2"><button class="btn btn-utama btn-kecil" type="submit">Kirim koreksi</button><button type="button" class="btn btn-kecil" @click="koreksi = false">Batal</button></div>
                        </form>
                    </div>
                @empty <x-kosong>Tidak ada catatan yang menunggu.</x-kosong> @endforelse
            </div>
        </section>
        <section class="panel">
            <div class="panel-kepala"><h2>Item revisi dari Anda</h2></div>
            <div class="divide-y divide-krem-2">
                @forelse ($revisi as $i)
                    <div class="p-4 text-sm" x-data="{ kembali: false }">
                        <div class="flex flex-wrap items-center justify-between gap-2"><span class="font-semibold">{{ $i->bab }}</span><x-status :s="$i->status" /></div>
                        <p class="mt-1">{{ $i->uraian }}</p>
                        @if ($i->status === 'dikerjakan')
                            <p class="mt-1 text-redup">{{ $i->keterangan_mahasiswa }}@if($i->dokumenPerbaikan) · <a href="{{ route('berkas.dokumen', $i->dokumenPerbaikan) }}">{{ $i->dokumenPerbaikan->label() }}</a>@endif</p>
                            <div class="mt-2 flex gap-2" x-show="!kembali">
                                <form method="post" action="{{ route('dosen.revisi.terima', $i) }}">@csrf<button class="btn btn-hijau btn-kecil">Terima</button></form>
                                <button type="button" class="btn btn-kecil" @click="kembali = true">Kembalikan</button>
                            </div>
                            <form x-show="kembali" x-cloak method="post" action="{{ route('dosen.revisi.kembalikan', $i) }}" class="mt-2 flex gap-2">@csrf<input class="input" name="alasan" required placeholder="Alasan dikembalikan" aria-label="Alasan"><button class="btn btn-utama btn-kecil">Kirim</button></form>
                        @endif
                    </div>
                @empty <x-kosong>Belum ada item revisi.</x-kosong> @endforelse
            </div>
            <form method="post" action="{{ route('dosen.revisi.store', $s) }}" class="space-y-2 border-t border-garis p-4">
                @csrf
                <p class="text-sm font-semibold">Tambah item revisi</p>
                <div class="flex gap-2">
                    <select class="input w-36" name="bab" aria-label="Bab">@foreach (['Judul', 'Bab 1', 'Bab 2', 'Bab 3', 'Bab 4', 'Bab 5', 'Proposal', 'Lampiran', 'Umum'] as $b)<option>{{ $b }}</option>@endforeach</select>
                    <input class="input" name="uraian" required placeholder="Uraian revisi" aria-label="Uraian revisi">
                </div>
                <button class="btn btn-utama btn-kecil" type="submit">Tambah revisi</button>
            </form>
        </section>
    </div>
    @if ($syarat['jenis'])
        <section class="panel mt-4 p-4">
            <h2>Persetujuan maju {{ \App\Models\Persetujuan::JENIS[$syarat['jenis']] }}</h2>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($syarat['items'] as $it)
                    <li class="flex items-start gap-2"><span class="mt-0.5 grid size-4 shrink-0 place-items-center rounded-full {{ $it['ok'] ? 'bg-hijau text-white' : 'bg-garis' }}">@if($it['ok'])<svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5 9-10"/></svg>@endif</span>{{ $it['label'] }}</li>
                @endforeach
            </ul>
            @if ($syarat['sudah'])
                <p class="mt-3 text-sm text-hijau">Anda sudah memberi persetujuan pada {{ $syarat['sudah']->tanggal_persetujuan->translatedFormat('d M Y') }}.</p>
            @else
                <form method="post" action="{{ route('dosen.persetujuan', $s) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf
                    <input class="input max-w-sm" name="catatan" placeholder="Catatan (opsional)" aria-label="Catatan persetujuan" @disabled(! $syarat['terpenuhi'])>
                    <button class="btn btn-utama" @disabled(! $syarat['terpenuhi']) onclick="return confirm('Beri persetujuan maju ujian?')">Beri persetujuan</button>
                </form>
                @unless ($syarat['terpenuhi'])<p class="bantuan">Tombol aktif setelah semua syarat terpenuhi.</p>@endunless
            @endif
        </section>
    @endif
    @else
        <p class="mt-4 rounded-2xl bg-krem p-4 text-sm">Anda tidak lagi menjadi pembimbing aktif mahasiswa ini. Data ditampilkan sebagai riwayat.</p>
    @endif
</x-layout>

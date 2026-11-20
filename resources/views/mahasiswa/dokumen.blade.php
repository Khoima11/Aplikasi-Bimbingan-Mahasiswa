<x-layout title="Dokumen" judul="Dokumen skripsi">
    <div class="grid gap-4 lg:grid-cols-[2fr_3fr]">
        <form method="post" action="{{ route('mahasiswa.dokumen.store') }}" enctype="multipart/form-data" class="panel panel-isi h-fit space-y-3">
            @csrf
            <h2>Unggah versi baru</h2>
            <div><label class="label" for="bagian">Bagian</label><select class="input" id="bagian" name="bagian">@foreach (\App\Models\Dokumen::BAGIAN as $k => $l)<option value="{{ $k }}" @selected(old('bagian') === $k)>{{ $l }}</option>@endforeach</select></div>
            <div><label class="label" for="berkas">Berkas</label><input class="input file:mr-3 file:rounded file:border-0 file:bg-marun-50 file:px-3 file:py-1 file:text-sm file:font-semibold file:text-marun-700" type="file" id="berkas" name="berkas" accept=".pdf,.doc,.docx" required>
                <p class="bantuan">PDF atau DOCX, maksimal {{ $maks }} MB. Versi lama tetap tersimpan.</p></div>
            <div><label class="label" for="catatan_mahasiswa">Keterangan perubahan</label><input class="input" id="catatan_mahasiswa" name="catatan_mahasiswa" maxlength="255" value="{{ old('catatan_mahasiswa') }}" placeholder="Apa yang berubah dari versi sebelumnya"></div>
            <button class="btn btn-utama" type="submit"><x-ikon n="unggah" c="size-4" />Unggah</button>
        </form>
        <section class="panel">
            <div class="panel-kepala"><h2>Riwayat versi</h2></div>
            <ul class="divide-y divide-garis">
                @forelse ($dokumen as $d)
                    <li class="px-4 py-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <a href="{{ route('mahasiswa.dokumen.show', $d) }}" class="font-bold">{{ $d->label() }}</a>
                            <span class="text-xs text-redup">{{ $d->tanggal_unggah->translatedFormat('d M Y, H:i') }} · {{ number_format($d->ukuran_kb) }} KB</span>
                        </div>
                        @if ($d->catatan_mahasiswa)<p class="text-redup">{{ $d->catatan_mahasiswa }}</p>@endif
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            @foreach ($s->pembimbing as $p)
                                @php $r = $d->reviewDari($p->id_dosen); @endphp
                                <span class="text-xs">{{ $p->urutan == 1 ? 'P1' : 'P2' }}:</span>
                                @if ($r)<x-status :s="$r->hasil" />@else<span class="pill pill-abu">Menunggu review</span>@endif
                            @endforeach
                            <a href="{{ route('berkas.dokumen', $d) }}" class="btn btn-kecil ml-auto"><x-ikon n="unduh" c="size-3.5" />Unduh</a>
                        </div>
                    </li>
                @empty
                    <li><x-kosong>Belum ada dokumen. Unggah draf pertama Anda untuk direview pembimbing.</x-kosong></li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layout>

<x-layout title="Pengajuan judul" judul="Pengajuan judul">
    @if ($m->skripsi)
        <div class="panel panel-isi mb-4 text-sm">Judul Anda telah disetujui: <strong>{{ $m->skripsi->judul_final }}</strong>.</div>
    @elseif ($bolehAjukan)
        @php $revisi = $terakhir && $terakhir->status === 'perlu_revisi'; @endphp
        <form method="post" action="{{ route('mahasiswa.judul.store') }}" class="panel panel-isi mb-4 space-y-4"
              x-data="judulSerupa('{{ route('mahasiswa.judul.serupa') }}')" x-init="cari($refs.j.value, $refs.k.value)">
            @csrf
            @if ($revisi)
                <div class="rounded-md bg-dasar p-3 text-sm"><p class="font-semibold">Pengajuan ulang dari: {{ $terakhir->judul }}</p><p class="mt-1">Catatan kaprodi: {{ $terakhir->catatan_kaprodi }}</p></div>
            @endif
            <div>
                <label class="label" for="judul">Judul</label>
                <textarea class="input" id="judul" name="judul" rows="2" x-ref="j" @input="cari($refs.j.value, $refs.k.value)" required minlength="15" maxlength="255">{{ old('judul', $revisi ? $terakhir->judul : '') }}</textarea>
            </div>
            <div>
                <label class="label" for="latar_masalah">Latar masalah</label>
                <textarea class="input" id="latar_masalah" name="latar_masalah" rows="5" required minlength="50">{{ old('latar_masalah', $revisi ? $terakhir->latar_masalah : '') }}</textarea>
                <p class="bantuan">Uraikan masalah yang ingin diteliti dan alasan pentingnya, minimal 50 karakter.</p>
            </div>
            <div>
                <label class="label" for="kata_kunci">Kata kunci</label>
                <input class="input" id="kata_kunci" name="kata_kunci" x-ref="k" @input="cari($refs.j.value, $refs.k.value)" value="{{ old('kata_kunci', $revisi ? $terakhir->kata_kunci : '') }}" required maxlength="150" placeholder="misalnya: manajemen kurikulum, madrasah">
                <p class="bantuan">Pisahkan dengan koma. Kata kunci dipakai untuk menampilkan judul serupa.</p>
            </div>
            <div x-show="hasil.length" x-cloak class="rounded-md border border-dashed border-emas bg-emas-50 p-3 text-sm">
                <p class="font-semibold">Judul serupa yang pernah diajukan</p>
                <ul class="mt-1 space-y-1">
                    <template x-for="h in hasil"><li><span x-text="h.judul"></span> <span class="text-redup" x-text="'(' + (h.nama || '') + ', ' + (h.tahun || '') + ')'"></span></li></template>
                </ul>
                <p class="bantuan">Ini bahan pertimbangan, bukan penolakan otomatis. Pastikan judul Anda memiliki fokus yang berbeda.</p>
            </div>
            <button class="btn btn-utama" type="submit">Kirim pengajuan</button>
        </form>
    @else
        <div class="panel panel-isi mb-4 text-sm">Pengajuan Anda sedang diperiksa kaprodi. Anda dapat mengajukan lagi setelah ada keputusan.</div>
    @endif

    <section class="panel">
        <div class="panel-kepala"><h2>Riwayat pengajuan</h2></div>
        <ul class="divide-y divide-garis">
            @forelse ($riwayat as $p)
                <li class="px-4 py-3 text-sm">
                    <div class="flex flex-wrap items-center gap-2"><x-status :s="$p->status" /><span class="text-redup">Diajukan {{ $p->tanggal_ajuan->translatedFormat('d M Y') }}</span>@if($p->id_pengajuan_asal)<span class="pill pill-abu">Pengajuan ulang</span>@endif</div>
                    <p class="mt-1 font-medium">{{ $p->judul }}</p>
                    @if ($p->catatan_kaprodi)<p class="mt-1 text-redup">Catatan kaprodi: {{ $p->catatan_kaprodi }}</p>@endif
                </li>
            @empty
                <li><x-kosong>Belum ada pengajuan.</x-kosong></li>
            @endforelse
        </ul>
    </section>
</x-layout>

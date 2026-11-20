<x-layout title="Review dokumen" judul="Review dokumen">
    <section class="panel mb-4">
        <div class="panel-kepala"><h2>Menunggu review Anda</h2><span class="pill pill-jingga">{{ $menunggu->count() }}</span></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Mahasiswa</th><th>Dokumen</th><th>Diunggah</th><th></th></tr></thead><tbody>
        @forelse ($menunggu as $d)
            @php $lama = $d->tanggal_unggah->lt(now()->subDays(\App\Models\Pengaturan::angka('batas_review_hari'))); @endphp
            <tr><td class="font-medium">{{ $d->skripsi->mahasiswa->pengguna->nama }}</td><td>{{ $d->label() }}</td>
                <td class="whitespace-nowrap">{{ $d->tanggal_unggah->format('d/m/Y') }} @if($lama)<span class="pill pill-merah">Lewat batas</span>@endif</td>
                <td><a href="{{ route('dosen.review.show', $d) }}" class="btn btn-utama btn-kecil">Review</a></td></tr>
        @empty <tr><td colspan="4"><x-kosong>Tidak ada draf yang menunggu.</x-kosong></td></tr> @endforelse
        </tbody></table></div>
    </section>
    <section class="panel">
        <div class="panel-kepala"><h2>Review terakhir</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Mahasiswa</th><th>Dokumen</th><th>Hasil</th><th>Tanggal</th></tr></thead><tbody>
        @forelse ($selesai as $r)
            <tr><td>{{ $r->dokumen->skripsi->mahasiswa->pengguna->nama }}</td><td><a href="{{ route('dosen.review.show', $r->dokumen) }}">{{ $r->dokumen->label() }}</a></td><td><x-status :s="$r->hasil" /></td><td>{{ $r->tanggal_review->format('d/m/Y') }}</td></tr>
        @empty <tr><td colspan="4"><x-kosong>Belum ada review.</x-kosong></td></tr> @endforelse
        </tbody></table></div>
    </section>
</x-layout>

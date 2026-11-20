<x-layout title="Hasil ujian" judul="Hasil seminar proposal dan sidang">
    <section class="panel mb-4"><div class="panel-kepala"><h2>Siap ujian</h2></div>@include('admin.ujian._siap', ['siap' => $siap])</section>
    <section class="panel">
        <div class="panel-kepala"><h2>Riwayat hasil ujian</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Mahasiswa</th><th>Ujian</th><th>Tanggal</th><th>Hasil</th><th>Catatan</th><th>Dicatat oleh</th></tr></thead><tbody>
        @forelse ($riwayat as $u)
            <tr><td>{{ $u->skripsi->mahasiswa->pengguna->nama }}</td><td>{{ \App\Models\Persetujuan::JENIS[$u->jenis] }}</td><td>{{ $u->tanggal_pelaksanaan->format('d/m/Y') }}</td><td><x-status :s="$u->hasil" /></td><td>{{ $u->catatan }}{{ $u->tanggal_revisi_selesai ? ' Revisi selesai '.$u->tanggal_revisi_selesai->format('d/m/Y') : '' }}</td><td>{{ $u->admin->nama }}</td></tr>
        @empty <tr><td colspan="6"><x-kosong>Belum ada hasil ujian.</x-kosong></td></tr> @endforelse
        </tbody></table></div>
    </section>
    <div class="mt-4">{{ $riwayat->links() }}</div>
</x-layout>

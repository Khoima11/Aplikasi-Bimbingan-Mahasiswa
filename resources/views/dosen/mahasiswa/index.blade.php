<x-layout title="Mahasiswa bimbingan" judul="Mahasiswa bimbingan">
    <div class="panel overflow-x-auto">
        <table class="tbl"><thead><tr><th>Nama</th><th>Sebagai</th><th>Tahap</th><th>Bimbingan terakhir</th><th>Menunggu validasi</th><th>Status</th></tr></thead><tbody>
        @forelse ($mhs as $s)
            <tr><td><a href="{{ route('dosen.mahasiswa.show', $s) }}" class="font-medium">{{ $s->mahasiswa->pengguna->nama }}</a><br><span class="text-xs text-redup">{{ $s->mahasiswa->nim }}</span></td>
                <td>{{ $s->sebagai }}</td><td>{{ $s->tahapan->nama_tahapan }}</td>
                <td>{{ $s->terakhir_bimbingan ? \Carbon\Carbon::parse($s->terakhir_bimbingan)->format('d/m/Y') : '-' }}</td>
                <td>{{ $s->menunggu ?: '-' }}</td><td><x-indikator :items="$s->indikator" /></td></tr>
        @empty <tr><td colspan="6"><x-kosong>Belum ada mahasiswa bimbingan aktif.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
</x-layout>

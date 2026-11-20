<x-layout title="Dashboard pemantauan" judul="Dashboard pemantauan">
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-angka label="Skripsi aktif" :nilai="$jumlahAktif" :href="route('kaprodi.mahasiswa.index')" />
        <x-angka label="Pengajuan judul menunggu" :nilai="$menunggu" :tegas="true" :href="route('kaprodi.judul.index')">Verifikasi</x-angka>
        <x-angka label="Perlu ditinjau" :nilai="$ditinjau->count()" :tegas="true" :href="route('kaprodi.mahasiswa.index', ['ditinjau' => 1])">Lihat daftar</x-angka>
        <x-angka label="Rata-rata lama skripsi" :nilai="$rata ? $rata.' bln' : '-'" />
    </div>
    <div class="grid gap-4 lg:grid-cols-[1fr_1.3fr]">
        <section class="panel">
            <div class="panel-kepala"><h2>Sebaran mahasiswa per tahapan</h2></div>
            <div class="p-4"><canvas id="grafikTahap" height="220" role="img" aria-label="Grafik jumlah mahasiswa per tahapan: @foreach($sebaran as $x){{ $x['nama'] }} {{ $x['jumlah'] }}; @endforeach"></canvas></div>
            <p class="px-4 pb-3 text-xs text-redup">Tahap Pengajuan Judul menghitung mahasiswa aktif yang belum memiliki skripsi.</p>
        </section>
        <section class="panel">
            <div class="panel-kepala"><h2>Mahasiswa perlu ditinjau</h2></div>
            <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Nama</th><th>Tahap</th><th>Indikator</th></tr></thead><tbody>
            @forelse ($ditinjau->take(8) as $s)
                <tr><td><a href="{{ route('kaprodi.mahasiswa.show', $s) }}" class="font-medium">{{ $s->mahasiswa->pengguna->nama }}</a></td><td>{{ $s->tahapan->nama_tahapan }}</td><td><x-indikator :items="$s->indikator" /></td></tr>
            @empty <tr><td colspan="3"><x-kosong>Tidak ada mahasiswa yang perlu ditinjau.</x-kosong></td></tr> @endforelse
            </tbody></table></div>
        </section>
    </div>
    <section class="panel mt-4">
        <div class="panel-kepala"><h2>Beban bimbingan dosen</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Dosen</th><th>Pembimbing I</th><th>Pembimbing II</th><th>Draf melewati batas review</th><th>Catatan menunggu validasi</th></tr></thead><tbody>
        @foreach ($dosen as $d)
            <tr><td class="font-medium">{{ $d->pengguna->nama }}</td>
                <td><span class="{{ $d->p1 >= $d->kuota_pembimbing_1 ? 'font-bold text-merah' : '' }}">{{ $d->p1 }} / {{ $d->kuota_pembimbing_1 }}</span></td>
                <td><span class="{{ $d->p2 >= $d->kuota_pembimbing_2 ? 'font-bold text-merah' : '' }}">{{ $d->p2 }} / {{ $d->kuota_pembimbing_2 }}</span></td>
                <td>{{ $d->draf_lama ?: '-' }}</td><td>{{ $d->menunggu ?: '-' }}</td></tr>
        @endforeach
        </tbody></table></div>
    </section>
    @push('skrip')
    <script type="module">
        new Chart(document.getElementById('grafikTahap'), {
            type: 'bar',
            data: { labels: @json($sebaran->pluck('nama')), datasets: [{ data: @json($sebaran->pluck('jumlah')), backgroundColor: ['#C9CFD8','#7A1F3D','#7A1F3D','#7A1F3D','#7A1F3D','#2F7D5B'], borderRadius: 4, maxBarThickness: 44 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#EEF0F3' } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });
    </script>
    @endpush
</x-layout>

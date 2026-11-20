<x-layout title="Periode dan tahapan" judul="Periode, tahapan, dan pengaturan">
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="panel">
            <div class="panel-kepala"><h2>Periode</h2></div>
            <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Nama</th><th>Mulai</th><th>Selesai</th><th>Status</th></tr></thead><tbody>
            @foreach ($periode as $p)
                <tr><td>{{ $p->nama_periode }}</td><td>{{ $p->tanggal_mulai->format('d/m/Y') }}</td><td>{{ $p->tanggal_selesai->format('d/m/Y') }}</td>
                    <td>@if($p->status === 'aktif')<x-status s="aktif" />@else<form method="post" action="{{ route('admin.periode.aktif', $p) }}">@csrf<button class="btn btn-kecil">Jadikan aktif</button></form>@endif</td></tr>
            @endforeach
            </tbody></table></div>
            <form method="post" action="{{ route('admin.periode.store') }}" class="grid gap-2 border-t border-garis p-4 sm:grid-cols-[1.4fr_1fr_1fr_auto] sm:items-end">
                @csrf
                <div><label class="label" for="nama_periode">Nama</label><input class="input" id="nama_periode" name="nama_periode" placeholder="Genap 2026/2027" required maxlength="30"></div>
                <div><label class="label" for="tanggal_mulai">Mulai</label><input class="input" type="date" id="tanggal_mulai" name="tanggal_mulai" required></div>
                <div><label class="label" for="tanggal_selesai">Selesai</label><input class="input" type="date" id="tanggal_selesai" name="tanggal_selesai" required></div>
                <button class="btn">Tambah</button>
            </form>
        </section>
        <form method="post" action="{{ route('admin.tahapan.update') }}" class="panel">
            @csrf @method('put')
            <div class="panel-kepala"><h2>Tahapan skripsi</h2><button class="btn btn-utama btn-kecil">Simpan</button></div>
            <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>No</th><th>Tahapan</th><th>Batas hari</th><th>Min. bimbingan per pembimbing</th><th>Butuh persetujuan</th></tr></thead><tbody>
            @foreach ($tahapan as $t)
                <tr><td>{{ $t->urutan }}</td>
                    <td><input class="input !py-1" name="t[{{ $t->id_tahapan }}][nama_tahapan]" value="{{ $t->nama_tahapan }}" aria-label="Nama tahapan {{ $t->urutan }}"></td>
                    <td><input class="input !w-20 !py-1" type="number" name="t[{{ $t->id_tahapan }}][batas_hari]" value="{{ $t->batas_hari }}" aria-label="Batas hari tahap {{ $t->urutan }}"></td>
                    <td>@if($t->butuh_persetujuan)<input class="input !w-20 !py-1" type="number" name="t[{{ $t->id_tahapan }}][min_bimbingan]" value="{{ $t->min_bimbingan }}" aria-label="Minimal bimbingan tahap {{ $t->urutan }}">@else<span class="text-redup">-</span>@endif</td>
                    <td>{{ $t->butuh_persetujuan ? 'Ya' : 'Tidak' }}</td></tr>
            @endforeach
            </tbody></table></div>
            <p class="px-4 py-3 text-xs text-redup">Urutan dan gerbang persetujuan mengikuti alur program studi. Perubahan nilai tidak memengaruhi persetujuan dan hasil ujian yang sudah tercatat.</p>
        </form>
    </div>
    <form method="post" action="{{ route('admin.pengaturan.update') }}" class="panel mt-4">
        @csrf @method('put')
        <div class="panel-kepala"><h2>Pengaturan sistem</h2><button class="btn btn-utama btn-kecil">Simpan</button></div>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($pengaturan as $p)
                <div><label class="label" for="p_{{ $p->kunci }}">{{ $p->keterangan }}</label><input class="input" id="p_{{ $p->kunci }}" name="p[{{ $p->kunci }}]" value="{{ $p->nilai }}" required maxlength="100"></div>
            @endforeach
        </div>
    </form>
</x-layout>

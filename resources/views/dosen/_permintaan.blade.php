<article class="panel panel-isi text-sm" x-data="{ mode: '' }">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="font-bold">{{ $j->skripsi->mahasiswa->pengguna->nama }}</p><x-status :s="$j->status" />
    </div>
    <p>{{ $j->tanggal->translatedFormat('l, d M Y') }} · {{ substr($j->jam, 0, 5) }} · {{ ucfirst($j->mode) }}</p>
    <p class="text-redup">{{ $j->rencana_bahasan }}</p>
    @if ($j->status === 'diajukan')
        <div class="mt-3 flex flex-wrap gap-2" x-show="!mode">
            <form method="post" action="{{ route('dosen.jadwal.setujui', $j) }}">@csrf<button class="btn btn-hijau btn-kecil" type="submit">Setujui</button></form>
            <button type="button" class="btn btn-kecil" @click="mode='usul'">Usulkan waktu lain</button>
            <button type="button" class="btn btn-kecil" @click="mode='tolak'">Tolak</button>
        </div>
        <form x-show="mode==='tolak'" x-cloak method="post" action="{{ route('dosen.jadwal.tolak', $j) }}" class="mt-3 space-y-2">
            @csrf<input class="input" name="catatan_dosen" required placeholder="Alasan penolakan" aria-label="Alasan penolakan">
            <div class="flex gap-2"><button class="btn btn-bahaya btn-kecil">Tolak jadwal</button><button type="button" class="btn btn-kecil" @click="mode=''">Batal</button></div>
        </form>
        <form x-show="mode==='usul'" x-cloak method="post" action="{{ route('dosen.jadwal.usul', $j) }}" class="mt-3 space-y-2">
            @csrf
            <div class="grid grid-cols-2 gap-2"><input class="input" type="date" name="tanggal" min="{{ today()->toDateString() }}" required aria-label="Tanggal usulan"><input class="input" type="time" name="jam" required aria-label="Jam usulan"></div>
            <input class="input" name="catatan_dosen" placeholder="Keterangan (opsional)" aria-label="Keterangan">
            <div class="flex gap-2"><button class="btn btn-utama btn-kecil">Kirim usulan</button><button type="button" class="btn btn-kecil" @click="mode=''">Batal</button></div>
        </form>
    @else
        <p class="mt-2 text-redup">Menunggu jawaban mahasiswa atas usulan waktu.</p>
    @endif
</article>

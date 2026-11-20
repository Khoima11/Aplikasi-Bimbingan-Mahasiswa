<x-layout title="Jadwal dan slot" judul="Jadwal dan slot ketersediaan">
    <div class="grid gap-4 lg:grid-cols-[2fr_3fr]">
        <div class="space-y-4">
            <form method="post" action="{{ route('dosen.slot.store') }}" class="panel panel-isi space-y-3">
                @csrf
                <h2>Buka slot baru</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="label" for="tanggal">Tanggal</label><input class="input" type="date" id="tanggal" name="tanggal" min="{{ today()->toDateString() }}" value="{{ old('tanggal') }}" required></div>
                    <div><label class="label" for="kuota">Kuota mahasiswa</label><input class="input" type="number" id="kuota" name="kuota" min="1" max="20" value="{{ old('kuota', 3) }}" required></div>
                    <div><label class="label" for="jam_mulai">Jam mulai</label><input class="input" type="time" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', '09:00') }}" required></div>
                    <div><label class="label" for="jam_selesai">Jam selesai</label><input class="input" type="time" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', '10:30') }}" required></div>
                    <div><label class="label" for="mode">Mode</label><select class="input" id="mode" name="mode"><option value="luring">Luring</option><option value="daring">Daring</option></select></div>
                    <div><label class="label" for="ulang">Ulangi mingguan</label><select class="input" id="ulang" name="ulang">@foreach ([0 => 'Tidak', 1 => '+1 minggu', 3 => '+3 minggu', 7 => '+7 minggu'] as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                </div>
                <div><label class="label" for="lokasi">Ruangan atau tautan daring</label><input class="input" id="lokasi" name="lokasi" maxlength="150" value="{{ old('lokasi') }}"></div>
                <button class="btn btn-utama" type="submit">Buka slot</button>
            </form>
            <section class="panel">
                <div class="panel-kepala"><h2>Slot saya</h2></div>
                <ul class="divide-y divide-garis">
                    @forelse ($slot as $sl)
                        <li class="flex items-center justify-between gap-2 px-4 py-2.5 text-sm {{ $sl->tanggal->lt(today()) ? 'opacity-60' : '' }}">
                            <span><span class="font-semibold">{{ $sl->tanggal->translatedFormat('D, d M') }}</span> {{ substr($sl->jam_mulai, 0, 5) }}–{{ substr($sl->jam_selesai, 0, 5) }}<br><span class="text-redup">{{ ucfirst($sl->mode) }} · terisi {{ $sl->terisi }}/{{ $sl->kuota }}</span></span>
                            <form method="post" action="{{ route('dosen.slot.status', $sl) }}">@csrf<button class="btn btn-kecil" title="Ubah status">{{ $sl->status === 'buka' ? 'Tutup' : 'Buka lagi' }}</button></form>
                        </li>
                    @empty <li><x-kosong>Belum ada slot. Mahasiswa hanya bisa mengajukan jadwal pada slot yang Anda buka.</x-kosong></li> @endforelse
                </ul>
            </section>
        </div>
        <div class="space-y-4">
            <section>
                <h2 class="mb-2">Permintaan jadwal</h2>
                <div class="grid gap-3">
                    @forelse ($permintaan as $j) @include('dosen._permintaan', ['j' => $j]) @empty <div class="panel"><x-kosong>Tidak ada permintaan jadwal.</x-kosong></div> @endforelse
                </div>
            </section>
            <section class="panel">
                <div class="panel-kepala"><h2>Jadwal disetujui</h2></div>
                <ul class="divide-y divide-garis">
                    @forelse ($mendatang as $j)
                        <li class="px-4 py-2.5 text-sm"><span class="font-semibold">{{ $j->tanggal->translatedFormat('D, d M') }} · {{ substr($j->jam, 0, 5) }}</span> {{ $j->skripsi->mahasiswa->pengguna->nama }}<br><span class="text-redup">{{ $j->rencana_bahasan }}</span></li>
                    @empty <li><x-kosong>Belum ada jadwal mendatang.</x-kosong></li> @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layout>

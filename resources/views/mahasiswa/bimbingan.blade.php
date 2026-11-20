<x-layout title="Bimbingan" judul="Bimbingan">
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="panel" x-data="{ tab: {{ $s->pembimbing->first()?->id_dosen ?? 0 }} }">
            <div class="panel-kepala"><h2>Ajukan jadwal</h2></div>
            <div class="baris-tab mb-0 px-3 pt-2">
                @foreach ($s->pembimbing as $p)
                    <button type="button" class="tab" :class="tab === {{ $p->id_dosen }} && 'tab-aktif'" @click="tab = {{ $p->id_dosen }}">{{ $p->label() }}</button>
                @endforeach
            </div>
            @foreach ($s->pembimbing as $p)
                <div x-show="tab === {{ $p->id_dosen }}" class="p-4" @if(! $loop->first) x-cloak @endif>
                    <p class="mb-3 text-sm text-redup">{{ $p->dosen->pengguna->nama }}</p>
                    @forelse ($slot[$p->id_dosen] as $sl)
                        <form method="post" action="{{ route('mahasiswa.jadwal.store') }}" class="mb-3 rounded-2xl bg-krem p-4" x-data="{ buka: false }">
                            @csrf
                            <input type="hidden" name="id_slot" value="{{ $sl->id_slot }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="text-sm"><p class="font-bold">{{ $sl->tanggal->translatedFormat('l, d M') }} · {{ substr($sl->jam_mulai, 0, 5) }}–{{ substr($sl->jam_selesai, 0, 5) }}</p>
                                    <p class="text-redup">{{ ucfirst($sl->mode) }}{{ $sl->lokasi ? ', '.$sl->lokasi : '' }} · sisa {{ $sl->kuota - $sl->terisi }}</p></div>
                                <button type="button" class="btn btn-kecil" x-show="!buka" @click="buka = true">Pilih</button>
                            </div>
                            <div x-show="buka" x-cloak class="mt-3 space-y-2">
                                <label class="label" for="rb{{ $sl->id_slot }}">Rencana bahasan</label>
                                <input class="input" id="rb{{ $sl->id_slot }}" name="rencana_bahasan" required maxlength="255" placeholder="Bab atau hal yang akan dibahas">
                                <div class="flex gap-2"><button class="btn btn-utama btn-kecil" type="submit">Ajukan jadwal</button><button type="button" class="btn btn-kecil" @click="buka = false">Batal</button></div>
                            </div>
                        </form>
                    @empty
                        <x-kosong class="!py-4">Belum ada slot yang bisa dipilih. Slot harus diajukan paling lambat {{ $batasJam }} jam sebelum dimulai.</x-kosong>
                    @endforelse
                </div>
            @endforeach
        </section>

        <section class="panel">
            <div class="panel-kepala"><h2>Jadwal saya</h2></div>
            <ul class="divide-y divide-krem-2">
                @forelse ($jadwal as $j)
                    <li class="px-4 py-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-semibold">{{ $j->tanggal->translatedFormat('d M Y') }} · {{ substr($j->jam, 0, 5) }}</span>
                            <x-status :s="$j->status" />
                        </div>
                        <p class="text-redup">{{ $j->dosen->pengguna->nama }} · {{ ucfirst($j->mode) }}</p>
                        @if ($j->rencana_bahasan)<p>{{ $j->rencana_bahasan }}</p>@endif
                        @if ($j->catatan_dosen)<p class="mt-1 rounded bg-krem px-2 py-1">Dosen: {{ $j->catatan_dosen }}</p>@endif
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if ($j->status === 'diusulkan_ulang')
                                <form method="post" action="{{ route('mahasiswa.jadwal.terima', $j) }}">@csrf<button class="btn btn-utama btn-kecil">Terima usulan</button></form>
                            @endif
                            @if (in_array($j->status, ['diajukan', 'disetujui', 'diusulkan_ulang']) && $j->tanggal->gte(today()))
                                <form method="post" action="{{ route('mahasiswa.jadwal.batal', $j) }}" onsubmit="return confirm('Batalkan jadwal ini?')">@csrf<button class="btn btn-kecil">{{ $j->status === 'diusulkan_ulang' ? 'Tolak usulan' : 'Batalkan' }}</button></form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li><x-kosong>Belum ada jadwal.</x-kosong></li>
                @endforelse
            </ul>
        </section>
    </div>

    <section id="catatan" class="mt-4 grid gap-4 lg:grid-cols-[2fr_3fr]">
        <form method="post" action="{{ route('mahasiswa.catatan.store') }}" class="panel panel-isi space-y-3" x-data="{ jadwal: '{{ old('id_jadwal', $jadwalTanpaCatatan->first()?->id_jadwal) }}' }">
            @csrf
            <h2>Isi catatan bimbingan</h2>
            <p class="text-sm text-redup">Tulis setelah bimbingan selesai. Catatan dihitung setelah divalidasi dosen.</p>
            <div>
                <label class="label" for="id_jadwal">Jadwal terkait</label>
                <select class="input" id="id_jadwal" name="id_jadwal" x-model="jadwal">
                    @foreach ($jadwalTanpaCatatan as $j)<option value="{{ $j->id_jadwal }}">{{ $j->tanggal->format('d/m/Y') }} · {{ $j->dosen->pengguna->nama }}</option>@endforeach
                    <option value="">Tanpa jadwal (bimbingan insidental/daring)</option>
                </select>
            </div>
            <div x-show="jadwal === ''" x-cloak class="grid grid-cols-2 gap-3">
                <div><label class="label" for="id_dosen">Pembimbing</label><select class="input" id="id_dosen" name="id_dosen">@foreach ($s->pembimbing as $p)<option value="{{ $p->id_dosen }}">{{ $p->label() }}</option>@endforeach</select></div>
                <div><label class="label" for="tanggal_bimbingan">Tanggal</label><input class="input" type="date" id="tanggal_bimbingan" name="tanggal_bimbingan" max="{{ today()->toDateString() }}" value="{{ old('tanggal_bimbingan', today()->toDateString()) }}"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label" for="mode">Mode</label><select class="input" id="mode" name="mode"><option value="luring">Luring</option><option value="daring">Daring</option></select></div>
                <div><label class="label" for="bab_dibahas">Bab dibahas</label><input class="input" id="bab_dibahas" name="bab_dibahas" value="{{ old('bab_dibahas') }}" required maxlength="50" placeholder="Bab 2"></div>
            </div>
            <div><label class="label" for="ringkasan">Ringkasan hasil bimbingan</label><textarea class="input" id="ringkasan" name="ringkasan" rows="5" required minlength="20" placeholder="Poin yang dibahas dan arahan dosen">{{ old('ringkasan') }}</textarea></div>
            <button class="btn btn-utama" type="submit">Kirim untuk validasi</button>
        </form>

        <div class="panel">
            <div class="panel-kepala"><h2>Catatan saya</h2><a href="{{ route('lembar', $s) }}" class="text-sm font-semibold">Lembar bimbingan</a></div>
            <ul class="divide-y divide-krem-2">
                @forelse ($catatan as $c)
                    <li class="px-4 py-3 text-sm" x-data="{ edit: false }">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-semibold">{{ $c->tanggal_bimbingan->format('d/m/Y') }} · {{ $c->bab_dibahas }}</span>
                            <x-status :s="$c->status_validasi" />
                        </div>
                        <p class="text-redup">{{ $c->dosen->pengguna->nama }}</p>
                        <p class="mt-1 whitespace-pre-line">{{ $c->ringkasan }}</p>
                        @if ($c->catatan_dosen)<p class="mt-2 rounded border-l-4 border-navy bg-krem px-3 py-2">Dosen: {{ $c->catatan_dosen }}</p>@endif
                        @if ($c->status_validasi === 'dikoreksi')
                            <button type="button" class="btn btn-kecil mt-2" x-show="!edit" @click="edit = true">Perbaiki catatan</button>
                            <form x-show="edit" x-cloak method="post" action="{{ route('mahasiswa.catatan.update', $c) }}" class="mt-2 space-y-2">
                                @csrf @method('put')
                                <input class="input" name="bab_dibahas" value="{{ $c->bab_dibahas }}" required aria-label="Bab dibahas">
                                <textarea class="input" name="ringkasan" rows="4" required aria-label="Ringkasan">{{ $c->ringkasan }}</textarea>
                                <button class="btn btn-utama btn-kecil" type="submit">Kirim ulang</button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li><x-kosong>Belum ada catatan bimbingan.</x-kosong></li>
                @endforelse
            </ul>
        </div>
    </section>
</x-layout>

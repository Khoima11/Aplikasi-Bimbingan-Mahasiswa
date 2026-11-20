<x-layout title="Laporan" judul="Laporan">
    <div class="baris-tab">
        @foreach (\App\Http\Controllers\Kaprodi\LaporanController::TAB as $k => $l)
            <a href="{{ request()->fullUrlWithQuery(['tab' => $k]) }}" class="tab whitespace-nowrap {{ $tab === $k ? 'tab-aktif' : '' }}">{{ $l }}</a>
        @endforeach
    </div>
    <form method="get" class="mb-4 flex flex-wrap items-end gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div><label class="label" for="periode">Periode pengajuan</label><select class="input w-auto" id="periode" name="periode"><option value="">Semua periode</option>@foreach ($daftarPeriode as $p)<option value="{{ $p->id_periode }}" @selected(request('periode') == $p->id_periode)>{{ $p->nama_periode }}</option>@endforeach</select></div>
        <div><label class="label" for="angkatan">Angkatan</label><select class="input w-auto" id="angkatan" name="angkatan"><option value="">Semua</option>@foreach ($daftarAngkatan as $a)<option @selected(request('angkatan') == $a)>{{ $a }}</option>@endforeach</select></div>
        <button class="btn" type="submit">Tampilkan</button>
        <a href="{{ route('kaprodi.laporan.pdf', request()->query() + ['tab' => $tab]) }}" class="btn btn-utama sm:ml-auto"><x-ikon n="unduh" c="size-4" />Unduh PDF</a>
    </form>
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($ringkas as $l => $v)<x-angka :label="$l" :nilai="$v" />@endforeach
    </div>
    <div class="panel overflow-x-auto">
        <table class="tbl"><thead><tr>@foreach ($kepala as $h)<th>{{ $h }}</th>@endforeach</tr></thead><tbody>
        @forelse ($baris as $b)<tr>@foreach ($b as $c)<td>{{ $c }}</td>@endforeach</tr>
        @empty <tr><td colspan="{{ count($kepala) }}"><x-kosong>Tidak ada data untuk filter ini.</x-kosong></td></tr> @endforelse
        </tbody></table>
    </div>
</x-layout>

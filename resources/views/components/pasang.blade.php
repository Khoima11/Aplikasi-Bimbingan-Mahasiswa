{{-- Tombol "Pasang aplikasi" + panduan per perangkat. Varian: terang (latar putih), gelap (sidebar navy) --}}
@props(['varian' => 'terang'])
<div x-data="pasangAplikasi()" x-show="!terpasang" x-cloak {{ $attributes }}>
    @if ($varian === 'gelap')
        <button type="button" @click="pasang()" class="flex w-full items-center gap-3 rounded-[18px] bg-white/[.06] px-3 py-2.5 text-left text-white hover:bg-white/10">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-krem text-navy"><x-ikon n="unduh" c="size-4" /></span>
            <span><span class="block text-sm font-medium">Pasang aplikasi</span><span class="block text-xs text-white/55">Buka SIBIMTA tanpa browser</span></span>
        </button>
    @elseif ($varian === 'kartu')
        <div class="flex items-center gap-3 rounded-[var(--radius-panel)] bg-white p-4">
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-krem p-1.5"><x-logo class="size-full" /></span>
            <span class="min-w-0 flex-1"><span class="block font-semibold">Pasang SIBIMTA di HP</span><span class="block text-sm text-redup">Ikon di layar utama, buka layar penuh seperti aplikasi.</span></span>
            <button type="button" @click="pasang()" class="btn btn-utama btn-kecil shrink-0">Pasang</button>
        </div>
    @else
        <button type="button" @click="pasang()" class="nav-terang w-full"><x-ikon n="unduh" />Pasang aplikasi di HP</button>
    @endif

    {{-- Panduan bila browser tidak menyediakan dialog pemasangan otomatis --}}
    <div x-show="panduan" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-labelledby="judul-pasang">
        <div class="absolute inset-0 bg-navy/50" @click="panduan = false"></div>
        <div class="relative w-full max-w-md rounded-t-[28px] bg-white p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))] text-tinta sm:rounded-[28px]" @keydown.escape.window="panduan = false">
            <div class="mb-4 flex items-center gap-3">
                <span class="grid size-12 place-items-center rounded-2xl bg-krem p-1.5"><x-logo class="size-full" /></span>
                <div><p id="judul-pasang" class="text-lg font-bold">Pasang SIBIMTA</p><p class="text-sm text-redup">Gratis, tanpa Play Store atau App Store</p></div>
            </div>
            <ol x-show="platform === 'ios'" class="space-y-3 text-sm">
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">1</span><span>Buka halaman ini di <b>Safari</b>.</span></li>
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">2</span><span>Ketuk tombol <b>Bagikan</b> <svg class="inline size-4 align-[-2px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M8 7l4-4 4 4M5 12v8h14v-8"/></svg> di bagian bawah layar.</span></li>
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">3</span><span>Pilih <b>Tambah ke Layar Utama</b> (Add to Home Screen), lalu ketuk <b>Tambah</b>.</span></li>
            </ol>
            <ol x-show="platform === 'android'" class="space-y-3 text-sm">
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">1</span><span>Buka halaman ini di <b>Chrome</b>.</span></li>
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">2</span><span>Ketuk menu <b>⋮</b> di kanan atas.</span></li>
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">3</span><span>Pilih <b>Instal aplikasi</b> atau <b>Tambahkan ke layar utama</b>, lalu ketuk <b>Instal</b>.</span></li>
            </ol>
            <ol x-show="platform === 'laptop'" class="space-y-3 text-sm">
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">1</span><span>Buka halaman ini di <b>Chrome</b> atau <b>Edge</b>.</span></li>
                <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-navy text-xs font-bold text-white">2</span><span>Klik ikon <b>Instal</b> di ujung kanan bilah alamat, atau menu <b>⋮ → Instal SIBIMTA</b>.</span></li>
            </ol>
            <p class="mt-4 rounded-2xl bg-krem p-3 text-xs text-redup">Setelah terpasang, buka SIBIMTA dari ikon di layar utama. Aplikasi tetap butuh internet untuk menampilkan data terbaru.</p>
            <button type="button" class="btn btn-utama mt-4 w-full" @click="panduan = false">Mengerti</button>
        </div>
    </div>
</div>

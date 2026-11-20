<x-layout title="Notifikasi">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h1>Notifikasi</h1>
        <form method="post" action="{{ route('notifikasi.semua') }}">@csrf<button class="btn btn-kecil" type="submit">Tandai semua dibaca</button></form>
    </div>
    <div class="panel divide-y divide-garis">
        @forelse ($items as $n)
            <a href="{{ route('notifikasi.baca', $n) }}" class="flex gap-3 px-4 py-3 text-tinta no-underline hover:bg-dasar {{ $n->sudah_dibaca ? '' : 'bg-marun-50/50' }}">
                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $n->sudah_dibaca ? 'bg-transparent' : 'bg-marun-700' }}" aria-hidden="true"></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm {{ $n->sudah_dibaca ? 'font-medium' : 'font-bold' }}">{{ $n->judul }}@unless($n->sudah_dibaca)<span class="sr-only"> (belum dibaca)</span>@endunless</span>
                    <span class="block text-sm text-redup">{{ $n->pesan }}</span>
                    <span class="mt-0.5 block text-xs text-redup">{{ $n->tanggal_kirim->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <x-kosong>Belum ada notifikasi. Pemberitahuan tentang jadwal, catatan, revisi, dan judul akan muncul di sini.</x-kosong>
        @endforelse
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</x-layout>

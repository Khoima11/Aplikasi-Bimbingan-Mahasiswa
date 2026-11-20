<x-layout title="Ganti kata sandi" judul="Ganti kata sandi">
    @if (auth()->user()->wajib_ganti_password)
        <div class="mb-4 rounded-xl border border-abu/40 bg-krem px-4 py-3 text-sm">Ini login pertama Anda. Ganti kata sandi awal sebelum melanjutkan.</div>
    @endif
    <form method="post" action="{{ route('password.update') }}" class="panel panel-isi max-w-md space-y-4">
        @csrf
        <div><label class="label" for="password_lama">Kata sandi saat ini</label><input class="input" type="password" id="password_lama" name="password_lama" required autocomplete="current-password"></div>
        <div><label class="label" for="password">Kata sandi baru</label><input class="input" type="password" id="password" name="password" required autocomplete="new-password"><p class="bantuan">Minimal 8 karakter, berisi huruf dan angka.</p></div>
        <div><label class="label" for="password_confirmation">Ulangi kata sandi baru</label><input class="input" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"></div>
        <button class="btn btn-utama" type="submit">Simpan kata sandi</button>
    </form>
</x-layout>

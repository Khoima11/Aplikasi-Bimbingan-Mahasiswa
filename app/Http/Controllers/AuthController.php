<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function form() { return view('auth.login'); }

    public function login(Request $r)
    {
        $v = $r->validate(['username' => 'required|string', 'password' => 'required|string'], [], ['username' => 'username', 'password' => 'kata sandi']);
        $username = trim($v['username']);
        $field = str_contains($username, '@') ? 'email' : 'username';
        if (! Auth::attempt([$field => $username, 'password' => $v['password']])) {
            return back()->withErrors(['username' => 'Username atau kata sandi salah.'])->onlyInput('username');
        }
        $u = Auth::user();
        if (! $u->aktif()) {
            Auth::logout();
            return back()->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi admin program studi.'])->onlyInput('username');
        }
        $r->session()->regenerate();
        $u->forceFill(['terakhir_login' => now()])->save();
        session(['peran_aktif' => $u->daftarPeran()[0] ?? null]);
        LogAktivitas::catat('Login', 'pengguna', $u->id_pengguna);
        return redirect()->intended(route('beranda'));
    }

    public function logout(Request $r)
    {
        LogAktivitas::catat('Logout', 'pengguna', auth()->id());
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function formPassword() { return view('auth.password'); }

    public function ubahPassword(Request $r)
    {
        $u = $this->pengguna();
        $r->validate([
            'password_lama' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:password_lama'],
        ], [], ['password_lama' => 'kata sandi lama', 'password' => 'kata sandi baru']);
        if (! Hash::check($r->password_lama, $u->password)) {
            return back()->withErrors(['password_lama' => 'Kata sandi lama tidak sesuai.']);
        }
        $u->forceFill(['password' => $r->password, 'wajib_ganti_password' => false])->save();
        LogAktivitas::catat('Mengubah kata sandi', 'pengguna', $u->id_pengguna);
        return redirect()->route('beranda')->with('ok', 'Kata sandi berhasil diubah.');
    }
}

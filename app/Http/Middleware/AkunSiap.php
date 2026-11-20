<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Menolak akun nonaktif (AB-16) dan mewajibkan ganti kata sandi awal (KF-02). */
class AkunSiap
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if ($u && ! $u->aktif()) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi admin program studi.']);
        }
        if ($u && $u->wajib_ganti_password && ! $request->routeIs('password.*', 'logout')) {
            return redirect()->route('password.form');
        }
        if ($u && ! session('peran_aktif')) {
            session(['peran_aktif' => $u->daftarPeran()[0] ?? null]);
        }
        return $next($request);
    }
}

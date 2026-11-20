<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peran)
    {
        $u = $request->user();
        foreach ($peran as $p) {
            if ($u && $u->punyaPeran($p)) {
                if (! in_array(session('peran_aktif'), $peran, true)) session(['peran_aktif' => $p]);
                return $next($request);
            }
        }
        abort(403, 'Halaman ini tidak tersedia untuk peran Anda.');
    }
}

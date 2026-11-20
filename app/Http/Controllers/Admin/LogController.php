<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $r)
    {
        $q = LogAktivitas::with('pengguna')->orderByDesc('id_log');
        if ($s = trim((string) $r->cari)) $q->whereRaw('LOWER(aktivitas) LIKE ?', ['%'.mb_strtolower($s).'%']);
        return view('admin.log', ['items' => $q->paginate(30)->withQueryString()]);
    }
}

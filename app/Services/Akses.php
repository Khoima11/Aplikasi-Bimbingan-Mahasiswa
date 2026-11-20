<?php

namespace App\Services;

use App\Models\Pengguna;
use App\Models\Skripsi;

/** Pemeriksaan hak akses data skripsi (AB-11, KNF-01). */
class Akses
{
    public static function bolehLihat(?Pengguna $u, Skripsi $s): bool
    {
        if (! $u) return false;
        if ($u->punyaPeran('kaprodi')) return true;
        if ($u->mahasiswa && $u->mahasiswa->id_mahasiswa === $s->id_mahasiswa) return true;
        if ($u->dosen && $s->semuaPembimbing()->where('id_dosen', $u->dosen->id_dosen)->exists()) return true;
        return false;
    }

    public static function pastikan(Skripsi $s): void
    {
        abort_unless(self::bolehLihat(auth()->user(), $s), 403, 'Anda tidak memiliki akses ke data skripsi ini.');
    }
}

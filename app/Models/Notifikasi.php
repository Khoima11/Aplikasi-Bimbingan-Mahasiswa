<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['sudah_dibaca' => 'boolean', 'tanggal_kirim' => 'datetime']; }

    public static function kirim($idPengguna, string $judul, string $pesan, ?string $tautan = null): void
    {
        foreach ((array) $idPengguna as $id) {
            if (! $id) continue;
            static::create(['id_pengguna' => $id, 'judul' => mb_substr($judul, 0, 100), 'pesan' => mb_substr($pesan, 0, 255),
                'tautan' => $tautan, 'sudah_dibaca' => false, 'tanggal_kirim' => now()]);
        }
    }
}

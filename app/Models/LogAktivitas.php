<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    protected $primaryKey = 'id_log';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['waktu' => 'datetime']; }
    public function pengguna() { return $this->belongsTo(Pengguna::class, 'id_pengguna'); }

    public static function catat(string $aktivitas, ?string $tabel = null, $idData = null): void
    {
        static::create(['id_pengguna' => auth()->id(), 'aktivitas' => mb_substr($aktivitas, 0, 100), 'tabel_terkait' => $tabel,
            'id_data' => $idData, 'alamat_ip' => request()?->ip(), 'waktu' => now()]);
    }
}

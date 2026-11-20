<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $primaryKey = 'id_periode';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date']; }
    public static function aktif(): ?self { return static::where('status', 'aktif')->orderByDesc('tanggal_mulai')->first(); }
}

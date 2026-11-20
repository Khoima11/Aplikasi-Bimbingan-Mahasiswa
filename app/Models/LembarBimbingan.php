<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LembarBimbingan extends Model
{
    protected $table = 'lembar_bimbingan';
    protected $primaryKey = 'id_lembar';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_cetak' => 'datetime']; }
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function pencetak() { return $this->belongsTo(Pengguna::class, 'id_pencetak'); }
}

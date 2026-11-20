<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembimbing extends Model
{
    protected $table = 'pembimbing';
    protected $primaryKey = 'id_pembimbing';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_penetapan' => 'date', 'tanggal_berakhir' => 'date']; }
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function label(): string { return 'Pembimbing '.($this->urutan == 1 ? 'I' : 'II'); }
}

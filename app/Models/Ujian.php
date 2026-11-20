<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    protected $table = 'ujian';
    protected $primaryKey = 'id_ujian';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_pelaksanaan' => 'date', 'tanggal_revisi_selesai' => 'date', 'tanggal_input' => 'datetime']; }
    public const HASIL = ['lulus' => 'Lulus', 'lulus_dengan_revisi' => 'Lulus dengan revisi', 'tidak_lulus' => 'Tidak lulus'];
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function admin() { return $this->belongsTo(Pengguna::class, 'id_admin'); }
}

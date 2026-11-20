<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanBimbingan extends Model
{
    protected $table = 'catatan_bimbingan';
    protected $primaryKey = 'id_catatan';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_bimbingan' => 'date', 'tanggal_input' => 'datetime', 'tanggal_validasi' => 'datetime']; }
    public const STATUS = ['menunggu' => 'Menunggu', 'divalidasi' => 'Divalidasi', 'dikoreksi' => 'Dikoreksi'];
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function jadwal() { return $this->belongsTo(JadwalBimbingan::class, 'id_jadwal'); }
}

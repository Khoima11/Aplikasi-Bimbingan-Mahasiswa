<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalBimbingan extends Model
{
    protected $table = 'jadwal_bimbingan';
    protected $primaryKey = 'id_jadwal';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal' => 'date', 'tanggal_ajuan' => 'datetime']; }
    public const STATUS = ['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'diusulkan_ulang' => 'Diusulkan ulang', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan', 'selesai' => 'Selesai'];
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function slot() { return $this->belongsTo(SlotBimbingan::class, 'id_slot'); }
    public function catatan() { return $this->hasOne(CatatanBimbingan::class, 'id_jadwal'); }
}

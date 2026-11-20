<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotBimbingan extends Model
{
    protected $table = 'slot_bimbingan';
    protected $primaryKey = 'id_slot';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal' => 'date']; }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function jadwal() { return $this->hasMany(JadwalBimbingan::class, 'id_slot'); }
    public function terisi(): int { return $this->jadwal()->whereIn('status', ['diajukan', 'disetujui', 'selesai'])->count(); }
    public function sisa(): int { return max(0, (int) $this->kuota - $this->terisi()); }
    public function mulai() { return $this->tanggal->copy()->setTimeFromTimeString($this->jam_mulai); }
}

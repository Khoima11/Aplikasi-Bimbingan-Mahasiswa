<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $table = 'dokumen';
    protected $primaryKey = 'id_dokumen';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_unggah' => 'datetime']; }
    public const BAGIAN = ['proposal' => 'Proposal', 'bab1' => 'Bab 1', 'bab2' => 'Bab 2', 'bab3' => 'Bab 3', 'bab4' => 'Bab 4', 'bab5' => 'Bab 5', 'naskah_lengkap' => 'Naskah Lengkap'];
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function review() { return $this->hasMany(ReviewDokumen::class, 'id_dokumen'); }
    public function reviewDari(int $idDosen): ?ReviewDokumen { return $this->review->firstWhere('id_dosen', $idDosen); }
    public function labelBagian(): string { return self::BAGIAN[$this->bagian] ?? $this->bagian; }
    public function label(): string { return $this->labelBagian().' v'.$this->versi; }
}

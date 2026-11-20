<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosen';
    protected $primaryKey = 'id_dosen';
    public $timestamps = false;
    protected $guarded = [];

    public function pengguna() { return $this->belongsTo(Pengguna::class, 'id_pengguna'); }
    public function pembimbing() { return $this->hasMany(Pembimbing::class, 'id_dosen'); }
    public function slot() { return $this->hasMany(SlotBimbingan::class, 'id_dosen'); }

    /** Jumlah bimbingan aktif sebagai pembimbing ke-$urutan (skripsi aktif, penetapan aktif). */
    public function terisi(int $urutan): int
    {
        return Pembimbing::where('id_dosen', $this->id_dosen)->where('urutan', $urutan)->where('status', 'aktif')
            ->whereHas('skripsi', fn ($q) => $q->where('status_skripsi', 'aktif'))->count();
    }
    public function kuota(int $urutan): int { return (int) $this->{'kuota_pembimbing_'.$urutan}; }
    public function getNamaAttribute() { return $this->pengguna?->nama; }
}

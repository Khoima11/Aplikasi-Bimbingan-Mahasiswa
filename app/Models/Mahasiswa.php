<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'id_mahasiswa';
    public $timestamps = false;
    protected $guarded = [];

    public function pengguna() { return $this->belongsTo(Pengguna::class, 'id_pengguna'); }
    public function skripsi() { return $this->hasOne(Skripsi::class, 'id_mahasiswa'); }
    public function pengajuan() { return $this->hasMany(PengajuanJudul::class, 'id_mahasiswa')->orderByDesc('id_pengajuan'); }

    /** Semester berjalan dihitung dari angkatan (semester ganjil mulai Agustus). */
    public function semester(): int
    {
        $n = now();
        return max(1, ($n->year - (int) $this->angkatan) * 2 + ($n->month >= 8 ? 1 : 0));
    }
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Pengguna extends Authenticatable
{
    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['password'];
    protected $rememberTokenName = null;
    protected function casts(): array
    {
        return ['wajib_ganti_password' => 'boolean', 'terakhir_login' => 'datetime', 'password' => 'hashed'];
    }

    public function peran() { return $this->belongsToMany(Peran::class, 'pengguna_peran', 'id_pengguna', 'id_peran'); }
    public function mahasiswa() { return $this->hasOne(Mahasiswa::class, 'id_pengguna'); }
    public function dosen() { return $this->hasOne(Dosen::class, 'id_pengguna'); }
    public function notifikasi() { return $this->hasMany(Notifikasi::class, 'id_pengguna'); }

    public function daftarPeran(): array
    {
        $urut = ['admin', 'kaprodi', 'dosen', 'mahasiswa'];
        $milik = $this->relationLoaded('peran') ? $this->peran : $this->peran()->get();
        return array_values(array_intersect($urut, $milik->pluck('nama_peran')->all()));
    }
    public function punyaPeran(string $p): bool { return in_array($p, $this->daftarPeran(), true); }
    public function aktif(): bool { return $this->status === 'aktif'; }
    public function getRememberTokenName() { return null; }
}

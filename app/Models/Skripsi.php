<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skripsi extends Model
{
    protected $table = 'skripsi';
    protected $primaryKey = 'id_skripsi';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_masuk_tahap' => 'date', 'tanggal_selesai' => 'date'];
    }

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa'); }
    public function pengajuan() { return $this->belongsTo(PengajuanJudul::class, 'id_pengajuan'); }
    public function tahapan() { return $this->belongsTo(Tahapan::class, 'id_tahapan'); }
    public function semuaPembimbing() { return $this->hasMany(Pembimbing::class, 'id_skripsi')->orderBy('urutan')->orderByDesc('id_pembimbing'); }
    public function pembimbing() { return $this->hasMany(Pembimbing::class, 'id_skripsi')->where('status', 'aktif')->orderBy('urutan'); }
    public function jadwal() { return $this->hasMany(JadwalBimbingan::class, 'id_skripsi'); }
    public function catatan() { return $this->hasMany(CatatanBimbingan::class, 'id_skripsi'); }
    public function revisi() { return $this->hasMany(ItemRevisi::class, 'id_skripsi'); }
    public function dokumen() { return $this->hasMany(Dokumen::class, 'id_skripsi'); }
    public function persetujuan() { return $this->hasMany(Persetujuan::class, 'id_skripsi'); }
    public function ujian() { return $this->hasMany(Ujian::class, 'id_skripsi'); }
    public function lembar() { return $this->hasMany(LembarBimbingan::class, 'id_skripsi'); }

    public function pembimbingKe(int $u): ?Pembimbing
    {
        return $this->pembimbing->firstWhere('urutan', $u);
    }
    public function idDosenAktif(): array { return $this->pembimbing->pluck('id_dosen')->all(); }
    public function dibimbingOleh(?Dosen $d): bool { return $d && in_array($d->id_dosen, $this->idDosenAktif()); }

    /** Bimbingan tervalidasi dengan dosen tertentu (opsional: sejak tanggal masuk tahap). */
    public function jumlahTervalidasi(?int $idDosen = null, bool $tahapIni = false): int
    {
        $q = $this->catatan()->where('status_validasi', 'divalidasi');
        if ($idDosen) $q->where('id_dosen', $idDosen);
        if ($tahapIni) $q->whereDate('tanggal_bimbingan', '>=', $this->tanggal_masuk_tahap);
        return $q->count();
    }
}

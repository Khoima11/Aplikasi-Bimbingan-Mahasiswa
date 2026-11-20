<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanJudul extends Model
{
    protected $table = 'pengajuan_judul';
    protected $primaryKey = 'id_pengajuan';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_ajuan' => 'datetime', 'tanggal_putusan' => 'datetime']; }

    public const STATUS = ['menunggu' => 'Menunggu', 'perlu_revisi' => 'Perlu revisi', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa'); }
    public function periode() { return $this->belongsTo(Periode::class, 'id_periode'); }
    public function asal() { return $this->belongsTo(self::class, 'id_pengajuan_asal'); }
    public function kaprodi() { return $this->belongsTo(Pengguna::class, 'id_kaprodi'); }

    public function pengajuanKe(): int
    {
        $n = 1; $p = $this;
        while ($p->id_pengajuan_asal && $n < 50) { $p = self::find($p->id_pengajuan_asal); $n++; if (! $p) break; }
        return $n;
    }
}

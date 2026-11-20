<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persetujuan extends Model
{
    protected $table = 'persetujuan';
    protected $primaryKey = 'id_persetujuan';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_persetujuan' => 'datetime']; }
    public const JENIS = ['seminar_proposal' => 'Seminar Proposal', 'sidang' => 'Sidang Munaqasyah'];
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
}

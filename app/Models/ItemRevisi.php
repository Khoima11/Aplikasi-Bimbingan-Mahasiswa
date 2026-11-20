<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemRevisi extends Model
{
    protected $table = 'item_revisi';
    protected $primaryKey = 'id_revisi';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_dibuat' => 'datetime', 'tanggal_dikerjakan' => 'datetime', 'tanggal_diterima' => 'datetime']; }
    public const STATUS = ['belum' => 'Belum dikerjakan', 'dikerjakan' => 'Dikerjakan', 'diterima' => 'Diterima'];
    public function skripsi() { return $this->belongsTo(Skripsi::class, 'id_skripsi'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function catatan() { return $this->belongsTo(CatatanBimbingan::class, 'id_catatan'); }
    public function review() { return $this->belongsTo(ReviewDokumen::class, 'id_review'); }
    public function dokumenPerbaikan() { return $this->belongsTo(Dokumen::class, 'id_dokumen_perbaikan'); }
}

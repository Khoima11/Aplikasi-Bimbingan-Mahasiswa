<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewDokumen extends Model
{
    protected $table = 'review_dokumen';
    protected $primaryKey = 'id_review';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['tanggal_review' => 'datetime']; }
    public function dokumen() { return $this->belongsTo(Dokumen::class, 'id_dokumen'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function revisi() { return $this->hasMany(ItemRevisi::class, 'id_review'); }
}

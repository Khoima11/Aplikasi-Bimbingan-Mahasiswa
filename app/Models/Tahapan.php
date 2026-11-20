<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tahapan extends Model
{
    protected $table = 'tahapan';
    protected $primaryKey = 'id_tahapan';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['butuh_persetujuan' => 'boolean', 'urutan' => 'integer', 'batas_hari' => 'integer', 'min_bimbingan' => 'integer']; }

    public static function urutan(int $u): ?self { return static::where('urutan', $u)->first(); }
    public static function semua() { return static::orderBy('urutan')->get(); }
    /** Jenis persetujuan yang dibutuhkan untuk keluar dari tahap ini. */
    public function jenisPersetujuan(): ?string
    {
        if (! $this->butuh_persetujuan) return null;
        return $this->urutan <= 2 ? 'seminar_proposal' : 'sidang';
    }
}

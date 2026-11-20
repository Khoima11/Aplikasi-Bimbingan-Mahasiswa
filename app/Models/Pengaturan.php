<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $primaryKey = 'id_pengaturan';
    public $timestamps = false;
    protected $guarded = [];

    public const BAWAAN = [
        'batas_tanpa_bimbingan_hari' => ['30', 'Batas hari tanpa bimbingan tervalidasi sebelum ditandai Perlu Ditinjau'],
        'batas_review_hari' => ['14', 'Batas hari draf menunggu review sebelum ditandai pada dashboard'],
        'batas_revisi_hari' => ['30', 'Batas hari item revisi belum dikerjakan sebelum ditandai Perlu Ditinjau'],
        'semester_peringatan' => ['12', 'Semester mulai muncul peringatan masa studi'],
        'ukuran_berkas_mb' => ['10', 'Ukuran berkas maksimal (MB)'],
        'batas_ajuan_jadwal_jam' => ['24', 'Batas pengajuan jadwal sebelum slot dimulai (jam)'],
        'nama_prodi' => ['Manajemen Pendidikan Islam', 'Nama program studi pada kop lembar bimbingan dan laporan'],
        'nama_institusi' => ['UIN Raden Fatah Palembang', 'Nama institusi pada kop lembar bimbingan dan laporan'],
    ];

    private static ?array $cache = null;
    public static function nilai(string $kunci, $bawaan = null)
    {
        if (static::$cache === null) static::$cache = static::pluck('nilai', 'kunci')->all();
        return static::$cache[$kunci] ?? (static::BAWAAN[$kunci][0] ?? $bawaan);
    }
    public static function angka(string $kunci): int { return (int) static::nilai($kunci); }
    public static function lupakan(): void { static::$cache = null; }
}

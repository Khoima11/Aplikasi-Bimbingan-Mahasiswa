<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    protected $table = 'peran';
    protected $primaryKey = 'id_peran';
    public $timestamps = false;
    protected $guarded = [];
    public const LABEL = ['admin' => 'Admin Prodi', 'kaprodi' => 'Kaprodi', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa'];
}

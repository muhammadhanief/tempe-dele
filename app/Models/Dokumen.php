<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $table = 'm_pejabat';  
    protected $primaryKey = 'id_pejabat';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'jabatan',
        'nip_lama',
        'nip',
        'status',
        'tahun',
    ];
}

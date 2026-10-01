<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Pegawai extends Authenticatable
{
    protected $table = 'm_pegawai';
    protected $primaryKey = 'id_pegawai';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'password',
        'nama',
        'nip_lama',
        'nip',
        'golongan',
        'role',
        'foto_url',
        'satker',
        'kd_satker',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

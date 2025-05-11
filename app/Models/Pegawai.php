<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $fillable = [
        'id_pegawai',
        'nama',
        'email',
        'nohp',
        'jabatan',
    ];

    protected $primaryKey = 'id_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->hasOne(User::class);
    }
}

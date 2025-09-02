<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Pegawai extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'pegawais';
    protected $primaryKey = 'id_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pegawai',
        'nama',
        'email',
        'nohp',
        'jabatan',
        'divisi',
    ];

    protected $appends = [
        'foto_url',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('foto_pegawai')
            ->singleFile();
    }

    /**
     * Relasi ke model User (satu pegawai punya satu user)
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'pegawai_id', 'id_pegawai');
    }

    // Accessor
    protected function fotoUrl(): Attribute
    {
        return Attribute::get(fn () =>
            $this->hasMedia('foto_pegawai')
                ? $this->getFirstMediaUrl('foto_pegawai')
                : null
        );
    }
}

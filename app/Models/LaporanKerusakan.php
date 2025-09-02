<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class LaporanKerusakan extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'laporan_kerusakan';

    protected $fillable = [
        'tgl_selesai',
        'tgl_batal',
        'order_id',
        'divisi_pelapor',
        'status_id',
        'status_checking_id',
        'pelapor_nama',
        'pelapor_id',
        'jumlah_rusak',
        'divisi_bertanggung_jawab',
        'keterangan',
        'keterangan_checking',
        'is_human_error',
    ];

    protected $casts = [
        'tgl_selesai' => 'datetime',
        'tgl_batal' => 'datetime',
        'is_human_error' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['foto_kerusakan'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto_kerusakan')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');
    }

    public function getFotoKerusakanAttribute()
    {
        return $this->getFirstMediaUrl('foto_kerusakan');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id_order');
    }

    public function status()
    {
        return $this->belongsTo(Kategori::class, 'status_id');
    }

    public function statusChecking()
    {
        return $this->belongsTo(Kategori::class, 'status_checking_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    protected function tglSelesai(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function tglBatal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }
}

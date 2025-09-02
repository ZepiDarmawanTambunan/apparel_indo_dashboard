<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Packaging extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'packaging';

    protected $fillable = [
        'tgl_terima',
        'tgl_selesai',
        'tgl_batal',
        'order_id',
        'status_id',
        'user_id',
        'user_nama',
        'keterangan',
        'jumlah_dikerjakan',
    ];

    protected $casts = [
        'tgl_selesai' => 'datetime',
        'tgl_terima' => 'datetime',
        'tgl_batal' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['file_packaging'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file_packaging')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');
    }

    public function getFilePackagingAttribute()
    {
        return $this->getFirstMediaUrl('file_packaging');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id_order');
    }

    public function status()
    {
        return $this->belongsTo(Kategori::class, 'status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

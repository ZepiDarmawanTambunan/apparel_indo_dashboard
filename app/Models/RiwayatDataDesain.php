<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class RiwayatDataDesain extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'riwayat_data_desain';

    protected $fillable = [
        'user_id',
        'user_nama',
        'data_desain_id',
        'tgl_feedback',
        'keterangan',
        'feedback',
    ];

    protected $casts = [
        'tgl_feedback' => 'datetime',
    ];

    protected $appends = ['file_url'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file_riwayat_data_desain')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    public function getFileUrlAttribute()
    {
        return $this->getFirstMediaUrl('file_riwayat_data_desain');
    }

    public function dataDesain()
    {
        return $this->belongsTo(DataDesain::class, 'data_desain_id');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function tglFeedback(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }
}

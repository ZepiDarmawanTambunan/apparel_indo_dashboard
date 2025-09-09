<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class RiwayatCuttingKain extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'riwayat_cutting_kain';

    protected $fillable = [
        'cutting_kain_id',
        'user_id',
        'user_nama',
        'produk_id',
        'produk_nama',
        'salary',
        'jumlah_dikerjakan',
    ];

    public function cuttingKain()
    {
        return $this->belongsTo(CuttingKain::class, 'cutting_kain_id');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }
}

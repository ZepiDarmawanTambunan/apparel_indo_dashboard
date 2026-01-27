<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Pembayaran extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pembayaran',
        'order_id',
        'bayar',
        'kembalian',
        'kategori_id',
        'status_id',
        'user_nama',
        'user_id',
    ];

    protected $appends = ['bukti_pembayaran', 'created_at_format'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('bukti_pembayaran')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');
    }

    // START RELASI
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id_order');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'pembayaran_id', 'id_pembayaran');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function status()
    {
        return $this->belongsTo(Kategori::class, 'status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // END RELASI

    //  START MUTATOR
    public function getBuktiPembayaranAttribute()
    {
        return $this->getFirstMediaUrl('bukti_pembayaran');
    }

    public function getCreatedAtFormatAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('d-m-Y')
            : null;
    }
    //  END MUTATOR
}

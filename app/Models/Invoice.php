<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_invoice';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_invoice',
        'order_id',
        'pembayaran_id',
        'kategori_id',
        'status_id',
        'user_nama',
        'user_id',
        'keterangan',
    ];

    // START RELASI
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id_order');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id', 'id_pembayaran');
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

    //  START ACCESSOR
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }
    //  END ACCESSOR
}

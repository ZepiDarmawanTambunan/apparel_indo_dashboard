<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderTambahan extends Model
{
    use SoftDeletes;

    protected $table = 'order_tambahan';

    protected $fillable = [
        'order_detail_id',
        'produk_id',
        'nama',
        'kategori',
        'satuan',
        'qty',
        'harga',
        'total',
        'user_nama',
        'user_id',
    ];

    // Relasi ke Order Detail
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    // Relasi ke Produk (nullable)
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id_produk');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

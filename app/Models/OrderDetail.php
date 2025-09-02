<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use SoftDeletes;

    protected $table = 'order_detail';

    protected $fillable = [
        'order_id',
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

    // Relasi ke order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id_order');
    }

    // Relasi ke order tambahan
    public function orderTambahan()
    {
        return $this->hasMany(OrderTambahan::class, 'order_detail_id');
    }

    // Relasi ke produk (nullable)
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id_produk');
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

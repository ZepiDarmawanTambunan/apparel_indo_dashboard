<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HargaProduk extends Model
{
    use SoftDeletes;

    protected $table = 'harga_produk';

    protected $fillable = [
        'produk_id',
        'harga_before',
        'harga_after',
        'tgl_jam',
        'user_nama',
        'user_id',
    ];

    protected $dates = [
        'tgl_jam',
    ];

    // Relasi ke Produk
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StokProduk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stok_produk';

    protected $fillable = [
        'produk_id',
        'stok_before',
        'stok_after',
        'kategori_id',
        'keterangan',
        'tgl_jam',
        'user_nama',
        'user_id',
    ];

    protected $dates = [
        'tgl_jam',
        'deleted_at',
    ];

    // Relasi ke Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id_produk');
    }

    // Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

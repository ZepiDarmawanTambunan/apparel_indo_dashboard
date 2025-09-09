<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Produk extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'nama',
        'deskripsi',
        'harga',
        'stok',
        'kategori_id',
        'satuan_id',
        'parent_id',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto_produk')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(800)
            ->sharpen(10);
    }

    public function getCoverFoto()
    {
        return $this->getMedia('foto_produk')->firstWhere('custom_properties.is_cover', true);
    }

    public function getGaleriFoto()
    {
        return $this->getMedia('foto_produk')->reject(function ($media) {
            return $media->getCustomProperty('is_cover') === true;
        });
    }

    public function setCoverFoto(Media $media)
    {
        foreach ($this->getMedia('foto_produk') as $item) {
            $item->setCustomProperty('is_cover', false)->save();
        }

        $media->setCustomProperty('is_cover', true)->save();
    }

    // Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    // Relasi ke Satuan
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    // Relasi parent (produk induk)
    public function parent()
    {
        return $this->belongsTo(Produk::class, 'parent_id', 'id_produk');
    }

    // Relasi children (produk turunan)
    public function children()
    {
        return $this->hasMany(Produk::class, 'parent_id', 'id_produk');
    }

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id_order');
    }

    public function orderTambahan()
    {
        return $this->hasMany(OrderTambahan::class, 'order_id', 'id_order');
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class, 'produk_id', 'id_produk');
    }
}

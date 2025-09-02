<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use SoftDeletes;

    protected $table = 'kategori';

    protected $fillable = [
        'id',
        'nama',
        'deskripsi',
        'parent_id',
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'kategori_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Kategori::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Kategori::class, 'parent_id');
    }

    public static function getKategoriId(string $parentName, string $childName): int
    {
        $parent = self::whereRaw('LOWER(nama) = ?', [strtolower($parentName)])->first();

        if (!$parent) {
            throw new \Exception("Parent kategori '$parentName' tidak ditemukan.");
        }

        $child = self::where('parent_id', $parent->id)
            ->whereRaw('LOWER(nama) = ?', [strtolower($childName)])
            ->first();

        if (!$child) {
            throw new \Exception("Kategori '$childName' dengan parent '$parentName' tidak ditemukan.");
        }

        return $child->id;
    }
}

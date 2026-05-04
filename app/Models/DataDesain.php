<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class DataDesain extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_desain';

    protected $fillable = [
        'order_id',
        'status_id',
        'accepted_by_id',
        'accepted_by_name',
        'updated_by_id',
        'updated_by_name',
        'tgl_terima',
        'tgl_selesai',
        'tgl_batal',
        'jumlah_dikerjakan',
    ];

    protected $casts = [
        'tgl_terima' => 'datetime',
        'tgl_selesai' => 'datetime',
        'tgl_batal' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id_order');
    }

    public function status()
    {
        return $this->belongsTo(Kategori::class, 'status_id');
    }

    public function acceptedById()
    {
        return $this->belongsTo(User::class, 'accepted_by_id');
    }

    public function updatedById()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function riwayatDataDesain()
    {
        return $this->hasMany(RiwayatDataDesain::class, 'data_desain_id');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function tglTerima(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function tglSelesai(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function tglBatal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $table = 'invoice_detail';

    protected $fillable = [
        'invoice_id',
        'nama',
        'kategori',
        'satuan',
        'qty',
        'harga',
        'total',
        'user_nama',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id_invoice');
    }

    public function invoiceTambahan()
    {
        return $this->hasMany(InvoiceTambahan::class, 'invoice_detail_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTambahan extends Model
{
    protected $table = 'invoice_tambahan';

    protected $fillable = [
        'invoice_detail_id',
        'nama',
        'kategori',
        'satuan',
        'qty',
        'harga',
        'total',
        'user_nama',
    ];

    // Relasi ke Invoice Detail
    public function invoiceDetail()
    {
        return $this->belongsTo(InvoiceDetail::class, 'invoice_detail_id');
    }
}

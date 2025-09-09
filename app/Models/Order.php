<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'order';
    protected $primaryKey = 'id_order';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_order',
        'nama_pelanggan',
        'nohp_wa',
        'tgl_deadline',
        'keterangan',
        'sub_total',
        'lainnya',
        'diskon',
        'total',
        'total_pembayaran',
        'total_keb_kain',
        'status_id',
        'status_pembayaran_id',
        'user_nama',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $appends = ['sisa_bayar', 'total_laporan_kerusakan', 'total_qty'];

    //  START RELASI
    public function status()
    {
        return $this->belongsTo(Kategori::class, 'status_id');
    }

    public function statusPembayaran()
    {
        return $this->belongsTo(Kategori::class, 'status_pembayaran_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id_order');
    }

    public function orderTambahan()
    {
        return $this->hasMany(OrderTambahan::class, 'order_id', 'id_order');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'order_id', 'id_order');
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'order_id', 'id_order');
    }

    public function dataDesain()
    {
        return $this->hasOne(DataDesain::class, 'order_id', 'id_order');
    }

    public function cetakPrint()
    {
        return $this->hasOne(CetakPrint::class, 'order_id', 'id_order');
    }

    public function pressKain()
    {
        return $this->hasOne(PressKain::class, 'order_id', 'id_order');
    }

    public function cuttingKain()
    {
        return $this->hasOne(CuttingKain::class, 'order_id', 'id_order');
    }

    public function jahit()
    {
        return $this->hasOne(Jahit::class, 'order_id', 'id_order');
    }

    public function sablonPress()
    {
        return $this->hasOne(SablonPress::class, 'order_id', 'id_order');
    }

    public function qc()
    {
        return $this->hasOne(QC::class, 'order_id', 'id_order');
    }

    public function packaging()
    {
        return $this->hasOne(Packaging::class, 'order_id', 'id_order');
    }

    public function laporanKerusakan()
    {
        return $this->hasMany(LaporanKerusakan::class, 'order_id', 'id_order');
    }
    //  END RELASI

    //  START ACCESSOR
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function tglDeadline(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
        );
    }

    protected function sisaBayar(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, $this->attributes['total'] - $this->attributes['total_pembayaran']),
        );
    }

    public function totalLaporanKerusakan(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->laporanKerusakan()->count(),
        );
    }

    protected function totalQty(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orderDetail->sum('qty'),
        );
    }

    public function statusLaporanKerusakan(): Attribute
    {
        return Attribute::make(
            get: function () {
                $pendingId = Kategori::getKategoriId('Status Checking', 'Pending');

                $hasPending = $this->laporanKerusakan()
                    ->where('status_checking_id', $pendingId)
                    ->exists();

                return $hasPending ? 'Pending' : 'Selesai';
            }
        );
    }
    //  END ACCESSOR

    // START METHODS
    public function pembayaranTerakhir()
    {
        $statusSelesaiId = Kategori::getKategoriId('Status Pembayaran', 'Selesai');
        return $this->pembayaran()
            ->with(['kategori', 'status'])
            ->where('status_id', $statusSelesaiId)
            ->latest()
            ->first();
    }

    public function statusPembayaranOrderTerakhir()
    {
        $statusPembayaranOrder = 'Belum Bayar';

        $statusSelesaiId = Kategori::getKategoriId('Status Pembayaran', 'Selesai');
        $pembayaranTerakhir = $this->pembayaran()
            ->where('status_id', $statusSelesaiId)
            ->with('kategori')
            ->latest()
            ->first();

        if($pembayaranTerakhir && $pembayaranTerakhir->kategori?->nama === 'DP Awal') {
            $statusPembayaranOrder = 'DP Awal';
        } elseif($pembayaranTerakhir && $pembayaranTerakhir->kategori?->nama === 'DP Produksi') {
            $statusPembayaranOrder = 'DP Produksi';
        } elseif($pembayaranTerakhir && $pembayaranTerakhir->kategori?->nama === 'Lunas') {
            $statusPembayaranOrder = 'Lunas';
        }

        return Kategori::getKategoriId('Status Pembayaran Order', $statusPembayaranOrder);
    }

    public function statusOrderTerakhir()
    {
        // GET KATEGORI PEMBAYARAN ID
        $kategoriDPAwal = Kategori::getKategoriId('Kategori Pembayaran', 'DP Awal');
        $kategoriDPProduksi = Kategori::getKategoriId('Kategori Pembayaran', 'DP Produksi');
        $kategoriLunas = Kategori::getKategoriId('Kategori Pembayaran', 'Lunas');

        // GET PEMBAYARAN TERAKHIR
        $pembayaranDPAwal = $this->pembayaran()->where('kategori_id', $kategoriDPAwal)->with('status')->latest()->first();
        $pembayaranDPProduksi = $this->pembayaran()->where('kategori_id', $kategoriDPProduksi)->with('status')->latest()->first();
        $pembayaranLunas = $this->pembayaran()->where('kategori_id', $kategoriLunas)->with('status')->latest()->first();

        // GET KATEGORI SIKLUS ID
        $statusDataDesainBatalId = Kategori::getKategoriId('Status Data Desain', 'Batal');
        $statusCetakPrintBatalId = Kategori::getKategoriId('Status Cetak Print', 'Batal');
        $statusPressKainBatalId = Kategori::getKategoriId('Status Press Kain', 'Batal');
        $statusCuttingKainBatalId = Kategori::getKategoriId('Status Cutting Kain', 'Batal');
        $statusJahitBatalId = Kategori::getKategoriId('Status Jahit', 'Batal');
        $statusSablonPressBatalId = Kategori::getKategoriId('Status Sablon Press', 'Batal');
        $statusQcBatalId = Kategori::getKategoriId('Status QC', 'Batal');
        $statusPackagingBatalId = Kategori::getKategoriId('Status Packaging', 'Batal');

        // GET DATA SIKLUS
        $dataDesain = $this->dataDesain()
            ->where('status_id', '!=', $statusDataDesainBatalId)
            ->with('status')
            ->first();

        $cetakPrint = $this->cetakPrint()
            ->where('status_id', '!=', $statusCetakPrintBatalId)
            ->with('status')
            ->first();

        $pressKain = $this->pressKain()
            ->where('status_id', '!=', $statusPressKainBatalId)
            ->with('status')
            ->first();

        $cuttingKain = $this->cuttingKain()
            ->where('status_id', '!=', $statusCuttingKainBatalId)
            ->with('status')
            ->first();

        $jahit = $this->jahit()
            ->where('status_id', '!=', $statusJahitBatalId)
            ->with('status')
            ->first();

        $sablonPress = $this->sablonPress()
            ->where('status_id', '!=', $statusSablonPressBatalId)
            ->with('status')
            ->first();

        $qc = $this->qc()
            ->where('status_id', '!=', $statusQcBatalId)
            ->with('status')
            ->first();

        $packaging = $this->packaging()
            ->where('status_id', '!=', $statusPackagingBatalId)
            ->with('status')
            ->first();

        // =============================
        // START LOGIKA PENENTUAN STATUS
        // =============================
        $statusOrder = 'Menunggu DP Awal';

        //START MENUNGGU ACC
        if ($pembayaranDPAwal && $pembayaranDPAwal->status?->nama === 'Menunggu ACC') {
            $statusOrder = 'Menunggu ACC DP Awal';
        }

        if ($pembayaranDPProduksi && $pembayaranDPProduksi->status?->nama === 'Menunggu ACC') {
            $statusOrder = 'Menunggu ACC DP Produksi';
        }

        if ($pembayaranLunas && $pembayaranLunas->status?->nama === 'Menunggu ACC') {
            $statusOrder = 'Menunggu ACC Lunas';
        }
        // END MENUNGGU ACC

        // START DESAIN DATA
        if ($pembayaranLunas && $pembayaranLunas->status?->nama === 'Selesai') {
            $statusOrder = 'Desain Data';
        }

        if ($pembayaranDPProduksi && $pembayaranDPProduksi->status?->nama === 'Selesai') {
            $statusOrder = 'Desain Data';
        }

        if ($pembayaranDPAwal && $pembayaranDPAwal->status?->nama === 'Selesai') {
            $statusOrder = 'Desain Data';
        }
        // END DESAIN DATA

        // START MENUNGGU DP PRODUKSI
        if ($dataDesain && $dataDesain->status?->nama === 'Selesai') {
            $statusOrder = 'Menunggu DP Produksi';
        }
        // END MENUNGGU DP PRODUKSI

        // START MENUNGGU ACC DP PRODUKSI
        if ($pembayaranDPProduksi && $pembayaranDPProduksi->status?->nama === 'Menunggu ACC') {
            $statusOrder = 'Menunggu ACC DP Produksi';
        }

        if ($pembayaranDPProduksi && $pembayaranDPProduksi->status?->nama === 'Selesai') {
            $statusOrder = 'Cetak & Print';
        }
        // END MENUNGGU ACC DP PRODUKSI

        // START MENUNGGU ACC LUNAS
        if ($dataDesain && $dataDesain->status?->nama === 'Selesai' && $pembayaranLunas && $pembayaranLunas->status?->nama === 'Menunggu ACC') {
            $statusOrder = 'Menunggu ACC Lunas';
        }

        if ($dataDesain && $dataDesain->status?->nama === 'Selesai' && $pembayaranLunas && $pembayaranLunas->status?->nama === 'Selesai') {
            $statusOrder = 'Cetak & Print';
        }
        // END MENUNGGU ACC LUNAS

        // START PROSES PRODUKSI
        if ($cetakPrint && $cetakPrint->status?->nama === 'Selesai') {
            $statusOrder = 'Press Kain';
        }

        if ($pressKain && $pressKain->status?->nama === 'Selesai') {
            $statusOrder = 'Cutting Kain';
        }

        if ($cuttingKain && $cuttingKain->status?->nama === 'Selesai') {
            $statusOrder = 'Jahit';
        }

        if ($jahit && $jahit->status?->nama === 'Selesai') {
            $statusOrder = 'Sablon & Press Kecil';
        }

        if ($sablonPress && $sablonPress->status?->nama === 'Selesai') {
            $statusOrder = 'QC';
        }

        if ($qc && $qc->status?->nama === 'Selesai') {
            $statusOrder = 'Packaging';
        }
        // END PROSES PRODUKSI

        // START LUNAS
        if ($packaging && $packaging->status?->nama === 'Selesai') {
            $statusOrder = 'Menunggu Tagihan Lunas';
        }

        if ($packaging && $packaging->status?->nama === 'Selesai' && $pembayaranLunas && $pembayaranLunas->status?->nama === 'Menunggu ACC') {
            $statusOrder = 'Menunggu ACC Lunas';
        }

        if ($packaging && $packaging->status?->nama === 'Selesai' && $pembayaranLunas && $pembayaranLunas->status?->nama === 'Selesai') {
            $statusOrder = 'Selesai';
        }
        // END LUNAS

        return Kategori::getKategoriId('Status Order', $statusOrder);
    }

    public function prosesKerjaTerakhir()
    {
        // GET KATEGORI SIKLUS ID
        $statusCetakPrintBatalId = Kategori::getKategoriId('Status Cetak Print', 'Batal');
        $statusDataDesainBatalId = Kategori::getKategoriId('Status Data Desain', 'Batal');

        // GET DATA SIKLUS YANG BELUM BATAL
        $cetakPrint = $this->cetakPrint()
            ->where('status_id', '!=', $statusCetakPrintBatalId)
            ->first();

        $dataDesain = $this->dataDesain()
            ->where('status_id', '!=', $statusDataDesainBatalId)
            ->first();

        if ($cetakPrint) return 'Cetak Print';
        if ($dataDesain) return 'Data Desain';

        return null;
    }
    // END METHODS
}

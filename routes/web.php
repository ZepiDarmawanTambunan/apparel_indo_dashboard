<?php

use App\Http\Controllers\CetakPrintController;
use App\Http\Controllers\CheckingController;
use App\Http\Controllers\CuttingKainController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\DesainDataController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JahitController;
use App\Http\Controllers\LaporanKerusakanController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PressKainController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\QCController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SablonPressKecilController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

Route::get('dashboard', function () {
    $user = Auth::user();
    return Inertia::render('Dashboard', [
        'permissions' => $user->getAllPermissions()->pluck('name'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])
    ->prefix('profil')
    ->name('profil.')
    ->group(function () {
        Route::get('/', [ProfilController::class, 'index'])->name('index');
        Route::put('/', [ProfilController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'permission:akses-monitoring'])
    ->prefix('monitoring')
    ->name('monitoring.')
    ->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('index');

        Route::get('/order', [MonitoringController::class, 'order'])->name('order');
        Route::get('/tracking', [MonitoringController::class, 'tracking'])->name('tracking');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('order')
    ->name('order.')
    ->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');

        Route::get('/{id_order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{id_order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{id_order}', [OrderController::class, 'update'])->name('update');
        Route::put('{id_order}/batal', [OrderController::class, 'batal'])->name('batal');

        Route::get('/{id_order}/tracking', [OrderController::class, 'tracking'])->name('tracking');
        Route::get('/{id_order}/pembayaran', [OrderController::class, 'pembayaran'])->name('pembayaran');
        Route::get('/{id_order}/kerusakan', [OrderController::class, 'kerusakan'])->name('kerusakan');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('pembayaran')
    ->name('pembayaran.')
    ->group(function () {
        Route::get('/', [PembayaranController::class, 'index'])->name('index');
        Route::get('/create', [PembayaranController::class, 'create'])->name('create');
        Route::post('/', [PembayaranController::class, 'store'])->name('store');

        Route::get('/{id_pembayaran}', [PembayaranController::class, 'show'])->name('show');
        Route::get('/{id_pembayaran}/edit', [PembayaranController::class, 'edit'])->name('edit');
        Route::put('/{id_pembayaran}', [PembayaranController::class, 'update'])->name('update');
        Route::put('{id_pembayaran}/batal', [PembayaranController::class, 'batal'])->name('batal');
        Route::delete('/{id_pembayaran}', [PembayaranController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('invoice')
    ->name('invoice.')
    ->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::get('/{id_invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{id_invoice}/konfirmasi', [InvoiceController::class, 'konfirmasi'])->name('konfirmasi');

        Route::get('/export/pdf/{id}', [InvoiceController::class, 'exportPdf'])->name('export.pdf');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('desain-data')
    ->name('desain-data.')
    ->group(function () {
        Route::get('/', [DesainDataController::class, 'index'])->name('index');
        Route::get('/create', [DesainDataController::class, 'create'])->name('create');
        Route::get('/{id}', [DesainDataController::class, 'show'])->name('show');
        Route::put('/{id}', [DesainDataController::class, 'update'])->name('update');
        Route::put('/{id}/terima', [DesainDataController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [DesainDataController::class, 'batal'])->name('batal');
        Route::put('/{id}/selesai', [DesainDataController::class, 'selesai'])->name('selesai');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('cetak-print')
    ->name('cetak-print.')
    ->group(function () {
        Route::get('/', [CetakPrintController::class, 'index'])->name('index');
        Route::get('/create', [CetakPrintController::class, 'create'])->name('create');
        Route::get('/{id}', [CetakPrintController::class, 'show'])->name('show');
        Route::put('/{id}', [CetakPrintController::class, 'update'])->name('update');
        Route::put('/{id}/terima', [CetakPrintController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [CetakPrintController::class, 'batal'])->name('batal');
        Route::put('/{id}/selesai', [CetakPrintController::class, 'selesai'])->name('selesai');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('press-kain')
    ->name('press-kain.')
    ->group(function () {
        Route::get('/', [PressKainController::class, 'index'])->name('index');
        Route::get('/create', [PressKainController::class, 'create'])->name('create');
        Route::get('/{id}', [PressKainController::class, 'show'])->name('show');
        Route::put('/{id}/terima', [PressKainController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [PressKainController::class, 'batal'])->name('batal');
        Route::put('/{id}', [PressKainController::class, 'update'])->name('update');
        Route::put('/{id}/selesai', [PressKainController::class, 'selesai'])->name('selesai');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('cutting-kain')
    ->name('cutting-kain.')
    ->group(function () {
        Route::get('/', [CuttingKainController::class, 'index'])->name('index');
        Route::get('/create', [CuttingKainController::class, 'create'])->name('create');
        Route::get('/{id}', [CuttingKainController::class, 'show'])->name('show');
        Route::put('/{id}/terima', [CuttingKainController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [CuttingKainController::class, 'batal'])->name('batal');
        Route::put('/{id}/selesai', [CuttingKainController::class, 'selesai'])->name('selesai');
        Route::put('/{id}', [CuttingKainController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('jahit')
    ->name('jahit.')
    ->group(function () {
        Route::get('/', [JahitController::class, 'index'])->name('index');
        Route::get('/create', [JahitController::class, 'create'])->name('create');
        Route::get('/{id}', [JahitController::class, 'show'])->name('show');
        Route::put('/{id}/terima', [JahitController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [JahitController::class, 'batal'])->name('batal');
        Route::put('/{id}/selesai', [JahitController::class, 'selesai'])->name('selesai');
        Route::put('/{id}', [JahitController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('sablon-press-kecil')
    ->name('sablon-press-kecil.')
    ->group(function () {
        Route::get('/', [SablonPressKecilController::class, 'index'])->name('index');
        Route::get('/create', [SablonPressKecilController::class, 'create'])->name('create');
        Route::get('/{id}', [SablonPressKecilController::class, 'show'])->name('show');
        Route::put('/{id}/terima', [SablonPressKecilController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [SablonPressKecilController::class, 'batal'])->name('batal');
        Route::put('/{id}', [SablonPressKecilController::class, 'update'])->name('update');
        Route::put('/{id}/selesai', [SablonPressKecilController::class, 'selesai'])->name('selesai');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('qc')
    ->name('qc.')
    ->group(function () {
        Route::get('/', [QCController::class, 'index'])->name('index');
        Route::get('/create', [QCController::class, 'create'])->name('create');
        Route::get('/{id}', [QCController::class, 'show'])->name('show');
        Route::put('/{id}/terima', [QCController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [QCController::class, 'batal'])->name('batal');
        Route::put('/{id}', [QCController::class, 'update'])->name('update');
        Route::put('/{id}/selesai', [QCController::class, 'selesai'])->name('selesai');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('packaging')
    ->name('packaging.')
    ->group(function () {
        Route::get('/', [PackagingController::class, 'index'])->name('index');
        Route::get('/create', [PackagingController::class, 'create'])->name('create');
        Route::get('/{id}', [PackagingController::class, 'show'])->name('show');
        Route::put('/{id}/terima', [PackagingController::class, 'terima'])->name('terima');
        Route::put('/{id}/batal', [PackagingController::class, 'batal'])->name('batal');
        Route::put('/{id}', [PackagingController::class, 'update'])->name('update');
        Route::put('/{id}/selesai', [PackagingController::class, 'selesai'])->name('selesai');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('checking')
    ->name('checking.')
    ->group(function () {
        Route::get('/', [CheckingController::class, 'index'])->name('index');
        Route::get('/{id}', [CheckingController::class, 'show'])->name('show');
        Route::get('/create', [CheckingController::class, 'create'])->name('create');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('report')
    ->name('report.')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/kerusakan', [ReportController::class, 'kerusakan'])->name('kerusakan');
        Route::get('/order', [ReportController::class, 'order'])->name('order');
        Route::get('/produk', [ReportController::class, 'produk'])->name('produk');
        Route::get('/jahit', [ReportController::class, 'jahit'])->name('jahit');
        Route::get('/cutting-kain', [ReportController::class, 'cuttingKain'])->name('cutting-kain');

        Route::get('/order/export/pdf', [ReportController::class, 'orderExportPdf'])->name('order.export.pdf');
        Route::get('/order/export/excel', [ReportController::class, 'orderExportExcel'])->name('order.export.excel');

        Route::get('/kerusakan/export/pdf', [ReportController::class, 'kerusakanExportPdf'])->name('kerusakan.export.pdf');
        Route::get('/kerusakan/export/excel', [ReportController::class, 'kerusakanExportExcel'])->name('kerusakan.export.excel');

        Route::get('/produk/export/pdf', [ReportController::class, 'produkExportPdf'])->name('produk.export.pdf');
        Route::get('/produk/export/excel', [ReportController::class, 'produkExportExcel'])->name('produk.export.excel');

        Route::get('/jahit/export/pdf', [ReportController::class, 'jahitExportPdf'])->name('jahit.export.pdf');
        Route::get('/jahit/export/excel', [ReportController::class, 'jahitExportExcel'])->name('jahit.export.excel');

        Route::get('/cutting-kain/export/pdf', [ReportController::class, 'cuttingKainExportPdf'])->name('cutting-kain.export.pdf');
        Route::get('/cutting-kain/export/excel', [ReportController::class, 'cuttingKainExportExcel'])->name('cutting-kain.export.excel');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('gudang')
    ->name('gudang.')
    ->group(function () {
        Route::get('/', [GudangController::class, 'index'])->name('index');
        Route::get('/create', [GudangController::class, 'create'])->name('create');
});

Route::middleware(['auth', 'permission:akses-order'])
    ->prefix('laporan-kerusakan')
    ->name('laporan-kerusakan.')
    ->group(function () {
        Route::post('/', [LaporanKerusakanController::class, 'store'])->name('store');
        Route::put('/{id}/selesai', [LaporanKerusakanController::class, 'selesai'])->name('selesai');
        Route::put('/{id}/batal', [LaporanKerusakanController::class, 'batal'])->name('batal');
        Route::put('/{id}/selesai/checking', [LaporanKerusakanController::class, 'selesaiChecking'])->name('selesai.checking');
        Route::put('/{id}/batal/checking', [LaporanKerusakanController::class, 'batalChecking'])->name('batal.checking');
        Route::put('/{id}', [LaporanKerusakanController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'permission:akses-data'])
    ->prefix('data')
    ->name('data.')
    ->group(function () {
        Route::get('/', [DataController::class, 'index'])->name('index');

        Route::get('/kategori', [DataController::class, 'kategori'])->name('kategori');
        Route::post('/kategori', [DataController::class, 'storeKategori'])->name('kategori.store');
        Route::put('/kategori/{kategori}', [DataController::class, 'updateKategori'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [DataController::class, 'destroyKategori'])->name('kategori.destroy');

        Route::get('/salary', [DataController::class, 'salary'])->name('salary');
        Route::post('/salary/create', [DataController::class, 'createSalary'])->name('salary.create');

        Route::get('/satuan', [DataController::class, 'satuan'])->name('satuan');
        Route::post('/satuan', [DataController::class, 'storeSatuan'])->name('satuan.store');
        Route::put('/satuan/{satuan}', [DataController::class, 'updateSatuan'])->name('satuan.update');
        Route::delete('/satuan/{satuan}', [DataController::class, 'destroySatuan'])->name('satuan.destroy');

        Route::get('/produk', [DataController::class, 'produk'])->name('produk');
        Route::get('/produk/create', [DataController::class, 'createProduk'])->name('produk.create');
        Route::get('/produk/{id_produk}', [DataController::class, 'showProduk'])->name('produk.show');
        Route::post('/produk', [DataController::class, 'storeProduk'])->name('produk.store');
        Route::get('/produk/{id_produk}/edit', [DataController::class, 'editProduk'])->name('produk.edit');
        Route::put('/produk/{id_produk}', [DataController::class, 'updateProduk'])->name('produk.update');
        Route::delete('/produk/{produk}', [DataController::class, 'destroyProduk'])->name('produk.destroy');

        Route::get('/user', [DataController::class, 'user'])->name('user');
        Route::get('/user/create', [DataController::class, 'createUser'])->name('user.create');
        Route::post('/user', [DataController::class, 'storeUser'])->name('user.store');
        Route::get('/user/{user}', [DataController::class, 'showUser'])->name('user.show');
        Route::get('/user/{id}/edit', [DataController::class, 'editUser'])->name('user.edit');
        Route::put('/user/{id}', [DataController::class, 'updateUser'])->name('user.update');
        Route::delete('/user/{id}', [DataController::class, 'destroyUser'])->name('user.destroy');
});


require __DIR__.'/auth.php';

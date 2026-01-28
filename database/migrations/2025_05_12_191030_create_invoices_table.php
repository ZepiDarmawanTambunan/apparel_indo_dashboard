<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('id_invoice', 8)->primary();
            $table->string('order_id');
            $table->string('pembayaran_id');
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('status_id');
            $table->string('user_nama')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            
            $table->unsignedBigInteger('sub_total')->default(0);
            $table->unsignedBigInteger('diskon')->default(0);
            $table->unsignedBigInteger('lainnya')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            
            $table->unsignedBigInteger('bayar')->default(0);
            $table->unsignedBigInteger('kembalian')->default(0);
            
            $table->unsignedBigInteger('sisa_bayar_sblm_pembayaran')->default(0);
            $table->unsignedBigInteger('total_pembayaran_sblm_pembayaran')->default(0);

            $table->unsignedBigInteger('sisa_bayar')->default(0);
            $table->unsignedBigInteger('total_pembayaran')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pembayaran_id')->references('id_pembayaran')->on('pembayaran')->onDelete('cascade');
            $table->foreign('order_id')->references('id_order')->on('order')->onDelete('cascade');
            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('cascade'); // dp awal, dp produksi, lunas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('kategori')->onDelete('cascade'); // batal, pending, terima
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

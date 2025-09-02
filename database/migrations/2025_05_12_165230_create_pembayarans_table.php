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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->string('id_pembayaran', 8)->primary();
            $table->string('order_id');
            $table->unsignedBigInteger('bayar')->default(0);
            $table->unsignedBigInteger('kembalian')->default(0);
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('status_id');
            $table->string('user_nama')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id_order')->on('order')->onDelete('cascade');
            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('cascade'); // dp awal, dp produksi, lunas
            $table->foreign('status_id')->references('id')->on('kategori')->onDelete('cascade'); // batal, terima
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null'); // kasir, superadmin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};

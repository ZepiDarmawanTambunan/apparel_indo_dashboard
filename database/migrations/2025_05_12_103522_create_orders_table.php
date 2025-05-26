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
        Schema::create('order', function (Blueprint $table) {
            $table->string('id_order', 8)->primary();
            $table->string('nama_pelanggan');
            $table->string('nohp_wa');
            $table->date('tgl');
            $table->date('tgl_deadline')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedInteger('diskon')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedInteger('total_keb_kain')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('status_pembayaran_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('status_id')->references('id')->on('kategori')->onDelete('cascade'); // batal, proses, selesai
            $table->foreign('status_pembayaran_id')->references('id')->on('kategori')->onDelete('cascade'); // dp awal, dp produksi, lunas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};

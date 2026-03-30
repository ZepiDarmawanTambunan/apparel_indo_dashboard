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
            $table->date('tgl_deadline')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('sub_total')->default(0);
            $table->unsignedInteger('lainnya')->default(0);
            $table->unsignedInteger('diskon')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('total_pembayaran')->default(0);
            $table->unsignedBigInteger('total_keb_kain')->default(0);
            $table->unsignedBigInteger('status_id'); // batal, proses, selesai
            $table->unsignedBigInteger('status_pembayaran_id'); // dp awal, dp produksi, lunas
            $table->string('user_nama')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('status_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->foreign('status_pembayaran_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
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

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
        Schema::create('stok_produk', function (Blueprint $table) {
            $table->id();
            $table->string('produk_id');
            $table->unsignedInteger('stok_before');
            $table->unsignedInteger('stok_after');
            $table->unsignedBigInteger('kategori_id'); //brg masuk, brg keluar
            $table->text('keterangan')->nullable();
            $table->dateTime('tgl_jam');
            $table->string('user_nama')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();

            $table->foreign('produk_id')->references('id_produk')->on('produk')->onDelete('cascade');
            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_produk');
    }
};

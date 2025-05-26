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
        Schema::create('produk', function (Blueprint $table) {
            $table->string('id_produk', 6)->primary();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('harga')->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('satuan_id');
            $table->date('tgl');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->foreign('satuan_id')->references('id')->on('satuan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};

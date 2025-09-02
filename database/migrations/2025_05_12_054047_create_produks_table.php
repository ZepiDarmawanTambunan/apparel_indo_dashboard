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
            $table->string('id_produk', 8)->primary();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('harga')->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('satuan_id')->nullable();
            $table->string('parent_id', 8)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id_produk')->on('produk')->onDelete('set null');
            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('set null');
            $table->foreign('satuan_id')->references('id')->on('satuan')->onDelete('set null');
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

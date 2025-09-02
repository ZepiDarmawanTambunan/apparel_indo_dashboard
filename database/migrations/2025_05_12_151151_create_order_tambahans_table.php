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
        Schema::create('order_tambahan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_detail_id');
            $table->string('produk_id')->nullable();
            $table->string('nama');
            $table->string('kategori');
            $table->string('satuan');
            $table->unsignedInteger('qty')->default(0);
            $table->unsignedInteger('harga')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('user_nama')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_detail_id')->references('id')->on('order_detail')->onDelete('cascade');
            $table->foreign('produk_id')->references('id_produk')->on('produk')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tambahan');
    }
};

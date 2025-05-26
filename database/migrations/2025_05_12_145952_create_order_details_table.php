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
        Schema::create('order_detail', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('produk_id')->nullable();
            $table->string('nama');
            $table->string('kategori');
            $table->string('satuan');
            $table->unsignedInteger('qty')->default(0);
            $table->unsignedInteger('harga')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('order_id')->references('id_order')->on('order')->onDelete('cascade');
            $table->foreign('produk_id')->references('id_produk')->on('produk')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail');
    }
};

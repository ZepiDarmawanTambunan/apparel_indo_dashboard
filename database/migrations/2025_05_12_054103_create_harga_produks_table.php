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
        Schema::create('harga_produk', function (Blueprint $table) {
            $table->id();
            $table->string('produk_id');
            $table->unsignedInteger('harga_before');
            $table->unsignedInteger('harga_after');
            $table->date('tgl');
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('produk_id')->references('id_produk')->on('produk')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_produk');
    }
};

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
        Schema::create('invoice_tambahan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_detail_id');
            $table->string('nama');
            $table->string('kategori');
            $table->string('satuan');
            $table->unsignedInteger('qty')->default(0);
            $table->unsignedInteger('harga')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('user_nama')->nullable();
            $table->timestamps();

            $table->foreign('invoice_detail_id')->references('id')->on('invoice_detail')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_tambahan');
    }
};

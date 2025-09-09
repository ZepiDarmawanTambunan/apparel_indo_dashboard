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
        Schema::create('riwayat_jahit', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('jahit_id');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_nama')->nullable();
            $table->unsignedBigInteger('jumlah_dikerjakan')->default(0);
            $table->string('produk_id')->nullable();
            $table->string('produk_nama')->nullable();
            $table->unsignedInteger('salary')->default(0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('produk_id')->references('id')->on('produk')->onDelete('set null');
            $table->foreign('jahit_id')->references('id')->on('jahit')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_jahit');
    }
};

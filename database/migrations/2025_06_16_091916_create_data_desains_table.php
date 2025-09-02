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
        Schema::create('data_desain', function (Blueprint $table) {
            $table->id();

            $table->dateTime('tgl_terima')->nullable();
            $table->dateTime('tgl_selesai')->nullable();
            $table->dateTime('tgl_batal')->nullable();
            $table->unsignedBigInteger('jumlah_dikerjakan')->default(0);

            $table->string('order_id');
            $table->unsignedBigInteger('status_id');
            $table->string('user_nama')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('order_id')->references('id_order')->on('order')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('kategori')->onDelete('cascade'); // batal, pending, terima

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_desains');
    }
};

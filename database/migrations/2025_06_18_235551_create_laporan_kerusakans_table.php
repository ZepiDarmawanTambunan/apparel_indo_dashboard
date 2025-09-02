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
        Schema::create('laporan_kerusakan', function (Blueprint $table) {
            $table->id();

            $table->dateTime('tgl_selesai')->nullable();
            $table->dateTime('tgl_batal')->nullable();

            $table->string('order_id');
            $table->string('divisi_pelapor');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('status_checking_id');
            $table->string('pelapor_nama')->nullable();
            $table->unsignedBigInteger('pelapor_id')->nullable();

            $table->unsignedInteger('jumlah_rusak')->default(0);
            $table->string('divisi_bertanggung_jawab')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('keterangan_checking')->nullable();
            $table->boolean('is_human_error')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id_order')->on('order')->onDelete('cascade');
            $table->foreign('pelapor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('kategori')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kerusakan');
    }
};

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
        Schema::create('riwayat_data_desain', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_desain_id')->nullable();

            $table->string('accepted_by_name')->nullable();
            $table->unsignedBigInteger('accepted_by_id')->nullable();
            $table->unsignedBigInteger('updated_by_name')->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable();

            $table->dateTime('tgl_feedback')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('feedback')->nullable();

            $table->foreign('data_desain_id')->references('id')->on('data_desain')->onDelete('cascade');
            $table->foreign('accepted_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_id')->references('id')->on('users')->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_data_desain');
    }
};

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
        // Hapus index lama dulu (model_type + model_id)
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['model_type', 'model_id']);
        });

        // Ubah kolom model_id menjadi varchar(36)
        Schema::table('media', function (Blueprint $table) {
            $table->string('model_id', 36)->change();
        });

        // Buat ulang index untuk kolom (model_type, model_id)
        Schema::table('media', function (Blueprint $table) {
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['model_type', 'model_id']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->index(['model_type', 'model_id']);
        });
    }
};

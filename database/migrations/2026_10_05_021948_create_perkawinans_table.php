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
        Schema::create('perkawinans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betina_id')->constrained('kambings')->cascadeOnDelete();
            $table->foreignId('pejantan_id')->constrained('kambings')->cascadeOnDelete();
            $table->date('tanggal_kawin');
            $table->enum('status', ['menunggu', 'berhasil', 'tidak_berhasil'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perkawinans');
    }
};

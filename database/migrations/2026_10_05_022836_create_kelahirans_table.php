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
        Schema::create('kelahirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perkawinan_id')->nullable()->constrained('perkawinans')->nullOnDelete();
            $table->foreignId('induk_id')->constrained('kambings')->cascadeOnDelete();
            $table->foreignId('pejantan_id')->constrained('kambings')->cascadeOnDelete();
            $table->date('tanggal_kelahiran');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelahirans');
    }
};

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
        Schema::create('pengingat_perawatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kambing_id')->constrained('kambings')->cascadeOnDelete();
            $table->string('jenis_perawatan');
            $table->date('tanggal');
            $table->date('tanggal_pemberian')->nullable();
            $table->enum('status', ['belum_diberikan', 'sudah_diberikan'])->default('belum_diberikan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengingat_perawatans');
    }
};

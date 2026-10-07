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
        Schema::table('kambings', function (Blueprint $table) {
            $table->foreignId('kelahiran_id')->nullable()->after('id')->constrained('kelahirans')->nullOnDelete();
            $table->foreignId('induk_id')->nullable()->after('kelahiran_id')->constrained('kambings')->nullOnDelete();
            $table->foreignId('pejantan_id')->nullable()->after('induk_id')->constrained('kambings')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kambings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelahiran_id');
            $table->dropConstrainedForeignId('induk_id');
            $table->dropConstrainedForeignId('pejantan_id');
        });
    }
};

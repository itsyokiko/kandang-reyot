<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('kelahirans')) {
            try {
                DB::statement('ALTER TABLE kelahirans MODIFY pejantan_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                Schema::table('kelahirans', function (Blueprint $table) {
                    $table->unsignedBigInteger('pejantan_id')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kelahirans')) {
            try {
                DB::statement('ALTER TABLE kelahirans MODIFY pejantan_id BIGINT UNSIGNED NOT NULL');
            } catch (\Throwable $e) {
                // Ignore rollback failure if records with null exist
            }
        }
    }
};

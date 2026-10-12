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
        Schema::table('partial_leave_requests', function (Blueprint $table) {
            $table->string('signature_path_unit')->nullable()->after('approved_by_head_id');
            $table->string('signature_path_sdm')->nullable()->after('approved_by_hr_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partial_leave_requests', function (Blueprint $table) {
            // Ini akan menghapus kolom jika migrasi di-rollback
            $table->dropColumn(['signature_path_unit', 'signature_path_sdm']);
        });
    }
};
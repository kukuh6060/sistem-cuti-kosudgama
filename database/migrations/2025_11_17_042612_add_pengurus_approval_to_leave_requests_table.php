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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('approved_by_pengurus_id')->nullable()->constrained('users')->after('approved_by_hr_id');
            $table->string('signature_path_pengurus')->nullable()->after('signature_path_sdm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Drop foreign key terlebih dahulu sebelum drop kolom
            $table->dropForeign(['approved_by_pengurus_id']);
            
            // Hapus kedua kolom
            $table->dropColumn(['approved_by_pengurus_id', 'signature_path_pengurus']);
        });
    }
};
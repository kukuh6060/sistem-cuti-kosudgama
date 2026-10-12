<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('leave_quotas', function (Blueprint $table) {
            $table->unsignedInteger('total_izin')
                ->default(3)
                ->after('used_quota');

            $table->unsignedInteger('used_izin')
                ->default(0)
                ->after('total_izin');
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::table('leave_quotas', function (Blueprint $table) {
            $table->dropColumn([
                'total_izin',
                'used_izin',
            ]);
        });
    }
};
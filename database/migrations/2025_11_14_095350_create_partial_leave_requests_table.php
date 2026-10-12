<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partial_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('leave_date'); // Tanggal izin
            $table->time('start_time'); // Jam mulai
            $table->time('end_time');   // Jam selesai
            
            // Untuk keperluan (dari checkbox)
            $table->string('purpose_category');
            $table->text('purpose_other')->nullable(); // Jika memilih "keperluan pribadi lain"

            $table->string('status')->default('pending');
            $table->string('signature_path')->nullable(); // Path TTD

            // Kolom untuk approval (disiapkan untuk nanti)
            $table->foreignId('approved_by_head_id')->nullable()->constrained('users');
            $table->foreignId('approved_by_hr_id')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partial_leave_requests');
    }
};
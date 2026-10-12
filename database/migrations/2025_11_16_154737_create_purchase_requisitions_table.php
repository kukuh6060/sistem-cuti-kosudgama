<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Pemohon
            $table->string('requisition_number')->nullable(); // Diisi oleh SDM
            $table->date('requisition_date'); // Tanggal pengajuan
            $table->string('status')->default('pending_unit'); // Alur: pending_unit -> pending_sdm -> pending_pengurus -> approved -> rejected
            $table->string('signature_path')->nullable(); // TTD Pemohon

            // Tracking Approval
            $table->foreignId('approved_by_unit_id')->nullable()->constrained('users');
            $table->foreignId('approved_by_sdm_id')->nullable()->constrained('users');
            $table->foreignId('approved_by_pengurus_id')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};
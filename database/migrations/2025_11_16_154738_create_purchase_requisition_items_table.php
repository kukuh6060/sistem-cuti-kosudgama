<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')->constrained()->onDelete('cascade');
            $table->string('nama_barang');
            $table->integer('jumlah_barang');
            $table->decimal('perkiraan_harga', 15, 2)->nullable();
            $table->decimal('harga', 15, 2)->nullable(); // Mungkin "Harga Total"?
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_items');
    }
};
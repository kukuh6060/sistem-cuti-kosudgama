<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absentee_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year');

            // 1. Tidak masuk bekerja dengan alasan
            $table->integer('sakit')->default(0);
            $table->integer('tanpa_kabar')->default(0);
            $table->integer('ijin_meninggalkan_tempat')->default(0);

            // 2. Masuk bekerja tidak penuh
            $table->integer('terlambat')->default(0);
            $table->integer('setengah_hari')->default(0); // 1/2 hari kerja
            $table->integer('pulang_awal')->default(0);
            $table->integer('ijin_lebih_1_jam')->default(0);
            $table->integer('ijin_kurang_1_jam')->default(0);
            
            // 3. Sisa Cuti (ini akan kita sinkronkan)
            // Kita tetap simpan di sini untuk arsip/log
            $table->integer('sisa_cuti_input')->default(0);
            $table->integer('sisa_ijin_input')->default(0);

            $table->timestamps();
            
            // Hanya boleh ada 1 record per karyawan per tahun
            $table->unique(['user_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absentee_records');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->unsignedTinyInteger('bulan'); // 1-12
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('total_hadir')->default(0);
            $table->unsignedInteger('total_izin')->default(0);
            $table->unsignedInteger('total_sakit')->default(0);
            $table->unsignedInteger('total_alpha')->default(0);
            $table->decimal('persentase_hadir', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['siswa_id', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_kehadiran');
    }
};
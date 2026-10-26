<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: 2026/2027
            $table->unsignedSmallInteger('tahun_mulai')->unique(); // contoh: 2026
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedTinyInteger('tingkat')->default(7)->after('nama_kelas');
            $table->foreignId('tahun_ajaran_id')->nullable()->after('tingkat')
                ->constrained('tahun_ajaran')->nullOnDelete();
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'lulus'])->default('aktif')->after('jenis_kelamin');
        });

        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->timestamps();

            // satu siswa cuma punya satu kelas per tahun ajaran
            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });

        // ===== Data awal: semua data lama dimasukkan ke tahun ajaran 2026/2027 =====
        $taId = DB::table('tahun_ajaran')->insertGetId([
            'nama' => '2026/2027',
            'tahun_mulai' => 2026,
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kelas')->update(['tahun_ajaran_id' => $taId]);

        // tingkat diambil dari angka pertama nama kelas (7A jadi 7, 8B jadi 8)
        DB::statement("UPDATE kelas SET tingkat = CAST(LEFT(nama_kelas, 1) AS UNSIGNED) WHERE LEFT(nama_kelas, 1) IN ('7', '8', '9')");

        // riwayat kelas awal dari kelas siswa saat ini
        DB::insert(
            'INSERT INTO riwayat_kelas (siswa_id, kelas_id, tahun_ajaran_id, created_at, updated_at)
             SELECT id, kelas_id, ?, NOW(), NOW() FROM siswa WHERE kelas_id IS NOT NULL',
            [$taId]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn(['tingkat', 'tahun_ajaran_id']);
        });

        Schema::dropIfExists('tahun_ajaran');
    }
};
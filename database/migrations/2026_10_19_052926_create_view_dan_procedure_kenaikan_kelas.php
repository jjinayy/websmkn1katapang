<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================
        // VIEW: rekap per semester
        // Semester 1 = Juli-Desember, Semester 2 = Januari-Juni
        // tahun_mulai = tahun awal tahun ajaran (2026 untuk 2026/2027)
        // ==========================
        DB::unprepared('
            CREATE OR REPLACE VIEW v_rekap_semester AS
            SELECT
                r.siswa_id,
                IF(r.bulan >= 7, r.tahun, r.tahun - 1) AS tahun_mulai,
                IF(r.bulan >= 7, 1, 2) AS semester,
                SUM(r.total_hadir) AS total_hadir,
                SUM(r.total_izin) AS total_izin,
                SUM(r.total_sakit) AS total_sakit,
                SUM(r.total_alpha) AS total_alpha,
                ROUND(
                    SUM(r.total_hadir) / NULLIF(SUM(r.total_hadir + r.total_izin + r.total_sakit + r.total_alpha), 0) * 100,
                    2
                ) AS persentase_hadir
            FROM rekap_kehadiran r
            GROUP BY
                r.siswa_id,
                IF(r.bulan >= 7, r.tahun, r.tahun - 1),
                IF(r.bulan >= 7, 1, 2)
        ');

        // ==========================
        // PROCEDURE: naikkan semua siswa aktif satu kelas ke kelas tujuan
        // (pakai transaction: kalau ada error, semuanya rollback)
        // ==========================
        DB::unprepared('DROP PROCEDURE IF EXISTS proses_kenaikan_kelas');
        DB::unprepared('
            CREATE PROCEDURE proses_kenaikan_kelas(IN p_kelas_asal BIGINT, IN p_kelas_tujuan BIGINT)
            BEGIN
                DECLARE v_ta_tujuan BIGINT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                SELECT tahun_ajaran_id INTO v_ta_tujuan FROM kelas WHERE id = p_kelas_tujuan;

                START TRANSACTION;

                INSERT INTO riwayat_kelas (siswa_id, kelas_id, tahun_ajaran_id, created_at, updated_at)
                SELECT id, p_kelas_tujuan, v_ta_tujuan, NOW(), NOW()
                FROM siswa
                WHERE kelas_id = p_kelas_asal AND status = "aktif";

                UPDATE siswa SET kelas_id = p_kelas_tujuan
                WHERE kelas_id = p_kelas_asal AND status = "aktif";

                COMMIT;
            END
        ');

        // ==========================
        // PROCEDURE: kelulusan kelas 9
        // ==========================
        DB::unprepared('DROP PROCEDURE IF EXISTS proses_kelulusan');
        DB::unprepared('
            CREATE PROCEDURE proses_kelulusan(IN p_kelas_asal BIGINT)
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                UPDATE siswa SET status = "lulus"
                WHERE kelas_id = p_kelas_asal AND status = "aktif";

                COMMIT;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_rekap_semester');
        DB::unprepared('DROP PROCEDURE IF EXISTS proses_kenaikan_kelas');
        DB::unprepared('DROP PROCEDURE IF EXISTS proses_kelulusan');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_absensi');
        DB::unprepared('
            CREATE TRIGGER trg_after_insert_absensi
            AFTER INSERT ON absensi
            FOR EACH ROW
            BEGIN
                DECLARE v_bulan TINYINT;
                DECLARE v_tahun SMALLINT;
                DECLARE v_exists INT;

                SET v_bulan = MONTH(NEW.tanggal);
                SET v_tahun = YEAR(NEW.tanggal);

                SELECT COUNT(*) INTO v_exists
                FROM rekap_kehadiran
                WHERE siswa_id = NEW.siswa_id AND bulan = v_bulan AND tahun = v_tahun;

                IF v_exists = 0 THEN
                    INSERT INTO rekap_kehadiran
                        (siswa_id, bulan, tahun, total_hadir, total_izin, total_sakit, total_alpha, persentase_hadir, created_at, updated_at)
                    VALUES
                        (NEW.siswa_id, v_bulan, v_tahun, 0, 0, 0, 0, 0, NOW(), NOW());
                END IF;

                IF NEW.status = "hadir" THEN
                    UPDATE rekap_kehadiran SET total_hadir = total_hadir + 1
                    WHERE siswa_id = NEW.siswa_id AND bulan = v_bulan AND tahun = v_tahun;
                ELSEIF NEW.status = "izin" THEN
                    UPDATE rekap_kehadiran SET total_izin = total_izin + 1
                    WHERE siswa_id = NEW.siswa_id AND bulan = v_bulan AND tahun = v_tahun;
                ELSEIF NEW.status = "sakit" THEN
                    UPDATE rekap_kehadiran SET total_sakit = total_sakit + 1
                    WHERE siswa_id = NEW.siswa_id AND bulan = v_bulan AND tahun = v_tahun;
                ELSEIF NEW.status = "alpha" THEN
                    UPDATE rekap_kehadiran SET total_alpha = total_alpha + 1
                    WHERE siswa_id = NEW.siswa_id AND bulan = v_bulan AND tahun = v_tahun;
                END IF;

                UPDATE rekap_kehadiran
                SET persentase_hadir = ROUND(
                    (total_hadir / (total_hadir + total_izin + total_sakit + total_alpha)) * 100, 2
                )
                WHERE siswa_id = NEW.siswa_id AND bulan = v_bulan AND tahun = v_tahun;
            END
        ');

        DB::unprepared('DROP FUNCTION IF EXISTS hitung_persentase_kehadiran');
        DB::unprepared('
            CREATE FUNCTION hitung_persentase_kehadiran(
                p_siswa_id BIGINT, p_bulan TINYINT, p_tahun SMALLINT
            )
            RETURNS DECIMAL(5,2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_persentase DECIMAL(5,2);

                SELECT persentase_hadir INTO v_persentase
                FROM rekap_kehadiran
                WHERE siswa_id = p_siswa_id AND bulan = p_bulan AND tahun = p_tahun
                LIMIT 1;

                RETURN IFNULL(v_persentase, 0);
            END
        ');

        DB::unprepared('DROP PROCEDURE IF EXISTS catat_absen_massal');
        DB::unprepared('
            CREATE PROCEDURE catat_absen_massal(IN p_data JSON)
            BEGIN
                DECLARE v_index INT DEFAULT 0;
                DECLARE v_total INT;
                DECLARE v_siswa_id BIGINT;
                DECLARE v_guru_id BIGINT;
                DECLARE v_tanggal DATE;
                DECLARE v_status VARCHAR(10);
                DECLARE v_keterangan TEXT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                SET v_total = JSON_LENGTH(p_data);

                START TRANSACTION;

                WHILE v_index < v_total DO
                    SET v_siswa_id = JSON_UNQUOTE(JSON_EXTRACT(p_data, CONCAT("$[", v_index, "].siswa_id")));
                    SET v_guru_id = JSON_UNQUOTE(JSON_EXTRACT(p_data, CONCAT("$[", v_index, "].guru_id")));
                    SET v_tanggal = JSON_UNQUOTE(JSON_EXTRACT(p_data, CONCAT("$[", v_index, "].tanggal")));
                    SET v_status = JSON_UNQUOTE(JSON_EXTRACT(p_data, CONCAT("$[", v_index, "].status")));
                    SET v_keterangan = JSON_UNQUOTE(JSON_EXTRACT(p_data, CONCAT("$[", v_index, "].keterangan")));

                    INSERT INTO absensi (siswa_id, guru_id, tanggal, status, keterangan, created_at, updated_at)
                    VALUES (v_siswa_id, v_guru_id, v_tanggal, v_status, v_keterangan, NOW(), NOW());

                    SET v_index = v_index + 1;
                END WHILE;

                COMMIT;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_absensi');
        DB::unprepared('DROP FUNCTION IF EXISTS hitung_persentase_kehadiran');
        DB::unprepared('DROP PROCEDURE IF EXISTS catat_absen_massal');
    }
};
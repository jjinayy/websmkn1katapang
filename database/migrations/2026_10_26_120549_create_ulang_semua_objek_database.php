<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ===== TRIGGER: absen baru masuk =====
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

        // ===== TRIGGER: status absen diubah =====
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_update_absensi');
        DB::unprepared('
            CREATE TRIGGER trg_after_update_absensi
            AFTER UPDATE ON absensi
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    UPDATE rekap_kehadiran
                    SET total_hadir = GREATEST(CAST(total_hadir AS SIGNED) + (NEW.status = "hadir") - (OLD.status = "hadir"), 0),
                        total_izin = GREATEST(CAST(total_izin AS SIGNED) + (NEW.status = "izin") - (OLD.status = "izin"), 0),
                        total_sakit = GREATEST(CAST(total_sakit AS SIGNED) + (NEW.status = "sakit") - (OLD.status = "sakit"), 0),
                        total_alpha = GREATEST(CAST(total_alpha AS SIGNED) + (NEW.status = "alpha") - (OLD.status = "alpha"), 0)
                    WHERE siswa_id = NEW.siswa_id
                      AND bulan = MONTH(NEW.tanggal)
                      AND tahun = YEAR(NEW.tanggal);

                    UPDATE rekap_kehadiran
                    SET persentase_hadir = IFNULL(ROUND(
                        total_hadir / NULLIF(total_hadir + total_izin + total_sakit + total_alpha, 0) * 100, 2
                    ), 0)
                    WHERE siswa_id = NEW.siswa_id
                      AND bulan = MONTH(NEW.tanggal)
                      AND tahun = YEAR(NEW.tanggal);
                END IF;
            END
        ');

        // ===== TRIGGER: data absen dihapus =====
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_delete_absensi');
        DB::unprepared('
            CREATE TRIGGER trg_after_delete_absensi
            AFTER DELETE ON absensi
            FOR EACH ROW
            BEGIN
                UPDATE rekap_kehadiran
                SET total_hadir = GREATEST(CAST(total_hadir AS SIGNED) - (OLD.status = "hadir"), 0),
                    total_izin = GREATEST(CAST(total_izin AS SIGNED) - (OLD.status = "izin"), 0),
                    total_sakit = GREATEST(CAST(total_sakit AS SIGNED) - (OLD.status = "sakit"), 0),
                    total_alpha = GREATEST(CAST(total_alpha AS SIGNED) - (OLD.status = "alpha"), 0)
                WHERE siswa_id = OLD.siswa_id
                  AND bulan = MONTH(OLD.tanggal)
                  AND tahun = YEAR(OLD.tanggal);

                UPDATE rekap_kehadiran
                SET persentase_hadir = IFNULL(ROUND(
                    total_hadir / NULLIF(total_hadir + total_izin + total_sakit + total_alpha, 0) * 100, 2
                ), 0)
                WHERE siswa_id = OLD.siswa_id
                  AND bulan = MONTH(OLD.tanggal)
                  AND tahun = YEAR(OLD.tanggal);
            END
        ');

        // ===== FUNCTION: persentase kehadiran =====
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

        // ===== PROCEDURE: absen massal (commit dan rollback) =====
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

        // ===== VIEW: rekap per semester =====
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

        // ===== PROCEDURE: kenaikan kelas =====
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

        // ===== PROCEDURE: kelulusan =====
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
        // dibiarkan kosong supaya rollback migration tidak menghapus objek database
    }
};
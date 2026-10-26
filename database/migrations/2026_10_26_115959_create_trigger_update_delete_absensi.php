<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Trigger saat status absen diubah: kurangi hitungan status lama, tambah status baru
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

        // Trigger saat data absen dihapus: kurangi hitungan di rekap
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
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_update_absensi');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_delete_absensi');
    }
};
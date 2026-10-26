<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapKehadiran extends Model
{
    protected $table = 'rekap_kehadiran';

    protected $fillable = [
        'siswa_id', 'bulan', 'tahun',
        'total_hadir', 'total_izin', 'total_sakit', 'total_alpha', 'persentase_hadir'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
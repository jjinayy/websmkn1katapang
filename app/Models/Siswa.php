<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = ['nisn', 'nama', 'kelas_id', 'jenis_kelamin', 'status'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }

    public function rekapKehadiran()
    {
        return $this->hasMany(RekapKehadiran::class, 'siswa_id');
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatKelas::class, 'siswa_id');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = Siswa::where('status', 'aktif')->count();
        $totalKelas = Kelas::where('tahun_ajaran_id', \App\Models\TahunAjaran::aktif()?->id ?? 0)->count();

        $absenHariIni = Absensi::whereDate('tanggal', today())->get();

        $hadir = $absenHariIni->where('status', 'hadir')->count();
        $izin = $absenHariIni->where('status', 'izin')->count();
        $sakit = $absenHariIni->where('status', 'sakit')->count();
        $alpha = $absenHariIni->where('status', 'alpha')->count();

        return view('dashboard', compact('totalSiswa', 'totalKelas', 'hadir', 'izin', 'sakit', 'alpha'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\RekapKehadiran;
use App\Models\Kelas;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $kelasId = $request->kelas_id;

        $query = RekapKehadiran::with('siswa.kelas')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId));
        }

        $rekap = $query->get();
        $kelas = Kelas::all();

        return view('rekap.index', compact('rekap', 'kelas', 'bulan', 'tahun', 'kelasId'));
    }
}
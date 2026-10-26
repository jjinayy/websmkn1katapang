<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RekapKehadiran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);
        $kelasId = $request->query('kelas_id');

        $query = RekapKehadiran::with('siswa.kelas')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId));
        }

        return response()->json($query->get());
    }

    public function persentase(Request $request, Siswa $siswa)
    {
        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);

        $hasil = DB::selectOne(
            'SELECT hitung_persentase_kehadiran(?, ?, ?) AS persentase',
            [$siswa->id, $bulan, $tahun]
        );

        return response()->json([
            'siswa' => $siswa->nama,
            'bulan' => (int) $bulan,
            'tahun' => (int) $tahun,
            'persentase' => (float) $hasil->persentase,
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapSemesterController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaran = TahunAjaran::orderByDesc('tahun_mulai')->get();
        $taId = $request->tahun_ajaran_id ?? TahunAjaran::aktif()?->id;
        $ta = $tahunAjaran->firstWhere('id', $taId);
        $semester = (int) ($request->semester ?: (now()->month >= 7 ? 1 : 2));
        $kelasId = $request->kelas_id;

        $kelas = collect();
        $data = collect();

        if ($ta) {
            $kelas = Kelas::where('tahun_ajaran_id', $ta->id)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();

            $data = DB::table('riwayat_kelas as r')
                ->join('siswa as s', 's.id', '=', 'r.siswa_id')
                ->join('kelas as k', 'k.id', '=', 'r.kelas_id')
                ->leftJoin('v_rekap_semester as v', function ($join) use ($ta, $semester) {
                    $join->on('v.siswa_id', '=', 'r.siswa_id')
                        ->where('v.tahun_mulai', '=', $ta->tahun_mulai)
                        ->where('v.semester', '=', $semester);
                })
                ->where('r.tahun_ajaran_id', $ta->id)
                ->when($kelasId, fn($q) => $q->where('r.kelas_id', $kelasId))
                ->orderBy('k.tingkat')
                ->orderBy('k.nama_kelas')
                ->orderBy('s.nama')
                ->select(
                    's.id as siswa_id',
                    's.nisn',
                    's.nama',
                    'k.nama_kelas',
                    'k.tingkat',
                    DB::raw('COALESCE(v.total_hadir, 0) as total_hadir'),
                    DB::raw('COALESCE(v.total_izin, 0) as total_izin'),
                    DB::raw('COALESCE(v.total_sakit, 0) as total_sakit'),
                    DB::raw('COALESCE(v.total_alpha, 0) as total_alpha'),
                    'v.persentase_hadir'
                )
                ->get()
                ->groupBy('nama_kelas');
        }

        return view('rekap_semester.index', compact('tahunAjaran', 'ta', 'semester', 'kelas', 'kelasId', 'data'));
    }

    public function siswa(Siswa $siswa)
    {
        $riwayat = RiwayatKelas::with(['kelas', 'tahunAjaran'])
            ->where('siswa_id', $siswa->id)
            ->get()
            ->sortBy(fn($r) => $r->tahunAjaran->tahun_mulai)
            ->values();

        $rekap = DB::table('v_rekap_semester')
            ->where('siswa_id', $siswa->id)
            ->get()
            ->keyBy(fn($r) => $r->tahun_mulai . '-' . $r->semester);

        return view('rekap_semester.siswa', compact('siswa', 'riwayat', 'rekap'));
    }
}
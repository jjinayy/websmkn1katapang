<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KenaikanKelasController extends Controller
{
    public function index()
    {
        $aktif = TahunAjaran::aktif();

        $sebelumnya = $aktif
            ? TahunAjaran::where('tahun_mulai', '<', $aktif->tahun_mulai)->orderByDesc('tahun_mulai')->first()
            : null;

        $kelasAsal = collect();
        $kelasTujuan = collect();

        if ($aktif && $sebelumnya) {
            $kelasAsal = Kelas::where('tahun_ajaran_id', $sebelumnya->id)
                ->withCount(['siswa as siswa_aktif_count' => fn($q) => $q->where('status', 'aktif')])
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();

            $kelasTujuan = Kelas::where('tahun_ajaran_id', $aktif->id)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        }

        return view('kenaikan_kelas.index', compact('aktif', 'sebelumnya', 'kelasAsal', 'kelasTujuan'));
    }

    public function naikkan(Request $request)
    {
        $request->validate([
            'kelas_asal_id' => 'required|exists:kelas,id',
            'kelas_tujuan_id' => 'required|exists:kelas,id',
        ]);

        $asal = Kelas::findOrFail($request->kelas_asal_id);
        $tujuan = Kelas::findOrFail($request->kelas_tujuan_id);
        $aktif = TahunAjaran::aktif();

        if (!$aktif || $tujuan->tahun_ajaran_id != $aktif->id || $asal->tahun_ajaran_id == $aktif->id) {
            return back()->with('error', 'Kelas asal harus dari tahun ajaran lama dan kelas tujuan dari tahun ajaran aktif.');
        }

        if ($tujuan->tingkat != $asal->tingkat + 1) {
            return back()->with('error', 'Kelas tujuan harus satu tingkat di atas kelas asal.');
        }

        $jumlah = Siswa::where('kelas_id', $asal->id)->where('status', 'aktif')->count();

        try {
            DB::statement('CALL proses_kenaikan_kelas(?, ?)', [$asal->id, $tujuan->id]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Kenaikan kelas gagal dan dibatalkan (rollback). ' . $e->getMessage());
        }

        return back()->with('success', "{$jumlah} murid kelas {$asal->nama_kelas} naik ke kelas {$tujuan->nama_kelas}.");
    }

    public function luluskan(Request $request)
    {
        $request->validate([
            'kelas_asal_id' => 'required|exists:kelas,id',
        ]);

        $asal = Kelas::findOrFail($request->kelas_asal_id);
        $aktif = TahunAjaran::aktif();

        if ($asal->tingkat != 9 || !$aktif || $asal->tahun_ajaran_id == $aktif->id) {
            return back()->with('error', 'Hanya kelas 9 dari tahun ajaran lama yang bisa diluluskan.');
        }

        $jumlah = Siswa::where('kelas_id', $asal->id)->where('status', 'aktif')->count();

        try {
            DB::statement('CALL proses_kelulusan(?)', [$asal->id]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Kelulusan gagal dan dibatalkan (rollback). ' . $e->getMessage());
        }

        return back()->with('success', "{$jumlah} murid kelas {$asal->nama_kelas} ditandai lulus.");
    }
}
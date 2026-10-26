<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index()
    {
            $tahunAjaran = \App\Models\TahunAjaran::aktif();
            $kelas = Kelas::where('tahun_ajaran_id', $tahunAjaran?->id ?? 0)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();
        return view('absensi.index', compact('kelas'));
    }

    public function form(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();
        $absensiTersimpan = Absensi::where('tanggal', $request->tanggal)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        return view('absensi.form', [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'tanggal' => $request->tanggal,
            'absensiTersimpan' => $absensiTersimpan,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'status' => 'required|array',
        ]);

        $guruId = $request->user()->id;
        $tanggal = $request->tanggal;

        $data = [];
        foreach ($request->status as $siswaId => $status) {
            $data[] = [
                'siswa_id' => (int) $siswaId,
                'guru_id' => $guruId,
                'tanggal' => $tanggal,
                'status' => $status,
                'keterangan' => $request->keterangan[$siswaId] ?? null,
            ];
        }

        try {
            DB::transaction(function () use ($data, $tanggal) {
                foreach ($data as $row) {
                    Absensi::updateOrCreate(
                        ['siswa_id' => $row['siswa_id'], 'tanggal' => $tanggal],
                        $row
                    );
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }

        return redirect()->route('absensi.index')->with('success', 'Absensi berhasil disimpan.');
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
        ]);

        $siswa = Siswa::where('kelas_id', $request->kelas_id)->orderBy('nama')->get();

        $tersimpan = Absensi::where('tanggal', $request->tanggal)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $hasil = $siswa->map(fn($s) => [
            'siswa_id' => $s->id,
            'nisn' => $s->nisn,
            'nama' => $s->nama,
            'status' => $tersimpan[$s->id]->status ?? null,
            'keterangan' => $tersimpan[$s->id]->keterangan ?? null,
        ]);

        return response()->json($hasil);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'data' => 'required|array|min:1',
            'data.*.siswa_id' => 'required|exists:siswa,id',
            'data.*.status' => 'required|in:hadir,izin,sakit,alpha',
            'data.*.keterangan' => 'nullable|string',
        ]);

            $guruId = $request->user()->id;

        $rows = collect($request->data)->map(fn($d) => [
            'siswa_id' => (int) $d['siswa_id'],
            'guru_id' => $guruId,
            'tanggal' => $request->tanggal,
            'status' => $d['status'],
            'keterangan' => $d['keterangan'] ?? null,
        ])->values()->all();

        try {
            DB::statement('CALL catat_absen_massal(?)', [json_encode($rows)]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal menyimpan absensi. Semua data dibatalkan (rollback). Kemungkinan sebagian siswa sudah diabsen di tanggal ini.',
                'detail' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['message' => 'Absensi berhasil disimpan.'], 201);
    }
}
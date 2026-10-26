<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::withCount('siswa')->orderBy('nama_kelas')->get();

        return response()->json($kelas);
    }

    public function siswa(Kelas $kelas)
    {
        $siswa = $kelas->siswa()
            ->orderBy('nama')
            ->get(['id', 'nisn', 'nama', 'jenis_kelamin']);

        return response()->json($siswa);
    }
}
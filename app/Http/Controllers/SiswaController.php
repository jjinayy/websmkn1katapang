<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    private function kelasAktif()
    {
        $ta = TahunAjaran::aktif();

        return Kelas::where('tahun_ajaran_id', $ta?->id ?? 0)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();
    }

    public function index()
    {
        $tahunAjaran = TahunAjaran::aktif();

        $kelas = Kelas::where('tahun_ajaran_id', $tahunAjaran?->id ?? 0)
            ->with(['siswa' => fn($q) => $q->where('status', 'aktif')->orderBy('nama')])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('siswa.index', compact('kelas', 'tahunAjaran'));
    }

    public function create()
    {
        $kelas = $this->kelasAktif();

        return view('siswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|unique:siswa,nisn',
            'nama' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);

        DB::transaction(function () use ($request, $kelas) {
            $siswa = Siswa::create($request->only('nisn', 'nama', 'kelas_id', 'jenis_kelamin'));

            RiwayatKelas::create([
                'siswa_id' => $siswa->id,
                'kelas_id' => $kelas->id,
                'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
            ]);
        });

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $kelas = $this->kelasAktif();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nisn' => 'required|string|unique:siswa,nisn,' . $siswa->id,
            'nama' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);

        DB::transaction(function () use ($request, $siswa, $kelas) {
            $siswa->update($request->only('nisn', 'nama', 'kelas_id', 'jenis_kelamin'));

            RiwayatKelas::updateOrCreate(
                ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $kelas->tahun_ajaran_id],
                ['kelas_id' => $kelas->id]
            );
        });

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }

    public function nisnSuggestions(Request $request)
    {
        $prefix = preg_replace('/[^0-9]/', '', $request->query('prefix', ''));
        $tahun = date('Y');
        $base = $prefix !== '' ? $prefix : $tahun;

        $suggestions = [];
        $i = 1;
        while (count($suggestions) < 5 && $i <= 999) {
            $candidate = $base . str_pad($i, 10 - strlen($base), '0', STR_PAD_LEFT);
            if (strlen($candidate) === 10 && !Siswa::where('nisn', $candidate)->exists()) {
                $suggestions[] = $candidate;
            }
            $i++;
        }

        return response()->json($suggestions);
    }
}
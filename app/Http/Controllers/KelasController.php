<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaran = TahunAjaran::orderByDesc('tahun_mulai')->get();
        $aktif = TahunAjaran::aktif();
        $taId = $request->tahun_ajaran_id ?? $aktif?->id;

        $kelas = Kelas::withCount('riwayat')
            ->where('tahun_ajaran_id', $taId)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('kelas.index', compact('kelas', 'tahunAjaran', 'taId'));
    }

    public function create()
    {
        $tahunAjaran = TahunAjaran::aktif();

        return view('kelas.create', compact('tahunAjaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|in:7,8,9',
            'wali_kelas' => 'nullable|string|max:255',
        ]);

        $aktif = TahunAjaran::aktif();

        if (!$aktif) {
            return back()->with('error', 'Belum ada tahun ajaran aktif.');
        }

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'tingkat' => $request->tingkat,
            'wali_kelas' => $request->wali_kelas,
            'tahun_ajaran_id' => $aktif->id,
        ]);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        return view('kelas.edit', ['kelas' => $kela]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|in:7,8,9',
            'wali_kelas' => 'nullable|string|max:255',
        ]);

        $kela->update($request->only('nama_kelas', 'tingkat', 'wali_kelas'));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        if ($kela->riwayat()->exists() || $kela->siswa()->exists()) {
            return back()->with('error', 'Kelas tidak bisa dihapus karena sudah memiliki data siswa.');
        }

        $kela->delete();

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
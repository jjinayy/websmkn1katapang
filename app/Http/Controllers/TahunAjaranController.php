<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::withCount('kelas')->orderByDesc('tahun_mulai')->get();
        $tahunBerikutnya = ($tahunAjaran->max('tahun_mulai') ?? now()->year - 1) + 1;

        return view('tahun_ajaran.index', compact('tahunAjaran', 'tahunBerikutnya'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_mulai' => 'required|integer|min:2000|max:2100|unique:tahun_ajaran,tahun_mulai',
        ]);

        $tahunMulai = (int) $request->tahun_mulai;

        DB::transaction(function () use ($tahunMulai) {
            $sebelumnya = TahunAjaran::where('tahun_mulai', '<', $tahunMulai)
                ->orderByDesc('tahun_mulai')
                ->first();

            TahunAjaran::query()->update(['is_aktif' => false]);

            $baru = TahunAjaran::create([
                'nama' => $tahunMulai . '/' . ($tahunMulai + 1),
                'tahun_mulai' => $tahunMulai,
                'is_aktif' => true,
            ]);

            if ($sebelumnya) {
                $kelasLama = Kelas::where('tahun_ajaran_id', $sebelumnya->id)->get();
                $daftar = [];

                // 1. salin semua kelas lama (nama sama, untuk tampung murid baru dan tingkat yang sama)
                foreach ($kelasLama as $k) {
                    $daftar[$k->nama_kelas] = [
                        'nama_kelas' => $k->nama_kelas,
                        'tingkat' => $k->tingkat,
                        'wali_kelas' => $k->wali_kelas,
                    ];
                }

                // 2. bikin kelas tingkat berikutnya (7A jadi 8A, 8A jadi 9A) kalau belum ada
                foreach ($kelasLama as $k) {
                    if ($k->tingkat < 9 && str_starts_with($k->nama_kelas, (string) $k->tingkat)) {
                        $namaNaik = ($k->tingkat + 1) . substr($k->nama_kelas, 1);

                        if (!isset($daftar[$namaNaik])) {
                            $daftar[$namaNaik] = [
                                'nama_kelas' => $namaNaik,
                                'tingkat' => $k->tingkat + 1,
                                'wali_kelas' => null,
                            ];
                        }
                    }
                }

                foreach ($daftar as $data) {
                    Kelas::create($data + ['tahun_ajaran_id' => $baru->id]);
                }
            }
        });

        return redirect()->route('tahun-ajaran.index')
            ->with('success', 'Tahun ajaran baru dibuat dan dijadikan aktif. Kelas disalin, kelas tingkat berikutnya ikut dibuat otomatis.');
    }

    public function aktifkan(TahunAjaran $tahunAjaran)
    {
        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::query()->update(['is_aktif' => false]);
            $tahunAjaran->update(['is_aktif' => true]);
        });

        return back()->with('success', 'Tahun ajaran ' . $tahunAjaran->nama . ' dijadikan aktif.');
    }
}
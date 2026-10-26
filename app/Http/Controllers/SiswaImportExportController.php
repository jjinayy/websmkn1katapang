<?php

namespace App\Http\Controllers;

use App\Exports\SiswaExport;
use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaImportExportController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new SiswaExport, 'data-siswa.xlsx');
    }

    public function exportPdf()
    {
        $ta = TahunAjaran::aktif();

        if (!$ta) {
            return back()->with('error', 'Belum ada tahun ajaran aktif.');
        }

        $kelas = Kelas::where('tahun_ajaran_id', $ta->id)
            ->with(['siswa' => fn($q) => $q->where('status', 'aktif')->orderBy('nama')])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $pdf = Pdf::loadView('exports.siswa_pdf', [
            'kelas' => $kelas,
            'tahunAjaran' => $ta,
            'tanggal' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('data-siswa-' . str_replace('/', '-', $ta->nama) . '.pdf');
    }

    public function template()
    {
        return Excel::download(new SiswaTemplateExport, 'template-impor-siswa.xlsx');
    }

    public function importForm()
    {
        $tahunAjaran = TahunAjaran::aktif();

        $kelas = Kelas::where('tahun_ajaran_id', $tahunAjaran?->id ?? 0)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('siswa.import', compact('tahunAjaran', 'kelas'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new SiswaImport(
            $request->boolean('buat_kelas'),
            $request->boolean('lewati')
        );

        try {
            $import->proses($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'Impor gagal dan dibatalkan (rollback). ' . $e->getMessage());
        }

        if (!empty($import->errors)) {
            return back()->with('import_errors', $import->errors);
        }

        $rincian = collect($import->ringkasan)
            ->map(fn($jumlah, $kelas) => "{$kelas}: {$jumlah}")
            ->implode(', ');

        $pesan = "{$import->berhasil} murid berhasil diimpor ({$rincian}).";

        if ($import->kelasBaru > 0) {
            $pesan .= " {$import->kelasBaru} kelas baru dibuat otomatis.";
        }

        if (!empty($import->dilewati)) {
            return redirect()->route('siswa.import.form')
                ->with('success', $pesan)
                ->with('import_skipped', $import->dilewati);
        }

        return redirect()->route('siswa.index')->with('success', $pesan);
    }
}
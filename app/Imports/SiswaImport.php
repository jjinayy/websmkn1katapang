<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SiswaImport
{
    public array $errors = [];
    public array $dilewati = [];
    public int $berhasil = 0;
    public int $kelasBaru = 0;
    public array $ringkasan = [];

    public function __construct(
        private bool $buatKelas = false,
        private bool $lewati = false
    ) {
    }

    private function teks($nilai): string
    {
        if ($nilai === null) {
            return '';
        }

        if (is_int($nilai) || is_float($nilai)) {
            return number_format((float) $nilai, 0, '', '');
        }

        return trim((string) $nilai);
    }

    // Ambil angkanya saja, lalu lengkapi jadi 10 digit (angka nol di depan sering hilang di Excel)
    private function bersihkanNisn(string $nisn): string
    {
        $nisn = (string) preg_replace('/[^0-9]/', '', $nisn);

        if ($nisn !== '' && strlen($nisn) < 10) {
            $nisn = str_pad($nisn, 10, '0', STR_PAD_LEFT);
        }

        return $nisn;
    }

    // "7A", "kelas 7a", "VII A" -> "7A"
    private function deteksiKelas(string $teks): ?string
    {
        if ($teks === '') {
            return null;
        }

        if (!preg_match('/\b(VIII|VII|IX|[789])\s*[-.\s]?\s*([A-Z])\b/i', $teks, $m)) {
            return null;
        }

        $tingkat = ['VII' => '7', 'VIII' => '8', 'IX' => '9'][strtoupper($m[1])] ?? $m[1];

        return $tingkat . strtoupper($m[2]);
    }

    // Judul kelas: sel yang isinya cuma kode kelas, atau teks yang memuat kata "kelas"
    private function deteksiJudul(array $sel): ?string
    {
        foreach ($sel as $teks) {
            if ($teks === '') {
                continue;
            }

            $persis = preg_match('/^\s*(?:kelas\s*[:\-]?\s*)?(VIII|VII|IX|[789])\s*[-.\s]?\s*[A-Z]\s*$/i', $teks);
            $adaKata = stripos($teks, 'kelas') !== false;

            if ($persis || $adaKata) {
                $hasil = $this->deteksiKelas($teks);

                if ($hasil !== null) {
                    return $hasil;
                }
            }
        }

        return null;
    }

    // Cari posisi kolom dari baris judul kolom (Nama, NISN, Jenis Kelamin, Kelas, Wali Kelas)
    private function petaKolom(array $sel): ?array
    {
        $peta = ['nama' => null, 'nisn' => null, 'jk' => null, 'kelas' => null, 'wakel' => null];

        foreach ($sel as $i => $teks) {
            if ($teks === '') {
                continue;
            }

            if ($peta['nama'] === null && preg_match('/^nama(\s+(lengkap|siswa|murid|peserta\s*didik))?$/i', $teks)) {
                $peta['nama'] = $i;
            } elseif ($peta['nisn'] === null && preg_match('/nisn/i', $teks)) {
                $peta['nisn'] = $i;
            } elseif ($peta['jk'] === null && preg_match('/^(jenis\s*kelamin|jk|l\s*\/\s*p)/i', $teks)) {
                $peta['jk'] = $i;
            } elseif ($peta['kelas'] === null && preg_match('/^kelas$/i', $teks)) {
                $peta['kelas'] = $i;
            } elseif ($peta['wakel'] === null && preg_match('/^(nama\s*)?(wakel|wali\s*kelas)$/i', $teks)) {
                $peta['wakel'] = $i;
            }
        }

        return $peta['nama'] === null ? null : $peta;
    }

    // Kalau ada sheet yang punya kolom "Kelas" (misal sheet master), pakai sheet itu saja
    private function pilihSheet(array $sheets): array
    {
        $denganKelas = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet['rows'] as $row) {
                $peta = $this->petaKolom(array_map(fn($v) => $this->teks($v), $row));

                if ($peta !== null) {
                    if ($peta['kelas'] !== null) {
                        $denganKelas[] = $sheet;
                    }

                    break;
                }
            }
        }

        return !empty($denganKelas) ? $denganKelas : $sheets;
    }

    private function baca(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheets = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            try {
                $rows = $sheet->toArray(null, true, false, false);
            } catch (\Throwable $e) {
                $rows = $sheet->toArray(null, false, false, false);
            }

            $sheets[] = ['judul' => $sheet->getTitle(), 'rows' => $rows];
        }

        $hasil = [];

        foreach ($this->pilihSheet($sheets) as $sheet) {
            $judulSheet = $sheet['judul'];
            $kelasSekarang = $this->deteksiJudul([$judulSheet]);
            $kolom = null;

            foreach ($sheet['rows'] as $i => $row) {
                $baris = $i + 1;
                $sel = array_map(fn($v) => $this->teks($v), $row);
                $isi = count(array_filter($sel, fn($v) => $v !== ''));

                if ($isi === 0) {
                    continue;
                }

                // 1. baris judul kolom
                $petaBaru = $this->petaKolom($sel);
                if ($petaBaru !== null) {
                    $kolom = $petaBaru;
                    continue;
                }

                // 2. baris data murid
                if ($kolom !== null && ($sel[$kolom['nama']] ?? '') !== '') {
                    $nama = $sel[$kolom['nama']];

                    // nama kelas kadang ditaruh di kolom nama (sel gabungan)
                    $judulDariNama = $this->deteksiJudul([$nama]);
                    if ($judulDariNama !== null) {
                        $kelasSekarang = $judulDariNama;
                        continue;
                    }

                    // lewati baris ringkasan
                    if (preg_match('/^(jumlah|total|laki-laki|perempuan|keterangan)\b/i', $nama)) {
                        continue;
                    }

                    $kelasBaris = $kelasSekarang;

                    if ($kolom['kelas'] !== null && ($sel[$kolom['kelas']] ?? '') !== '') {
                        $kelasBaris = $this->deteksiKelas($sel[$kolom['kelas']]);

                        if ($kelasBaris === null) {
                            $this->errors[] = "Sheet '{$judulSheet}' baris {$baris}: kelas '{$sel[$kolom['kelas']]}' tidak dikenali.";
                            continue;
                        }
                    }

                    $jkMentah = strtoupper($kolom['jk'] !== null ? ($sel[$kolom['jk']] ?? '') : '');
                    $jk = str_starts_with($jkMentah, 'L') ? 'L' : (str_starts_with($jkMentah, 'P') ? 'P' : null);

                    $hasil[] = [
                        'sheet' => $judulSheet,
                        'baris' => $baris,
                        'nisn' => $this->bersihkanNisn($kolom['nisn'] !== null ? ($sel[$kolom['nisn']] ?? '') : ''),
                        'nama' => $nama,
                        'kelas' => $kelasBaris,
                        'jk' => $jk,
                        'wakel' => $kolom['wakel'] !== null ? ($sel[$kolom['wakel']] ?? '') : '',
                    ];

                    continue;
                }

                // 3. baris judul kelas (misal "KELAS 7A")
                $judul = $this->deteksiJudul($sel);
                if ($judul !== null) {
                    $kelasSekarang = $judul;
                }
            }
        }

        return $hasil;
    }

    public function proses(string $path): void
    {
        $aktif = TahunAjaran::aktif();

        if (!$aktif) {
            $this->errors[] = 'Belum ada tahun ajaran aktif.';
            return;
        }

        $data = $this->baca($path);

        if (empty($data) && empty($this->errors)) {
            $this->errors[] = 'Tidak ada data murid yang terbaca. Pastikan ada baris judul kolom (Nama, NISN, Jenis Kelamin) di atas daftar murid.';
            return;
        }

        $kelasMap = Kelas::where('tahun_ajaran_id', $aktif->id)
            ->get()
            ->keyBy(fn($k) => strtoupper(trim($k->nama_kelas)));

        $nisnTerpakai = Siswa::pluck('nisn')->flip();
        $prefix = (string) $aktif->tahun_mulai;
        $urutan = 1;

        $siap = [];
        $kelasBaru = [];
        $waliPerKelas = [];
        $kelasHilang = [];
        $tanpaKelas = [];

        foreach ($data as $d) {
            $lokasi = "Sheet '{$d['sheet']}' baris {$d['baris']} ({$d['nama']})";

            // --- masalah yang membatalkan semuanya ---
            if ($d['kelas'] === null) {
                $tanpaKelas[$d['sheet']] = ($tanpaKelas[$d['sheet']] ?? 0) + 1;
                continue;
            }

            if (!$kelasMap->has($d['kelas'])) {
                if ($this->buatKelas) {
                    $kelasBaru[$d['kelas']] = true;
                } else {
                    $kelasHilang[$d['kelas']] = ($kelasHilang[$d['kelas']] ?? 0) + 1;
                    continue;
                }
            }

            if ($d['wakel'] !== '' && !isset($waliPerKelas[$d['kelas']])) {
                $waliPerKelas[$d['kelas']] = $d['wakel'];
            }

            // --- masalah per baris ---
            $masalah = null;

            if ($d['jk'] === null) {
                $masalah = 'jenis kelamin harus L atau P';
            }

            $nisn = $d['nisn'];

            if ($nisn === '') {
                // NISN kosong: dibuatkan otomatis (tahun mulai + 6 digit urutan)
                do {
                    $nisn = $prefix . str_pad($urutan++, 6, '0', STR_PAD_LEFT);
                } while ($nisnTerpakai->has($nisn));
            } elseif ($nisnTerpakai->has($nisn)) {
                $masalah = "NISN {$nisn} sudah terdaftar (di sistem atau di baris lain pada file ini)";
            }

            if ($masalah !== null) {
                if ($this->lewati) {
                    $this->dilewati[] = "{$lokasi}: {$masalah}.";
                } else {
                    $this->errors[] = "{$lokasi}: {$masalah}.";
                }

                continue;
            }

            $nisnTerpakai->put($nisn, true);
            $siap[] = ['nisn' => $nisn, 'nama' => $d['nama'], 'kelas' => $d['kelas'], 'jk' => $d['jk']];
        }

        foreach ($tanpaKelas as $sheet => $jumlah) {
            $this->errors[] = "Sheet '{$sheet}': kelas tidak terdeteksi untuk {$jumlah} murid. Tulis 'Kelas 7A' di atas tabel, beri nama sheet 7A, atau tambah kolom Kelas.";
        }

        foreach ($kelasHilang as $namaKelas => $jumlah) {
            $this->errors[] = "Kelas {$namaKelas} ({$jumlah} murid) belum ada di tahun ajaran {$aktif->nama}. Tambahkan di menu Data Kelas, atau centang opsi buat kelas otomatis.";
        }

        if (count($this->errors) > 50) {
            $sisa = count($this->errors) - 50;
            $this->errors = array_slice($this->errors, 0, 50);
            $this->errors[] = "...dan {$sisa} masalah lainnya.";
        }

        if (count($this->dilewati) > 100) {
            $sisa = count($this->dilewati) - 100;
            $this->dilewati = array_slice($this->dilewati, 0, 100);
            $this->dilewati[] = "...dan {$sisa} baris lainnya.";
        }

        if (!empty($this->errors)) {
            return; // ada masalah: tidak ada data yang disimpan
        }

        if (empty($siap)) {
            $this->errors[] = 'Tidak ada murid yang bisa diimpor dari file ini.';
            return;
        }

        DB::transaction(function () use ($siap, $kelasMap, $kelasBaru, $waliPerKelas, $aktif) {
            // 1. buat kelas yang belum ada (kalau opsinya dicentang)
            foreach (array_keys($kelasBaru) as $kode) {
                $kelasMap->put($kode, Kelas::create([
                    'nama_kelas' => $kode,
                    'tingkat' => (int) substr($kode, 0, 1),
                    'wali_kelas' => $waliPerKelas[$kode] ?? null,
                    'tahun_ajaran_id' => $aktif->id,
                ]));

                $this->kelasBaru++;
            }

            // 2. isi wali kelas yang masih kosong dari data file
            foreach ($waliPerKelas as $kode => $wali) {
                $kelas = $kelasMap[$kode] ?? null;

                if ($kelas && empty($kelas->wali_kelas)) {
                    $kelas->update(['wali_kelas' => $wali]);
                }
            }

            // 3. simpan murid dan riwayat kelasnya
            foreach ($siap as $d) {
                $kelas = $kelasMap[$d['kelas']];

                $siswa = Siswa::create([
                    'nisn' => $d['nisn'],
                    'nama' => $d['nama'],
                    'kelas_id' => $kelas->id,
                    'jenis_kelamin' => $d['jk'],
                ]);

                RiwayatKelas::create([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $kelas->id,
                    'tahun_ajaran_id' => $aktif->id,
                ]);

                $this->ringkasan[$d['kelas']] = ($this->ringkasan[$d['kelas']] ?? 0) + 1;
            }
        });

        $this->berhasil = count($siap);
    }
}
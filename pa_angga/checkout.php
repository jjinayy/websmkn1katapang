<?php
/**
 * checkout.php
 * -----------------------------------------------------------
 * Dipanggil lewat fetch() dari fungsi handleCheckout() di index.php,
 * TEPAT SEBELUM pesanan dikirim ke WhatsApp / modal QRIS ditampilkan.
 * Menyimpan 1 baris ke tabel `pesanan` dan N baris ke `pesanan_detail`
 * (N = jumlah jenis barang di keranjang).
 * -----------------------------------------------------------
 */

require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi login sudah habis, silakan login ulang.']);
    exit;
}

$input       = json_decode(file_get_contents('php://input'), true) ?? [];
$namaPemesan = trim($input['nama'] ?? '');
$kelas       = trim($input['kelas'] ?? '');
$metode      = trim($input['metode'] ?? '');
$keranjang   = $input['keranjang'] ?? [];

if ($namaPemesan === '' || $kelas === '' || $metode === '' || !is_array($keranjang) || count($keranjang) === 0) {
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap.']);
    exit;
}

// Hitung ulang total di server (jangan percaya total dari client)
$produkIds = array_map(fn($item) => (int)($item['id'] ?? 0), $keranjang);
$placeholders = implode(',', array_fill(0, count($produkIds), '?'));
$stmtProduk = $pdo->prepare("SELECT id, nama, harga FROM produk WHERE id IN ($placeholders)");
$stmtProduk->execute($produkIds);
$produkById = [];
foreach ($stmtProduk->fetchAll() as $p) {
    $produkById[$p['id']] = $p;
}

$rincian = [];
$totalHarga = 0;

foreach ($keranjang as $item) {
    $id     = (int)($item['id'] ?? 0);
    $jumlah = max(1, (int)($item['jumlah'] ?? 1));

    if (!isset($produkById[$id])) {
        continue; // lewati produk yang tidak valid/tidak ditemukan
    }

    $produk   = $produkById[$id];
    $subtotal = $produk['harga'] * $jumlah;
    $totalHarga += $subtotal;

    $rincian[] = [
        'produk_id'    => $id,
        'nama_produk'  => $produk['nama'],
        'harga_satuan' => $produk['harga'],
        'jumlah'       => $jumlah,
        'subtotal'     => $subtotal,
    ];
}

if (empty($rincian)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang tidak valid.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmtPesanan = $pdo->prepare(
        'INSERT INTO pesanan (siswa_id, nama_pemesan, kelas, metode_bayar, total_harga)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmtPesanan->execute([
        $_SESSION['siswa_id'],
        $namaPemesan,
        $kelas,
        $metode,
        $totalHarga,
    ]);
    $pesananId = $pdo->lastInsertId();

    $stmtDetail = $pdo->prepare(
        'INSERT INTO pesanan_detail (pesanan_id, produk_id, nama_produk, harga_satuan, jumlah, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($rincian as $r) {
        $stmtDetail->execute([
            $pesananId,
            $r['produk_id'],
            $r['nama_produk'],
            $r['harga_satuan'],
            $r['jumlah'],
            $r['subtotal'],
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success'     => true,
        'message'     => 'Pesanan berhasil disimpan.',
        'pesanan_id'  => $pesananId,
        'total_harga' => $totalHarga,
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage()]);
}
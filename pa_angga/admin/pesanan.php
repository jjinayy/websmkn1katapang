<?php
/**
 * admin/pesanan.php
 * -----------------------------------------------------------
 * Halaman BONUS (tidak ada di desain 7 section utama) supaya
 * pihak koperasi/guru bisa melihat semua pesanan yang tersimpan
 * di database. Dilindungi password sederhana lewat session.
 * Ganti ADMIN_PASSWORD di bawah sesuai kebutuhan.
 * -----------------------------------------------------------
 */

require_once __DIR__ . '/../config.php';

const ADMIN_PASSWORD = 'admin123'; // TODO: ganti password ini

if (isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
    } else {
        $errorLogin = 'Password admin salah.';
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header('Location: pesanan.php');
    exit;
}

if (empty($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Login Admin - Katapang Store</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-950 min-h-screen flex items-center justify-center text-white">
        <form method="post" class="bg-slate-900 p-8 rounded-2xl w-full max-w-sm border border-slate-800">
            <h1 class="text-lg font-bold mb-4">Login Admin Koperasi</h1>
            <?php if (!empty($errorLogin)): ?>
                <p class="text-red-400 text-xs mb-3"><?= htmlspecialchars($errorLogin) ?></p>
            <?php endif; ?>
            <input type="password" name="admin_password" placeholder="Password admin" required
                   class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 mb-4 text-sm">
            <button type="submit" class="w-full bg-yellow-500 text-slate-950 font-bold py-3 rounded-xl text-sm">Masuk</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Ambil semua pesanan beserta rincian barangnya
$pesananList = $pdo->query(
    'SELECT p.*, s.nisn FROM pesanan p
     JOIN siswa s ON s.id = p.siswa_id
     ORDER BY p.dibuat_pada DESC'
)->fetchAll();

$stmtDetail = $pdo->prepare('SELECT * FROM pesanan_detail WHERE pesanan_id = ?');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pesanan - Katapang Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 min-h-screen text-white p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">📦 Data Pesanan Seragam - Katapang Store</h1>
            <a href="?logout=1" class="text-xs bg-slate-800 hover:bg-slate-700 px-4 py-2 rounded-xl">Logout</a>
        </div>

        <?php if (empty($pesananList)): ?>
            <p class="text-slate-400 text-sm">Belum ada pesanan masuk.</p>
        <?php endif; ?>

        <div class="space-y-4">
        <?php foreach ($pesananList as $p): ?>
            <?php
                $stmtDetail->execute([$p['id']]);
                $items = $stmtDetail->fetchAll();
            ?>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex flex-wrap justify-between gap-2 mb-3 text-sm">
                    <div>
                        <span class="font-bold text-yellow-400">#<?= (int)$p['id'] ?></span>
                        &middot; <?= htmlspecialchars($p['nama_pemesan']) ?>
                        (<?= htmlspecialchars($p['kelas']) ?>, NISN <?= htmlspecialchars($p['nisn']) ?>)
                    </div>
                    <div class="text-slate-400 text-xs"><?= htmlspecialchars($p['dibuat_pada']) ?></div>
                </div>
                <p class="text-xs text-slate-400 mb-2">Metode: <?= htmlspecialchars($p['metode_bayar']) ?> &middot; Status: <?= htmlspecialchars($p['status']) ?></p>
                <ul class="text-xs text-slate-300 mb-2 list-disc list-inside">
                    <?php foreach ($items as $it): ?>
                        <li><?= htmlspecialchars($it['nama_produk']) ?> x<?= (int)$it['jumlah'] ?> = Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="text-sm font-bold text-yellow-400">Total: Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></p>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
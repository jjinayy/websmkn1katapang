<?php
/**
 * register.php
 * -----------------------------------------------------------
 * Halaman pendaftaran akun siswa (mandiri). Desainnya dibuat
 * senada dengan modal login di index.php (tema gelap + brandYellow)
 * supaya tidak terasa asing, tapi ini halaman baru yang memang
 * belum ada di desain acuan — dibuat khusus supaya siswa bisa
 * langsung daftar sendiri dan otomatis masuk ke tabel `siswa`.
 * -----------------------------------------------------------
 */

require_once __DIR__ . '/config.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn      = trim($_POST['nisn'] ?? '');
    $nama      = trim($_POST['nama'] ?? '');
    $kelas     = trim($_POST['kelas'] ?? '');
    $password  = (string)($_POST['password'] ?? '');
    $konfirmasi = (string)($_POST['konfirmasi'] ?? '');

    if ($nisn === '' || $nama === '' || $kelas === '' || $password === '') {
        $errors[] = 'Semua kolom wajib diisi.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Cek NISN/username sudah dipakai atau belum
    if (empty($errors)) {
        $cek = $pdo->prepare('SELECT id FROM siswa WHERE nisn = ? LIMIT 1');
        $cek->execute([$nisn]);
        if ($cek->fetch()) {
            $errors[] = 'NISN/Username ini sudah terdaftar. Silakan login, atau pakai NISN lain.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            'INSERT INTO siswa (nisn, nama_lengkap, kelas, password) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nisn, $nama, $kelas, $hash]);

        // Langsung login-kan siswa yang baru daftar
        $_SESSION['siswa_id']    = $pdo->lastInsertId();
        $_SESSION['siswa_nama']  = $nama;
        $_SESSION['siswa_kelas'] = $kelas;

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Siswa - SMKN 1 Katapang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandBlue: '#2563eb',
                        darkBlue: '#1e3a8a',
                        brandYellow: '#eab308',
                    },
                    fontFamily: {
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                        body: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        html { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card-store {
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <a href="index.php" class="text-xs text-slate-400 hover:text-brandYellow transition inline-flex items-center gap-2 mb-4">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
            <h1 class="font-heading font-extrabold text-2xl text-white">SMKN 1 KATAPANG</h1>
            <p class="text-xs text-brandYellow font-semibold uppercase tracking-wider">Daftar Akun Katapang Store</p>
        </div>

        <div class="glass-card-store rounded-3xl p-6 sm:p-8 shadow-2xl">

            <?php if ($success): ?>
                <div class="text-center py-4">
                    <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h2 class="font-heading font-bold text-lg text-white mb-2">Pendaftaran Berhasil!</h2>
                    <p class="text-slate-400 text-xs mb-6">Akun kamu sudah dibuat dan otomatis masuk. Sekarang kamu bisa langsung belanja di Katapang Store.</p>
                    <a href="index.php#katapang-store" class="inline-flex items-center gap-2 bg-brandYellow hover:bg-yellow-400 text-slate-950 px-6 py-3 rounded-xl font-heading font-bold text-sm transition">
                        <i class="fa-solid fa-store"></i> Lanjut ke Katapang Store
                    </a>
                </div>
            <?php else: ?>

                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-brandYellow/20 text-brandYellow border border-brandYellow/30 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="font-heading font-extrabold text-xl text-white">Buat Akun Baru</h3>
                    <p class="text-slate-400 text-xs mt-1">Isi data di bawah untuk daftar akun siswa Katapang Store.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-3 mb-4 text-xs">
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-4 text-xs">
                    <div>
                        <label class="block text-slate-300 mb-1 font-semibold">NISN / Username</label>
                        <input type="text" name="nisn" required value="<?= htmlspecialchars($_POST['nisn'] ?? '') ?>"
                               placeholder="Masukkan NISN atau Username"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-1 font-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                               placeholder="Contoh: Ahmad Fauzi"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-1 font-semibold">Kelas / Jurusan</label>
                        <input type="text" name="kelas" required value="<?= htmlspecialchars($_POST['kelas'] ?? '') ?>"
                               placeholder="Contoh: X RPL 1"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-1 font-semibold">Password</label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-1 font-semibold">Konfirmasi Password</label>
                        <input type="password" name="konfirmasi" required placeholder="Ulangi password"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-brandYellow to-yellow-500 hover:from-yellow-400 hover:to-amber-500 text-slate-950 font-heading font-extrabold py-3.5 rounded-xl text-sm transition shadow-[0_0_15px_rgba(234,179,8,0.3)]">
                        Daftar Sekarang
                    </button>

                    <p class="text-center text-slate-400 text-[11px] pt-1">
                        Sudah punya akun? <a href="index.php#katapang-store" class="text-brandYellow font-bold hover:underline">Login di sini</a>
                    </p>
                </form>

            <?php endif; ?>
        </div>
    </div>

</body>
</html>
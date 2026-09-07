<?php
/**
 * config.php
 * -----------------------------------------------------------
 * File koneksi database untuk website SMKN 1 Katapang.
 * Dipakai bersama oleh index.php, auth/login.php, auth/logout.php,
 * checkout.php, dan file di folder admin/.
 *
 * Kalau pakai XAMPP/Laragon, biasanya nilai default di bawah ini
 * sudah langsung jalan (user "root", password kosong).
 * Sesuaikan kalau setting MySQL kamu berbeda.
 * -----------------------------------------------------------
 */

// ---- Ubah bagian ini sesuai environment kamu ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'katapang_store');
define('DB_USER', 'root');
define('DB_PASS', '');
// --------------------------------------------------

// Nomor WhatsApp koperasi (dipakai juga di index.php)
define('NO_WA_KOPERASI', '6281210655596');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal. Pastikan MySQL sudah menyala dan database "katapang_store" sudah diimport. Detail: ' . $e->getMessage()
    ]));
}

// Session dipakai untuk menyimpan status login siswa di seluruh website
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['siswa_id']);
}
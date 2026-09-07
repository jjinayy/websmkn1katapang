<?php
/**
 * auth/login.php
 * -----------------------------------------------------------
 * Dipanggil lewat fetch() dari modal "Login Katapang Store" di index.php.
 * Menerima NISN/username + password, cek ke tabel `siswa`,
 * kalau cocok -> simpan sesi login, kalau tidak -> kirim pesan error.
 * -----------------------------------------------------------
 */

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$username = trim($input['username'] ?? '');
$password = (string)($input['password'] ?? '');

if ($username === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'NISN/Username dan password wajib diisi.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nisn, nama_lengkap, kelas, password FROM siswa WHERE nisn = ? LIMIT 1');
$stmt->execute([$username]);
$siswa = $stmt->fetch();

if (!$siswa || !password_verify($password, $siswa['password'])) {
    echo json_encode(['success' => false, 'message' => 'NISN/Username atau password salah.']);
    exit;
}

// Login berhasil -> simpan ke session
$_SESSION['siswa_id']      = $siswa['id'];
$_SESSION['siswa_nama']    = $siswa['nama_lengkap'];
$_SESSION['siswa_kelas']   = $siswa['kelas'];

echo json_encode([
    'success' => true,
    'message' => 'Login berhasil.',
    'siswa'   => [
        'nama'  => $siswa['nama_lengkap'],
        'kelas' => $siswa['kelas'],
    ]
]);
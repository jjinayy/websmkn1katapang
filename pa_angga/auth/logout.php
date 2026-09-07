<?php
/**
 * auth/logout.php
 * Menghapus session login siswa lalu kembali ke halaman utama.
 */
require_once __DIR__ . '/../config.php';

$_SESSION = [];
session_destroy();

header('Location: ../index.php');
exit;
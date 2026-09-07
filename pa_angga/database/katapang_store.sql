-- =========================================================
-- DATABASE: katapang_store
-- Untuk fitur Login Siswa + Pesan Seragam (Katapang Store)
-- Cara pakai: buka phpMyAdmin > tab SQL > paste semua ini > Go
-- =========================================================

CREATE DATABASE IF NOT EXISTS katapang_store;
USE katapang_store;

-- =========================================================
-- TABEL 1: SISWA (buat Login)
-- =========================================================
CREATE TABLE tb_siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    nisn VARCHAR(20) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    kelas VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================================
-- TABEL 2: PRODUK (Katalog Seragam & Atribut)
-- =========================================================
CREATE TABLE tb_produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    icon VARCHAR(50),
    kategori ENUM('Seragam', 'Atribut') DEFAULT 'Seragam',
    stok INT DEFAULT 100
);

-- Data awal produk (sesuai katalog yang sudah ada di website)
INSERT INTO tb_produk (nama_produk, harga, icon, kategori) VALUES
('Seragam Olahraga (Setel)', 150000, 'fa-shirt', 'Seragam'),
('Batik Khusus K1', 120000, 'fa-user-tie', 'Seragam'),
('Seragam Putih Abu (Setel)', 135000, 'fa-shirt', 'Seragam'),
('Seragam Pramuka (Setel)', 145000, 'fa-campground', 'Seragam'),
('Pakaian Praktikum / Wearpack', 175000, 'fa-vest-patches', 'Seragam'),
('Jas Almamater Sekolah', 160000, 'fa-user-nurse', 'Seragam'),
('Topi Sekolah', 20000, 'fa-graduation-cap', 'Atribut'),
('Dasi Sekolah', 15000, 'fa-user-tie', 'Atribut'),
('Sabuk / Ikat Pinggang', 20000, 'fa-ring', 'Atribut'),
('Set Badge & Logo Bordir', 15000, 'fa-certificate', 'Atribut'),
('Pin / Ring Pramuka', 10000, 'fa-award', 'Atribut'),
('Kaos Kaki Resmi K1 (2 Pasang)', 25000, 'fa-socks', 'Atribut');

-- =========================================================
-- TABEL 3: PESANAN (Header/induk transaksi)
-- =========================================================
CREATE TABLE tb_pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT NOT NULL,
    tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metode_bayar ENUM('WhatsApp / Kasir Koperasi', 'Transfer Bank / QRIS') NOT NULL,
    total_harga INT NOT NULL,
    status_pesanan ENUM('Menunggu Pembayaran', 'Lunas', 'Diproses', 'Selesai', 'Dibatalkan') DEFAULT 'Menunggu Pembayaran',
    FOREIGN KEY (id_siswa) REFERENCES tb_siswa(id_siswa)
);

-- =========================================================
-- TABEL 4: DETAIL PESANAN (isi keranjang per transaksi)
-- =========================================================
CREATE TABLE tb_detail_pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    subtotal INT NOT NULL,
    FOREIGN KEY (id_pesanan) REFERENCES tb_pesanan(id_pesanan),
    FOREIGN KEY (id_produk) REFERENCES tb_produk(id_produk)
);

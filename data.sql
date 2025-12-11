-- Database untuk Sistem Pembelian Makanan (Updated)
CREATE DATABASE IF NOT EXISTS resto_queue;
USE resto_queue;

-- Tabel Admin untuk Login
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Menu Makanan
CREATE TABLE menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    kategori VARCHAR(50),
    gambar VARCHAR(200),
    status ENUM('tersedia', 'habis') DEFAULT 'tersedia'
);

-- Tabel Pesanan (Queue)
CREATE TABLE pesanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_antrian VARCHAR(20) UNIQUE NOT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status ENUM('antri', 'diproses', 'selesai', 'diambil') DEFAULT 'antri',
    waktu_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    waktu_selesai TIMESTAMP NULL,
    posisi_queue INT
);

-- Tabel Detail Pesanan
CREATE TABLE detail_pesanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pesanan_id INT,
    menu_id INT,
    jumlah INT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

-- Tabel Pengaturan Toko (NEW)
CREATE TABLE pengaturan_toko (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_toko VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    logo VARCHAR(200),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admin (username, password, nama_lengkap, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@warungmakan.com');

-- Insert default pengaturan toko
INSERT INTO pengaturan_toko (nama_toko, deskripsi) VALUES
('Warung Makan Sederhana', 'Pesan makanan favoritmu dengan mudah!');

-- Insert sample data menu
INSERT INTO menu (nama, harga, kategori, gambar, status) VALUES
('Nasi Goreng Special', 25000, 'Makanan', 'default.jpg', 'tersedia'),
('Mie Ayam', 20000, 'Makanan', 'default.jpg', 'tersedia'),
('Soto Ayam', 22000, 'Makanan', 'default.jpg', 'tersedia'),
('Ayam Geprek', 23000, 'Makanan', 'default.jpg', 'tersedia'),
('Nasi Uduk', 18000, 'Makanan', 'default.jpg', 'tersedia'),
('Gado-Gado', 20000, 'Makanan', 'default.jpg', 'tersedia'),
('Es Teh Manis', 5000, 'Minuman', 'default.jpg', 'tersedia'),
('Es Jeruk', 7000, 'Minuman', 'default.jpg', 'tersedia'),
('Jus Alpukat', 12000, 'Minuman', 'default.jpg', 'tersedia'),
('Kopi Hitam', 8000, 'Minuman', 'default.jpg', 'tersedia'),
('Es Campur', 15000, 'Minuman', 'default.jpg', 'tersedia'),
('Teh Tarik', 10000, 'Minuman', 'default.jpg', 'tersedia');
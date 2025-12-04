-- Database untuk Sistem Pembelian Makanan
CREATE DATABASE IF NOT EXISTS resto_queue;
USE resto_queue;

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

-- Insert sample data menu
INSERT INTO menu (nama, harga, kategori, gambar, status) VALUES
('Nasi Goreng Special', 25000, 'Makanan', 'nasgor.jpg', 'tersedia'),
('Mie Ayam', 20000, 'Makanan', 'mieayam.jpg', 'tersedia'),
('Soto Ayam', 22000, 'Makanan', 'soto.jpg', 'tersedia'),
('Ayam Geprek', 23000, 'Makanan', 'geprek.jpg', 'tersedia'),
('Es Teh Manis', 5000, 'Minuman', 'esteh.jpg', 'tersedia'),
('Es Jeruk', 7000, 'Minuman', 'esjeruk.jpg', 'tersedia'),
('Jus Alpukat', 12000, 'Minuman', 'alpukat.jpg', 'tersedia'),
('Kopi Hitam', 8000, 'Minuman', 'kopi.jpg', 'tersedia');

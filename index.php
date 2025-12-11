<?php
session_start();
require_once 'config.php';
require_once 'QueueClass.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$query_settings = "SELECT * FROM pengaturan_toko LIMIT 1";
$result_settings = mysqli_query($conn, $query_settings);
$store_settings = mysqli_fetch_assoc($result_settings);

if (!$store_settings) {
    $store_settings = [
        'nama_toko' => 'Warung Makan Sederhana',
        'deskripsi' => 'Pesan makanan favoritmu dengan mudah!'
    ];
}

$query = "SELECT * FROM menu WHERE status = 'tersedia' ORDER BY kategori, nama";
$result = mysqli_query($conn, $query);
$menu_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $menu_items[] = $row;
}

$menu_by_category = [];
foreach ($menu_items as $item) {
    $menu_by_category[$item['kategori']][] = $item;
}

if (isset($_POST['add_to_cart'])) {
    $menu_id = $_POST['menu_id'];
    $jumlah = $_POST['jumlah'];
    
    $query = "SELECT * FROM menu WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $menu_id);
    mysqli_stmt_execute($stmt);
    $menu = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (isset($_SESSION['cart'][$menu_id])) {
        $_SESSION['cart'][$menu_id]['jumlah'] += $jumlah;
    } else {
        $_SESSION['cart'][$menu_id] = [
            'menu_id' => $menu_id,
            'nama' => $menu['nama'],
            'harga' => $menu['harga'],
            'jumlah' => $jumlah
        ];
    }
    
    header("Location: index.php");
    exit;
}

if (isset($_GET['remove'])) {
    $menu_id = $_GET['remove'];
    unset($_SESSION['cart'][$menu_id]);
    header("Location: index.php");
    exit;
}

if (isset($_POST['checkout'])) {
    $nama_pelanggan = trim($_POST['nama_pelanggan']);
    
    if (empty($nama_pelanggan)) {
        $error = "Nama pelanggan harus diisi!";
    } elseif (empty($_SESSION['cart'])) {
        $error = "Keranjang masih kosong!";
    } else {
        $queue = new OrderQueue($conn);
        
        $items = [];
        $total = 0;
        
        foreach ($_SESSION['cart'] as $item) {
            $subtotal = $item['harga'] * $item['jumlah'];
            $items[] = [
                'menu_id' => $item['menu_id'],
                'jumlah' => $item['jumlah'],
                'harga' => $item['harga'],
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }
        
        $nomor_antrian = $queue->enqueue($nama_pelanggan, $items, $total);
        
        if ($nomor_antrian) {
            $_SESSION['cart'] = [];
            header("Location: receipt.php?nomor=" . $nomor_antrian);
            exit;
        } else {
            $error = "Gagal membuat pesanan!";
        }
    }
}

$total_cart = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_cart += $item['harga'] * $item['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($store_settings['nama_toko']); ?> - Sistem Pemesanan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-logo">🍽️</div>
            <h1><?php echo htmlspecialchars($store_settings['nama_toko']); ?></h1>
            <p class="tagline"><?php echo htmlspecialchars($store_settings['deskripsi']); ?></p>
            <div class="header-links">
                <a href="queue.php" class="header-link">
                    <span class="link-icon">📊</span>
                    <span>Lihat Antrian</span>
                </a>
                <a href="login.php" class="header-link">
                    <span class="link-icon">🔐</span>
                    <span>Login Admin</span>
                </a>
            </div>
        </header>

        <div class="main-content">
            <div class="menu-section">
                <h2>📋 Daftar Menu</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (empty($menu_items)): ?>
                    <div class="empty-menu">
                        <div class="empty-icon">🍽️</div>
                        <p>Belum ada menu tersedia</p>
                        <p style="font-size: 0.9em; color: #999;">Silakan hubungi admin untuk menambahkan menu</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($menu_by_category as $kategori => $items): ?>
                        <div class="category-section">
                            <h3><?php echo $kategori; ?></h3>
                            <div class="menu-grid">
                                <?php foreach ($items as $menu): ?>
                                    <div class="menu-card">
                                        <div class="menu-image">
                                            <img src="uploads/menu/<?php echo $menu['gambar']; ?>" 
                                                 alt="<?php echo htmlspecialchars($menu['nama']); ?>"
                                                 onerror="this.src='uploads/menu/default.jpg'">
                                        </div>
                                        <div class="menu-info">
                                            <h4><?php echo htmlspecialchars($menu['nama']); ?></h4>
                                            <p class="price">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></p>
                                        </div>
                                        <form method="POST" class="add-form">
                                            <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                            <input type="number" name="jumlah" value="1" min="1" max="10" class="qty-input">
                                            <button type="submit" name="add_to_cart" class="btn btn-add">
                                                🛒 Tambah
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="cart-section">
                <h2>🛒 Keranjang</h2>
                
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Keranjang masih kosong</p>
                        <p style="font-size: 0.85em; color: #999;">Pilih menu dan tambahkan ke keranjang</p>
                    </div>
                <?php else: ?>
                    <div class="cart-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-info">
                                    <strong><?php echo htmlspecialchars($item['nama']); ?></strong>
                                    <div class="cart-item-details">
                                        <span class="cart-qty"><?php echo $item['jumlah']; ?>x @ Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></span>
                                        <span class="cart-price">Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                                <a href="?remove=<?php echo $item['menu_id']; ?>" class="btn-remove" title="Hapus item">✕</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-total">
                        <strong>Total:</strong>
                        <strong class="total-price">Rp <?php echo number_format($total_cart, 0, ',', '.'); ?></strong>
                    </div>
                    
                    <form method="POST" class="checkout-form">
                        <input type="text" 
                               name="nama_pelanggan" 
                               placeholder="Masukkan Nama Anda" 
                               required 
                               class="input-nama"
                               minlength="3"
                               maxlength="100">
                        <button type="submit" name="checkout" class="btn btn-checkout">
                            📝 Pesan Sekarang
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

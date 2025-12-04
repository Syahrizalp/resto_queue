<?php
// index.php - Halaman Utama Pemesanan
session_start();
require_once 'config.php';
require_once 'QueueClass.php';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get menu items
$query = "SELECT * FROM menu WHERE status = 'tersedia' ORDER BY kategori, nama";
$result = mysqli_query($conn, $query);
$menu_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $menu_items[] = $row;
}

// Group by category
$menu_by_category = [];
foreach ($menu_items as $item) {
    $menu_by_category[$item['kategori']][] = $item;
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $menu_id = $_POST['menu_id'];
    $jumlah = $_POST['jumlah'];
    
    // Get menu details
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

// Handle remove from cart
if (isset($_GET['remove'])) {
    $menu_id = $_GET['remove'];
    unset($_SESSION['cart'][$menu_id]);
    header("Location: index.php");
    exit;
}

// Handle checkout
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

// Calculate total
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
    <title>Warung Makan - Sistem Pemesanan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🍽️ Warung Makan Sederhana</h1>
            <p class="tagline">Pesan makanan favoritmu dengan mudah!</p>
        </header>

        <div class="main-content">
            <!-- Menu Section -->
            <div class="menu-section">
                <h2>📋 Daftar Menu</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php foreach ($menu_by_category as $kategori => $items): ?>
                    <div class="category-section">
                        <h3><?php echo $kategori; ?></h3>
                        <div class="menu-grid">
                            <?php foreach ($items as $menu): ?>
                                <div class="menu-card">
                                    <div class="menu-icon">
                                        <?php echo $menu['kategori'] == 'Makanan' ? '🍛' : '🥤'; ?>
                                    </div>
                                    <div class="menu-info">
                                        <h4><?php echo $menu['nama']; ?></h4>
                                        <p class="price">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></p>
                                    </div>
                                    <form method="POST" class="add-form">
                                        <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                        <input type="number" name="jumlah" value="1" min="1" max="10" class="qty-input">
                                        <button type="submit" name="add_to_cart" class="btn btn-add">Tambah</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Section -->
            <div class="cart-section">
                <h2>🛒 Keranjang Belanja</h2>
                
                <?php if (empty($_SESSION['cart'])): ?>
                    <p class="empty-cart">Keranjang masih kosong</p>
                <?php else: ?>
                    <div class="cart-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-info">
                                    <strong><?php echo $item['nama']; ?></strong>
                                    <span class="cart-qty"><?php echo $item['jumlah']; ?>x</span>
                                    <span class="cart-price">Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></span>
                                </div>
                                <a href="?remove=<?php echo $item['menu_id']; ?>" class="btn-remove">✕</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-total">
                        <strong>Total:</strong>
                        <strong>Rp <?php echo number_format($total_cart, 0, ',', '.'); ?></strong>
                    </div>
                    
                    <form method="POST" class="checkout-form">
                        <input type="text" name="nama_pelanggan" placeholder="Nama Pelanggan" required class="input-nama">
                        <button type="submit" name="checkout" class="btn btn-checkout">Pesan Sekarang</button>
                    </form>
                <?php endif; ?>
                
                <div class="links">
                    <a href="queue.php" class="link">👁️ Lihat Antrian</a>
                    <a href="admin.php" class="link">⚙️ Admin Panel</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
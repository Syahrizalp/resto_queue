<?php
session_start();

require_once 'config.php';
require_once 'AuthClass.php';
require_once 'QueueClass.php';

$auth = new Auth($conn);
$auth->requireLogin();

$admin_info = $auth->getAdminInfo();
$queue = new OrderQueue($conn);
$message = '';

if (isset($_GET['logout'])) {
    $auth->logout();
    header("Location: login.php");
    exit;
}

if (isset($_POST['process_order'])) {
    $result = $queue->dequeue();
    if ($result) {
        $message = "Pesanan {$result['nomor_antrian']} sedang diproses!";
    } else {
        $message = "Tidak ada pesanan dalam antrian!";
    }
}

if (isset($_POST['complete_order'])) {
    $order_id = $_POST['order_id'];
    if ($queue->completeOrder($order_id)) {
        $message = "Pesanan telah selesai!";
    }
}

if (isset($_POST['mark_taken'])) {
    $order_id = $_POST['order_id'];
    if ($queue->markAsTaken($order_id)) {
        $message = "Pesanan telah diambil pelanggan!";
    }
}

$all_queue = $queue->getAllQueue();

$query_completed = "SELECT * FROM pesanan WHERE status = 'selesai' ORDER BY waktu_selesai DESC LIMIT 10";
$completed_orders = mysqli_query($conn, $query_completed);

$query_stats = "SELECT 
    COUNT(CASE WHEN status = 'antri' THEN 1 END) as waiting,
    COUNT(CASE WHEN status = 'diproses' THEN 1 END) as processing,
    COUNT(CASE WHEN status = 'selesai' THEN 1 END) as completed,
    COUNT(CASE WHEN DATE(waktu_pesan) = CURDATE() THEN 1 END) as today_orders,
    SUM(CASE WHEN DATE(waktu_pesan) = CURDATE() THEN total_harga ELSE 0 END) as today_revenue
FROM pesanan";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $query_stats));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kelola Antrian</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="30">
</head>

<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">🍽️</div>
                <h3>Admin Panel</h3>
            </div>
            
            <nav class="sidebar-menu">
                <a href="admin.php" class="menu-item active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="manage_menu.php" class="menu-item">
                    <span class="icon">🍴</span>
                    <span>Kelola Menu</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <span class="icon">⚙️</span>
                    <span>Pengaturan Toko</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="admin-profile">
                    <div class="profile-icon">👤</div>
                    <div class="profile-info">
                        <div class="profile-name"><?php echo $admin_info['nama']; ?></div>
                        <div class="profile-role">Administrator</div>
                    </div>
                </div>
                <a href="?logout=1" class="btn-logout">
                    <span>🚪</span> Logout
                </a>
            </div>
        </aside>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Dashboard Kelola Antrian</h1>
                <div class="header-right">
                    <span class="refresh-info">⟳ Auto refresh setiap 30 detik</span>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card stat-waiting">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['waiting']; ?></div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                </div>
                
                <div class="stat-card stat-processing">
                    <div class="stat-icon">⚙️</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['processing']; ?></div>
                        <div class="stat-label">Sedang Diproses</div>
                    </div>
                </div>
                
                <div class="stat-card stat-completed">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['today_orders']; ?></div>
                        <div class="stat-label">Pesanan Hari Ini</div>
                    </div>
                </div>
                
                <div class="stat-card stat-revenue">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-number">Rp <?php echo number_format($stats['today_revenue'], 0, ',', '.'); ?></div>
                        <div class="stat-label">Pendapatan Hari Ini</div>
                    </div>
                </div>
            </div>
            
            <div class="action-card">
                <h3>🔄 Aksi Cepat</h3>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="process_order" class="btn-action btn-process">
                        ▶️ Proses Pesanan Berikutnya
                    </button>
                </form>
                <p class="action-hint">* Tombol ini akan mengambil pesanan pertama dari antrian (FIFO)</p>
            </div>
            
            <div class="content-section">
                <h2>📋 Antrian Aktif</h2>

                <?php if (empty($all_queue)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>Tidak ada antrian saat ini</p>
                    </div>
                <?php else: ?>
                    <div class="queue-grid">
                        <?php foreach ($all_queue as $order): ?>
                            <?php
                            $details = $queue->getOrderDetails($order['id']);
                            $is_processing = $order['status'] == 'diproses';
                            ?>
                            <div class="order-card <?php echo $is_processing ? 'processing' : ''; ?>">
                                <div class="order-header">
                                    <h4><?php echo $order['nomor_antrian']; ?></h4>
                                    <?php if ($is_processing): ?>
                                        <span class="badge badge-processing">Sedang Diproses</span>
                                    <?php else: ?>
                                        <span class="badge badge-waiting">Menunggu</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="order-body">
                                    <p class="customer-name">👤 <?php echo $order['nama_pelanggan']; ?></p>
                                    
                                    <div class="order-items">
                                        <?php foreach ($details as $detail): ?>
                                            <div class="order-item">
                                                <span><?php echo $detail['nama_menu']; ?></span>
                                                <span class="qty">×<?php echo $detail['jumlah']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="order-total">
                                        <strong>Total:</strong>
                                        <strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong>
                                    </div>
                                    
                                    <div class="order-time">
                                        🕒 <?php echo date('d/m/Y H:i', strtotime($order['waktu_pesan'])); ?>
                                    </div>
                                </div>
                                
                                <?php if ($is_processing): ?>
                                    <form method="POST" class="order-action">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="complete_order" class="btn-complete">
                                            ✓ Tandai Selesai
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="content-section">
                <h2>✅ Pesanan Selesai (10 Terakhir)</h2>

                <?php if (mysqli_num_rows($completed_orders) == 0): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <p>Belum ada pesanan yang selesai</p>
                    </div>
                <?php else: ?>
                    <div class="completed-list">
                        <?php while ($order = mysqli_fetch_assoc($completed_orders)): ?>
                            <?php $details = $queue->getOrderDetails($order['id']); ?>
                            <div class="completed-item">
                                <div class="completed-info">
                                    <h4><?php echo $order['nomor_antrian']; ?> - <?php echo $order['nama_pelanggan']; ?></h4>
                                    <div class="completed-details">
                                        <?php foreach ($details as $detail): ?>
                                            <span class="item-tag"><?php echo $detail['nama_menu']; ?> (<?php echo $detail['jumlah']; ?>x)</span>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="completed-meta">
                                        <span class="price">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
                                        <span class="time">✓ <?php echo $order['waktu_selesai'] ? date('d/m H:i', strtotime($order['waktu_selesai'])) : '-'; ?></span>
                                    </div>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="mark_taken" class="btn-taken">
                                        👤 Sudah Diambil
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>

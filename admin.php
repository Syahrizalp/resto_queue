<?php
// admin.php - Admin Panel untuk Kelola Antrian
require_once 'config.php';
require_once 'QueueClass.php';

$queue = new OrderQueue($conn);
$message = '';

// Handle process order
if (isset($_POST['process_order'])) {
    $result = $queue->dequeue();
    if ($result) {
        $message = "Pesanan {$result['nomor_antrian']} sedang diproses!";
    } else {
        $message = "Tidak ada pesanan dalam antrian!";
    }
}

// Handle complete order
if (isset($_POST['complete_order'])) {
    $order_id = $_POST['order_id'];
    if ($queue->completeOrder($order_id)) {
        $message = "Pesanan telah selesai!";
    }
}

// Handle mark as taken
if (isset($_POST['mark_taken'])) {
    $order_id = $_POST['order_id'];
    if ($queue->markAsTaken($order_id)) {
        $message = "Pesanan telah diambil pelanggan!";
    }
}

// Get all orders
$all_queue = $queue->getAllQueue();

// Get completed orders
$query_completed = "SELECT * FROM pesanan WHERE status = 'selesai' ORDER BY waktu_selesai DESC LIMIT 10";
$completed_orders = mysqli_query($conn, $query_completed);

// Statistics
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
    <meta http-equiv="refresh" content="15">
</head>
<body>
    <div class="container">
        <header>
            <h1>⚙️ Admin Panel</h1>
            <p class="tagline">Kelola antrian pesanan dan status pengerjaan</p>
        </header>
        
        <div class="admin-container">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <h2 style="color: #667eea; margin-bottom: 20px;">📊 Statistik Hari Ini</h2>
            <div class="queue-stats" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['waiting']; ?></div>
                    <div class="stat-label">Menunggu</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                    <div class="stat-number"><?php echo $stats['processing']; ?></div>
                    <div class="stat-label">Sedang Diproses</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                    <div class="stat-number"><?php echo $stats['today_orders']; ?></div>
                    <div class="stat-label">Pesanan Hari Ini</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                    <div class="stat-number">Rp <?php echo number_format($stats['today_revenue'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Pendapatan Hari Ini</div>
                </div>
            </div>
            
            <!-- Queue Actions -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px; color: #333;">🔄 Aksi Cepat</h3>
                <div class="admin-actions">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="process_order" class="btn-action btn-process">
                            ▶️ Proses Pesanan Berikutnya
                        </button>
                    </form>
                </div>
                <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
                    * Tombol ini akan mengambil pesanan pertama dari antrian (FIFO - First In First Out)
                </p>
            </div>
            
            <!-- Active Queue -->
            <h2 style="color: #667eea; margin-bottom: 20px;">📋 Antrian Aktif</h2>
            <?php if (empty($all_queue)): ?>
                <p class="empty-cart">Tidak ada antrian saat ini</p>
            <?php else: ?>
                <div class="queue-list">
                    <?php foreach ($all_queue as $order): ?>
                        <?php
                        $details = $queue->getOrderDetails($order['id']);
                        $status_class = $order['status'] == 'diproses' ? 'queue-item processing' : 'queue-item';
                        ?>
                        <div class="<?php echo $status_class; ?>">
                            <div class="queue-info">
                                <h3><?php echo $order['nomor_antrian']; ?> - <?php echo $order['nama_pelanggan']; ?></h3>
                                <p><strong>Pesanan:</strong></p>
                                <?php foreach ($details as $detail): ?>
                                    <p>• <?php echo $detail['nama_menu']; ?> (<?php echo $detail['jumlah']; ?>x)</p>
                                <?php endforeach; ?>
                                <p><strong>Total: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></p>
                                <p style="font-size: 0.85em; color: #999;">
                                    <?php echo date('d/m/Y H:i', strtotime($order['waktu_pesan'])); ?>
                                </p>
                            </div>
                            <div>
                                <?php if ($order['status'] == 'diproses'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="complete_order" class="btn-action btn-complete">
                                            ✓ Selesai
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="queue-status status-waiting">Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Completed Orders -->
            <h2 style="color: #27ae60; margin: 40px 0 20px;">✅ Pesanan Selesai (10 Terakhir)</h2>
            <?php if (mysqli_num_rows($completed_orders) == 0): ?>
                <p class="empty-cart">Belum ada pesanan yang selesai</p>
            <?php else: ?>
                <div class="queue-list">
                    <?php while ($order = mysqli_fetch_assoc($completed_orders)): ?>
                        <?php $details = $queue->getOrderDetails($order['id']); ?>
                        <div class="queue-item completed">
                            <div class="queue-info">
                                <h3><?php echo $order['nomor_antrian']; ?> - <?php echo $order['nama_pelanggan']; ?></h3>
                                <p><strong>Pesanan:</strong></p>
                                <?php foreach ($details as $detail): ?>
                                    <p>• <?php echo $detail['nama_menu']; ?> (<?php echo $detail['jumlah']; ?>x)</p>
                                <?php endforeach; ?>
                                <p><strong>Total: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></p>
                                <p style="font-size: 0.85em; color: #999;">
                                    Selesai: <?php echo $order['waktu_selesai'] ? date('d/m/Y H:i', strtotime($order['waktu_selesai'])) : '-'; ?>
                                </p>
                            </div>
                            <div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="mark_taken" class="btn-action" style="background: #3498db;">
                                        👤 Sudah Diambil
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn-back">🏠 Kembali ke Beranda</a>
                <a href="queue.php" class="btn-back" style="background: #27ae60; margin-left: 10px;">
                    📊 Lihat Antrian Publik
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// queue.php - Halaman Lihat Antrian
require_once 'config.php';
require_once 'QueueClass.php';

$queue = new OrderQueue($conn);
$all_queue = $queue->getAllQueue();
$queue_size = $queue->getQueueSize();

// Count by status
$waiting = 0;
$processing = 0;
foreach ($all_queue as $order) {
    if ($order['status'] == 'antri') $waiting++;
    if ($order['status'] == 'diproses') $processing++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian Pesanan</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="30">
</head>
<body>
    <div class="container">
        <div class="queue-container">
            <div class="queue-header">
                <h2>📊 Daftar Antrian Pesanan</h2>
                <p>Halaman akan refresh otomatis setiap 30 detik</p>
            </div>
            
            <div class="queue-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $waiting; ?></div>
                    <div class="stat-label">Menunggu</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                    <div class="stat-number"><?php echo $processing; ?></div>
                    <div class="stat-label">Sedang Diproses</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                    <div class="stat-number"><?php echo count($all_queue); ?></div>
                    <div class="stat-label">Total Antrian</div>
                </div>
            </div>
            
            <?php if (empty($all_queue)): ?>
                <p class="empty-cart">Belum ada antrian pesanan</p>
            <?php else: ?>
                <div class="queue-list">
                    <?php foreach ($all_queue as $order): ?>
                        <?php
                        $details = $queue->getOrderDetails($order['id']);
                        $status_class = '';
                        $status_text = '';
                        $status_badge = '';
                        
                        if ($order['status'] == 'antri') {
                            $status_class = 'queue-item';
                            $status_text = 'Menunggu';
                            $status_badge = 'status-waiting';
                        } elseif ($order['status'] == 'diproses') {
                            $status_class = 'queue-item processing';
                            $status_text = 'Sedang Diproses';
                            $status_badge = 'status-processing';
                        }
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
                                <span class="queue-status <?php echo $status_badge; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn-back">🏠 Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
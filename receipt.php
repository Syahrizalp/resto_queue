<?php
// receipt.php - Halaman Struk Pesanan
require_once 'config.php';
require_once 'QueueClass.php';

$nomor_antrian = isset($_GET['nomor']) ? $_GET['nomor'] : '';

if (empty($nomor_antrian)) {
    header("Location: index.php");
    exit;
}

// Get order details
$query = "SELECT * FROM pesanan WHERE nomor_antrian = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $nomor_antrian);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: index.php");
    exit;
}

$queue = new OrderQueue($conn);
$details = $queue->getOrderDetails($order['id']);
$queue_size = $queue->getQueueSize();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="receipt-container">
            <div class="receipt-icon">✅</div>
            <h2>Pesanan Berhasil!</h2>
            <p>Terima kasih telah memesan</p>
            
            <div class="queue-number">
                <?php echo $order['nomor_antrian']; ?>
            </div>
            
            <div class="receipt-details">
                <div class="receipt-row">
                    <span>Nama Pelanggan:</span>
                    <strong><?php echo $order['nama_pelanggan']; ?></strong>
                </div>
                
                <div class="receipt-row">
                    <span>Waktu Pesan:</span>
                    <span><?php echo date('d/m/Y H:i', strtotime($order['waktu_pesan'])); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span>Posisi Antrian:</span>
                    <strong><?php echo $order['posisi_queue']; ?> dari <?php echo $queue_size; ?></strong>
                </div>
                
                <div style="margin: 20px 0; padding-top: 15px; border-top: 2px dashed #ddd;">
                    <p style="font-weight: bold; margin-bottom: 10px;">Detail Pesanan:</p>
                    <?php foreach ($details as $detail): ?>
                        <div class="receipt-row">
                            <span><?php echo $detail['nama_menu']; ?> (<?php echo $detail['jumlah']; ?>x)</span>
                            <span>Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="receipt-row">
                    <span>Total Bayar:</span>
                    <strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong>
                </div>
            </div>
            
            <p style="color: #666; margin: 20px 0;">
                Silakan tunggu nomor antrian Anda dipanggil.<br>
                Anda dapat melihat status antrian di layar monitor.
            </p>
            
            <a href="index.php" class="btn-back">🏠 Kembali ke Beranda</a>
            <a href="queue.php" class="btn-back" style="background: #27ae60; margin-left: 10px;">
                📊 Lihat Antrian
            </a>
        </div>
    </div>
</body>
</html>
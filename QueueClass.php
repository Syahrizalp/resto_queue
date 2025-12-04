<?php
// QueueClass.php - Implementasi Struktur Data Queue untuk Pesanan

class OrderQueue {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Enqueue - Menambahkan pesanan ke antrian
    public function enqueue($nama_pelanggan, $items, $total_harga) {
        // Generate nomor antrian
        $nomor_antrian = $this->generateQueueNumber();
        
        // Hitung posisi queue
        $posisi = $this->getQueueSize() + 1;
        
        // Insert pesanan
        $query = "INSERT INTO pesanan (nomor_antrian, nama_pelanggan, total_harga, posisi_queue) 
                  VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "ssdi", $nomor_antrian, $nama_pelanggan, $total_harga, $posisi);
        
        if (mysqli_stmt_execute($stmt)) {
            $pesanan_id = mysqli_insert_id($this->conn);
            
            // Insert detail pesanan
            foreach ($items as $item) {
                $query_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga, subtotal) 
                                VALUES (?, ?, ?, ?, ?)";
                $stmt_detail = mysqli_prepare($this->conn, $query_detail);
                mysqli_stmt_bind_param($stmt_detail, "iiidd", 
                    $pesanan_id, $item['menu_id'], $item['jumlah'], $item['harga'], $item['subtotal']);
                mysqli_stmt_execute($stmt_detail);
            }
            
            return $nomor_antrian;
        }
        
        return false;
    }
    
    // Dequeue - Mengambil dan memproses pesanan pertama dari antrian
    public function dequeue() {
        $query = "SELECT * FROM pesanan WHERE status = 'antri' ORDER BY posisi_queue ASC LIMIT 1";
        $result = mysqli_query($this->conn, $query);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Update status menjadi diproses
            $update = "UPDATE pesanan SET status = 'diproses' WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $update);
            mysqli_stmt_bind_param($stmt, "i", $row['id']);
            mysqli_stmt_execute($stmt);
            
            return $row;
        }
        
        return null;
    }
    
    // Peek - Melihat pesanan pertama tanpa menghapus
    public function peek() {
        $query = "SELECT * FROM pesanan WHERE status = 'antri' ORDER BY posisi_queue ASC LIMIT 1";
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_assoc($result);
    }
    
    // Get Queue Size - Menghitung jumlah antrian
    public function getQueueSize() {
        $query = "SELECT COUNT(*) as total FROM pesanan WHERE status = 'antri'";
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Get All Queue - Mendapatkan semua antrian
    public function getAllQueue() {
        $query = "SELECT * FROM pesanan WHERE status IN ('antri', 'diproses') ORDER BY posisi_queue ASC";
        $result = mysqli_query($this->conn, $query);
        $queue = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $queue[] = $row;
        }
        return $queue;
    }
    
    // Complete Order - Menyelesaikan pesanan
    public function completeOrder($id) {
        $query = "UPDATE pesanan SET status = 'selesai', waktu_selesai = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }
    
    // Mark as Taken - Pesanan sudah diambil
    public function markAsTaken($id) {
        $query = "UPDATE pesanan SET status = 'diambil' WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }
    
    // Generate Queue Number
    private function generateQueueNumber() {
        $prefix = "Q" . date("ymd");
        $query = "SELECT COUNT(*) as total FROM pesanan WHERE nomor_antrian LIKE ?";
        $stmt = mysqli_prepare($this->conn, $query);
        $like = $prefix . "%";
        mysqli_stmt_bind_param($stmt, "s", $like);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $number = $row['total'] + 1;
        return $prefix . str_pad($number, 3, "0", STR_PAD_LEFT);
    }
    
    // Get Order Details
    public function getOrderDetails($pesanan_id) {
        $query = "SELECT dp.*, m.nama as nama_menu 
                  FROM detail_pesanan dp 
                  JOIN menu m ON dp.menu_id = m.id 
                  WHERE dp.pesanan_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $pesanan_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $details = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $details[] = $row;
        }
        return $details;
    }
}
?>
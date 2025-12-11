<?php
session_start();
require_once 'config.php';
require_once 'AuthClass.php';

$auth = new Auth($conn);
$auth->requireLogin();

$admin_info = $auth->getAdminInfo();
$message = '';
$error = '';

if (isset($_GET['logout'])) {
    $auth->logout();
    header("Location: login.php");
    exit;
}

$query = "SELECT * FROM pengaturan_toko LIMIT 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

if (!$settings) {
    $insert = "INSERT INTO pengaturan_toko (nama_toko, deskripsi) VALUES ('Warung Makan Sederhana', 'Pesan makanan favoritmu dengan mudah!')";
    mysqli_query($conn, $insert);
    $settings = mysqli_fetch_assoc(mysqli_query($conn, $query));
}

if (isset($_POST['update_settings'])) {
    $nama_toko = trim($_POST['nama_toko']);
    $deskripsi = trim($_POST['deskripsi']);
    
    if (empty($nama_toko)) {
        $error = "Nama toko harus diisi!";
    } else {
        $query_update = "UPDATE pengaturan_toko SET nama_toko = ?, deskripsi = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt, "ssi", $nama_toko, $deskripsi, $settings['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Pengaturan berhasil diupdate!";
            $settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan_toko LIMIT 1"));
        } else {
            $error = "Gagal mengupdate pengaturan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Toko - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">🍽️</div>
                <h3>Admin Panel</h3>
            </div>
            
            <nav class="sidebar-menu">
                <a href="admin.php" class="menu-item">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="manage_menu.php" class="menu-item">
                    <span class="icon">🍴</span>
                    <span>Kelola Menu</span>
                </a>
                <a href="settings.php" class="menu-item active">
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
                <h1>Pengaturan Toko</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="settings-container">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2>⚙️ Pengaturan Informasi Toko</h2>
                        <p>Kelola informasi yang ditampilkan di halaman utama</p>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <div class="form-group">
                            <label>🏪 Nama Toko</label>
                            <input type="text" 
                                   name="nama_toko" 
                                   value="<?php echo htmlspecialchars($settings['nama_toko']); ?>" 
                                   required 
                                   placeholder="Contoh: Warung Makan Sederhana"
                                   class="form-control">
                            <p class="form-hint">Nama toko yang akan ditampilkan di header</p>
                        </div>
                        
                        <div class="form-group">
                            <label>📝 Deskripsi/Tagline</label>
                            <textarea name="deskripsi" 
                                      rows="3" 
                                      placeholder="Contoh: Pesan makanan favoritmu dengan mudah!"
                                      class="form-control"><?php echo htmlspecialchars($settings['deskripsi']); ?></textarea>
                            <p class="form-hint">Deskripsi singkat yang menarik untuk toko Anda</p>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_settings" class="btn-save-settings">
                                💾 Simpan Perubahan
                            </button>
                            <a href="index.php" target="_blank" class="btn-preview">
                                👁️ Preview Halaman
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="preview-card">
                    <div class="preview-header">
                        <h3>👁️ Preview</h3>
                        <p>Tampilan di halaman utama:</p>
                    </div>
                    <div class="preview-content">
                        <div class="preview-header-demo">
                            <div class="preview-logo">🍽️</div>
                            <h1><?php echo htmlspecialchars($settings['nama_toko']); ?></h1>
                            <p class="preview-tagline"><?php echo htmlspecialchars($settings['deskripsi']); ?></p>
                        </div>
                    </div>
                </div>
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

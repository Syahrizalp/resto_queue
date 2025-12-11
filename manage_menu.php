<?php
// manage_menu.php - Kelola Menu dengan Upload Gambar
session_start();
require_once 'config.php';
require_once 'AuthClass.php';

$auth = new Auth($conn);
$auth->requireLogin();

$admin_info = $auth->getAdminInfo();
$message = '';
$error = '';

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header("Location: login.php");
    exit;
}

// Function to upload image
function uploadImage($file) {
    $target_dir = "uploads/menu/";
    
    // Create directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ["success" => false, "message" => "File bukan gambar!"];
    }
    
    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "Ukuran file terlalu besar! Maksimal 5MB"];
    }
    
    // Allow certain file formats
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
        return ["success" => false, "message" => "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan!"];
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $new_filename];
    } else {
        return ["success" => false, "message" => "Gagal upload file!"];
    }
}

// Handle Add Menu
if (isset($_POST['add_menu'])) {
    $nama = trim($_POST['nama']);
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $status = $_POST['status'];
    $gambar = 'default.jpg';
    
    if (empty($nama) || empty($harga)) {
        $error = "Nama dan harga menu harus diisi!";
    } else {
        // Handle image upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $upload_result = uploadImage($_FILES['gambar']);
            if ($upload_result['success']) {
                $gambar = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (empty($error)) {
            $query = "INSERT INTO menu (nama, harga, kategori, gambar, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sdsss", $nama, $harga, $kategori, $gambar, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Menu berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan menu!";
            }
        }
    }
}

// Handle Edit Menu
if (isset($_POST['edit_menu'])) {
    $id = $_POST['menu_id'];
    $nama = trim($_POST['nama']);
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $status = $_POST['status'];
    
    if (empty($nama) || empty($harga)) {
        $error = "Nama dan harga menu harus diisi!";
    } else {
        // Get current image
        $query_current = "SELECT gambar FROM menu WHERE id = ?";
        $stmt_current = mysqli_prepare($conn, $query_current);
        mysqli_stmt_bind_param($stmt_current, "i", $id);
        mysqli_stmt_execute($stmt_current);
        $result_current = mysqli_stmt_get_result($stmt_current);
        $current_data = mysqli_fetch_assoc($result_current);
        $gambar = $current_data['gambar'];
        
        // Handle image upload if new image is provided
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $upload_result = uploadImage($_FILES['gambar']);
            if ($upload_result['success']) {
                // Delete old image if not default
                if ($gambar != 'default.jpg' && file_exists("uploads/menu/" . $gambar)) {
                    unlink("uploads/menu/" . $gambar);
                }
                $gambar = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (empty($error)) {
            $query = "UPDATE menu SET nama = ?, harga = ?, kategori = ?, gambar = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sdsssi", $nama, $harga, $kategori, $gambar, $status, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Menu berhasil diupdate!";
            } else {
                $error = "Gagal mengupdate menu!";
            }
        }
    }
}

// Handle Delete Menu
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if menu is used in orders
    $check_query = "SELECT COUNT(*) as total FROM detail_pesanan WHERE menu_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));
    
    if ($check_result['total'] > 0) {
        $error = "Menu tidak dapat dihapus karena sudah digunakan dalam pesanan!";
    } else {
        // Get image filename
        $query_img = "SELECT gambar FROM menu WHERE id = ?";
        $stmt_img = mysqli_prepare($conn, $query_img);
        mysqli_stmt_bind_param($stmt_img, "i", $id);
        mysqli_stmt_execute($stmt_img);
        $result_img = mysqli_stmt_get_result($stmt_img);
        $img_data = mysqli_fetch_assoc($result_img);
        
        // Delete menu
        $query = "DELETE FROM menu WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Delete image file if not default
            if ($img_data['gambar'] != 'default.jpg' && file_exists("uploads/menu/" . $img_data['gambar'])) {
                unlink("uploads/menu/" . $img_data['gambar']);
            }
            $message = "Menu berhasil dihapus!";
        } else {
            $error = "Gagal menghapus menu!";
        }
    }
}

// Get all menus
$query = "SELECT * FROM menu ORDER BY kategori, nama";
$menus = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
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
                <a href="manage_menu.php" class="menu-item active">
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
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Kelola Menu</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Menu Management -->
            <div class="menu-management">
                <div class="section-header">
                    <h2>📋 Daftar Menu</h2>
                    <button class="btn-add-menu" onclick="openAddModal()">
                        <span>➕</span>
                        <span>Tambah Menu</span>
                    </button>
                </div>
                
                <div class="menu-table-wrapper">
                    <table class="menu-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Gambar</th>
                                <th>Nama Menu</th>
                                <th>Harga</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($menus) == 0): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                        Belum ada menu
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($menu = mysqli_fetch_assoc($menus)): ?>
                                    <tr>
                                        <td><?php echo $menu['id']; ?></td>
                                        <td>
                                            <img src="uploads/menu/<?php echo $menu['gambar']; ?>" 
                                                 alt="<?php echo $menu['nama']; ?>"
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                 onerror="this.src='uploads/menu/default.jpg'">
                                        </td>
                                        <td><strong><?php echo $menu['nama']; ?></strong></td>
                                        <td>Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo $menu['kategori']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $menu['status']; ?>">
                                                <?php echo ucfirst($menu['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick='openEditModal(<?php echo json_encode($menu); ?>)'>
                                                    ✏️ Edit
                                                </button>
                                                <button class="btn-delete" onclick="confirmDelete(<?php echo $menu['id']; ?>, '<?php echo addslashes($menu['nama']); ?>')">
                                                    🗑️ Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add Menu Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>➕ Tambah Menu Baru</h3>
                <button class="close-modal" onclick="closeAddModal()">×</button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="modal-form">
                <div class="form-group">
                    <label>Gambar Menu</label>
                    <input type="file" name="gambar" accept="image/*" class="file-input" id="add-image-input" onchange="previewAddImage(event)">
                    <div class="image-preview" id="add-image-preview"></div>
                    <p style="font-size: 0.85em; color: #999; margin-top: 5px;">Format: JPG, PNG, GIF. Maksimal 5MB</p>
                </div>
                
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="nama" required placeholder="Contoh: Nasi Goreng Special">
                </div>
                
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" required placeholder="25000" min="0" step="1000">
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                    </select>
                </div>
                
                <button type="submit" name="add_menu" class="btn-submit">
                    Tambah Menu
                </button>
            </form>
        </div>
    </div>
    
    <!-- Edit Menu Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Edit Menu</h3>
                <button class="close-modal" onclick="closeEditModal()">×</button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="modal-form">
                <input type="hidden" name="menu_id" id="edit_menu_id">
                
                <div class="form-group">
                    <label>Gambar Menu</label>
                    <div class="current-image" id="edit-current-image"></div>
                    <input type="file" name="gambar" accept="image/*" class="file-input" id="edit-image-input" onchange="previewEditImage(event)">
                    <div class="image-preview" id="edit-image-preview"></div>
                    <p style="font-size: 0.85em; color: #999; margin-top: 5px;">Kosongkan jika tidak ingin mengubah gambar</p>
                </div>
                
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="nama" id="edit_nama" required>
                </div>
                
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" id="edit_harga" required min="0" step="1000">
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" id="edit_kategori" required>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                    </select>
                </div>
                
                <button type="submit" name="edit_menu" class="btn-submit">
                    Update Menu
                </button>
            </form>
        </div>
    </div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    
    <script>
        // Preview image for add form
        function previewAddImage(event) {
            const preview = document.getElementById('add-image-preview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; border-radius: 8px;">';
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Preview image for edit form
        function previewEditImage(event) {
            const preview = document.getElementById('edit-image-preview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; border-radius: 8px;">';
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Add Modal
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
            document.getElementById('add-image-preview').innerHTML = '';
            document.getElementById('add-image-input').value = '';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }
        
        // Edit Modal
        function openEditModal(menu) {
            document.getElementById('edit_menu_id').value = menu.id;
            document.getElementById('edit_nama').value = menu.nama;
            document.getElementById('edit_harga').value = menu.harga;
            document.getElementById('edit_kategori').value = menu.kategori;
            document.getElementById('edit_status').value = menu.status;
            
            // Show current image
            const currentImage = document.getElementById('edit-current-image');
            currentImage.innerHTML = '<p style="margin-bottom: 10px; font-weight: 600;">Gambar Saat Ini:</p><img src="uploads/menu/' + menu.gambar + '" style="max-width: 200px; border-radius: 8px;" onerror="this.src=\'uploads/menu/default.jpg\'">';
            
            // Clear preview
            document.getElementById('edit-image-preview').innerHTML = '';
            document.getElementById('edit-image-input').value = '';
            
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        // Delete Confirmation
        function confirmDelete(id, nama) {
            if (confirm('Apakah Anda yakin ingin menghapus menu "' + nama + '"?')) {
                window.location.href = '?delete=' + id;
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
        }
        
        // Mobile Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>
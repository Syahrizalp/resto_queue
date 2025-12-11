<?php
// login.php - Halaman Login Admin
session_start();
require_once 'config.php';
require_once 'AuthClass.php';

$auth = new Auth($conn);
$error = '';

// Redirect if already logged in
$auth->preventAdminAccess();

// Handle login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        if ($auth->login($username, $password)) {
            header("Location: admin.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Warung Makan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="login-icon">🔐</div>
                <h2>Admin Login</h2>
                <p>Masuk untuk mengelola sistem</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label>👤 Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>🔒 Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" name="login" class="btn-login">
                    Masuk
                </button>
            </form>
            
            <div class="login-footer">
                <a href="index.php" class="link-back">← Kembali ke Halaman Utama</a>
            </div>
            
            <div class="login-info">
                <p><small>Default Login: admin / password</small></p>
            </div>
        </div>
    </div>
</body>
</html>
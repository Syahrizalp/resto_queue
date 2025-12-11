<?php
// AuthClass.php - Class untuk Autentikasi Admin

class Auth {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Login Admin
    public function login($username, $password) {
        $query = "SELECT * FROM admin WHERE username = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($admin = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama_lengkap'];
                $_SESSION['is_admin'] = true;
                return true;
            }
        }
        return false;
    }
    
    // Logout Admin
    public function logout() {
        session_unset();
        session_destroy();
    }
    
    // Check if admin is logged in
    public function isLoggedIn() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
    
    // Redirect if not logged in
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: login.php");
            exit;
        }
    }
    
    // Prevent admin from accessing if logged in
    public function preventAdminAccess() {
        if ($this->isLoggedIn()) {
            header("Location: admin.php");
            exit;
        }
    }
    
    // Get admin info
    public function getAdminInfo() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'nama' => $_SESSION['admin_nama']
            ];
        }
        return null;
    }
}
?>
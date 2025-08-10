<?php
function verifySellerSession() {

    
    if (!isset($_SESSION['role'])) {
        header("Location: ../../welcome/login.php");
        exit;
    }
    
    if ($_SESSION['role'] !== 'seller') {
        header("Location: ../../welcome/index.php");
        exit;
    }
    
    // Generate CSRF token if not exists
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
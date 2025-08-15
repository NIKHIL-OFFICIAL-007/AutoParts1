<?php
// auth.php - Authentication and Authorization Functions for AutoParts Admin Panel

/**
 * Initialize secure session settings
 */
function secure_session_start() {
    $session_name = 'autoparts_admin_session';
    $secure = true; // Set to true if using HTTPS
    $httponly = true; // Prevent JavaScript access to session ID
    
    // Forces sessions to only use cookies
    ini_set('session.use_only_cookies', 1);
    
    // Gets current cookies params
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Strict'
    ]);
    
    // Sets the session name 
    session_name($session_name);
    session_start();
    session_regenerate_id(true); // Regenerate session ID to prevent fixation
}

/**
 * Verify admin login status
 */
function verify_admin_login($conn) {
    // Check if all session variables are set
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'], $_SESSION['role'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $login_string = $_SESSION['login_string'];
    $role = $_SESSION['role'];
    
    // Verify user role is admin
    if ($role !== 'admin') {
        return false;
    }
    
    // Get user agent string
    $user_browser = $_SERVER['HTTP_USER_AGENT'];
    
    // Prepare SQL statement to prevent SQL injection
    if ($stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1")) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($password);
            $stmt->fetch();
            
            // Verify login string matches
            $login_check = hash('sha512', $password . $user_browser);
            
            if (hash_equals($login_check, $login_string)) {
                // Logged in
                return true;
            }
        }
    }
    
    // Not logged in
    return false;
}

/**
 * Login function for admin users
 */
function admin_login($username, $password, $conn) {
    // Check login attempts first
    if (!check_login_attempts($conn, $username)) {
        return false;
    }

    // Prepare SQL statement to prevent SQL injection
    if ($stmt = $conn->prepare("SELECT id, username, password, role, full_name FROM users WHERE username = ? AND role = 'admin' LIMIT 1")) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $username, $db_password, $role, $full_name);
            $stmt->fetch();
            
            // Verify password
            if (password_verify($password, $db_password)) {
                // Check if password needs rehashing
                if (password_needs_rehash($db_password, PASSWORD_BCRYPT, ['cost' => 12])) {
                    $new_hash = generate_password_hash($password);
                    $conn->query("UPDATE users SET password = '$new_hash' WHERE id = $user_id");
                }
                
                // Get user agent string
                $user_browser = $_SERVER['HTTP_USER_AGENT'];
                
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['role'] = $role;
                $_SESSION['login_string'] = hash('sha512', $db_password . $user_browser);
                
                // Clear login attempts on success
                clear_login_attempts($conn, $username);
                
                // Login successful
                return true;
            }
        }
    }
    
    // Login failed
    return false;
}

/**
 * Logout function
 */
function logout() {
    // Unset all session values
    $_SESSION = array();
    
    // Get session parameters
    $params = session_get_cookie_params();
    
    // Delete the actual cookie
    setcookie(session_name(),
              '', 
              time() - 42000,
              $params["path"], 
              $params["domain"], 
              $params["secure"], 
              $params["httponly"]);
    
    // Destroy session
    session_destroy();
}

/**
 * Check if user is logged in and redirect if not
 */
function require_admin_login($conn) {
    if (!verify_admin_login($conn)) {
        header("Location: ../login.php?error=login_required");
        exit;
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Password strength validation
 */
function validate_password($password) {
    // Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number and 1 special character
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

/**
 * Generate a secure password hash
 */
function generate_password_hash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Rate limiting for login attempts
 */
function check_login_attempts($conn, $username) {
    $max_attempts = 5;
    $lockout_time = 15 * 60; // 15 minutes in seconds
    
    // Get current timestamp
    $now = time();
    
    // Get attempts from database
    if ($stmt = $conn->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE username = ?")) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($attempts, $last_attempt);
            $stmt->fetch();
            
            // Check if lockout time has passed
            if ($last_attempt > ($now - $lockout_time)) {
                if ($attempts >= $max_attempts) {
                    // Still in lockout period
                    return false;
                }
            } else {
                // Lockout period has passed, reset attempts
                $attempts = 0;
            }
        } else {
            // No record exists
            $attempts = 0;
            $last_attempt = 0;
        }
        
        // Update or insert attempt record
        $attempts++;
        $last_attempt = $now;
        
        if ($attempts == 1) {
            $stmt = $conn->prepare("INSERT INTO login_attempts (username, attempts, last_attempt) VALUES (?, ?, ?)");
            $stmt->bind_param('sii', $username, $attempts, $last_attempt);
        } else {
            $stmt = $conn->prepare("UPDATE login_attempts SET attempts = ?, last_attempt = ? WHERE username = ?");
            $stmt->bind_param('iis', $attempts, $last_attempt, $username);
        }
        
        $stmt->execute();
        
        return $attempts < $max_attempts;
    }
    
    return false;
}

/**
 * Clear login attempts on successful login
 */
function clear_login_attempts($conn, $username) {
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
}

/**
 * Check if user has specific permission
 */
function has_permission($conn, $permission) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Prepare SQL to check permissions
    if ($stmt = $conn->prepare("SELECT p.name FROM permissions p 
                               JOIN role_permissions rp ON p.id = rp.permission_id 
                               JOIN user_roles ur ON rp.role_id = ur.role_id 
                               WHERE ur.user_id = ? AND p.name = ?")) {
        $stmt->bind_param('is', $user_id, $permission);
        $stmt->execute();
        $stmt->store_result();
        
        return $stmt->num_rows > 0;
    }
    
    return false;
}

/**
 * Generate a secure random token for password reset
 */
function generate_password_reset_token() {
    return bin2hex(random_bytes(32));
}

/**
 * Validate password reset token
 */
function validate_password_reset_token($conn, $token) {
    // Token is valid for 1 hour
    $expiry = time() - 3600;
    
    if ($stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND created_at > ?")) {
        $stmt->bind_param('si', $token, $expiry);
        $stmt->execute();
        $stmt->store_result();
        
        return $stmt->num_rows > 0;
    }
    
    return false;
}

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
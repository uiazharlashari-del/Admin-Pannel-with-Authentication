<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'auth_system';
$username = 'root';      // Change this
$password = '';          // Change this

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get countries list
function getCountries() {
    return [
        'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Australia', 
        'Bangladesh', 'Brazil', 'Canada', 'China', 'Egypt', 
        'France', 'Germany', 'India', 'Indonesia', 'Iran', 
        'Italy', 'Japan', 'Malaysia', 'Mexico', 'Morocco', 
        'Nepal', 'Netherlands', 'New Zealand', 'Nigeria', 'Pakistan',
        'Philippines', 'Poland', 'Portugal', 'Russia', 'Saudi Arabia',
        'Singapore', 'South Africa', 'South Korea', 'Spain', 'Sweden',
        'Switzerland', 'Thailand', 'Turkey', 'UAE', 'UK', 'Ukraine',
        'USA', 'Vietnam'
    ];
}

// Upload file handler
function uploadFile($file, $targetDir = 'uploads/') {
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Only JPG and PNG images allowed'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Image must be less than 2MB'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'error' => 'Failed to save file'];
}
?>
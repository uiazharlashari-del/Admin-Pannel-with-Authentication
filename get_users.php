<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Debug log
error_log("=== get_users.php called ===");
error_log("Session: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Not logged in'
    ]);
    exit;
}

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false, 
        'message' => 'Not admin'
    ]);
    exit;
}

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'auth_system';
    $username = 'root';      // Change this
    $password = '';          // Change this
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Get all users except current admin
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll();
    
    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE id != ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total = $stmt->fetch();
    
    // Get today's new users
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE() AND id != ?");
    $stmt->execute([$_SESSION['user_id']]);
    $today = $stmt->fetch();
    
    // Return success response
    $response = [
        'success' => true,
        'users' => $users,
        'total' => (int)$total['total'],
        'today' => (int)$today['count'],
        'debug' => [
            'user_id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'],
            'user_count' => count($users)
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
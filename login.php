<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = 'localhost';
$dbname = 'auth_system';
$username = 'root';      // Change this
$password = '';          // Change this

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Set JSON header
header('Content-Type: application/json');

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

// Debug log
error_log("Login attempt - Email: " . $email);

// Validate CSRF (optional - remove if causing issues)
// if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
//     echo json_encode(['success' => false, 'message' => 'Invalid security token']);
//     exit;
// }

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter email and password']);
    exit;
}

// Find user by email
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Debug: Check if user exists
    error_log("User found: " . ($user ? 'Yes' : 'No'));
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // Debug: Show stored hash (first few chars)
    error_log("Stored hash: " . substr($user['password'], 0, 20) . "...");
    error_log("Entered password: " . $password);
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        error_log("Password verification failed");
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    error_log("Password verified successfully!");
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Store user in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    
    // Return success with user data
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'user' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'gender' => $user['gender'],
            'country' => $user['country'],
            'profile_image' => $user['profile_image'] ?? 'default.png',
            'role' => $user['role'],
            'created_at' => $user['created_at']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
}
?>
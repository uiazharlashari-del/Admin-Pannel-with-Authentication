<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Validate CSRF
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Get and sanitize data
$full_name = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$gender = sanitize($_POST['gender'] ?? '');
$country = sanitize($_POST['country'] ?? '');
$privacy_agreed = isset($_POST['privacy_agreed']) ? 1 : 0;

$errors = [];

// Server-side validation
if (strlen($full_name) < 3) {
    $errors['full_name'] = 'Full name must be at least 3 characters';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}

// Check email uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $errors['email'] = 'This email is already registered';
}

// Password validation
if (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one number';
} elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one special character';
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

if (!in_array($gender, ['male', 'female', 'other'])) {
    $errors['gender'] = 'Please select a valid gender';
}

if (empty($country)) {
    $errors['country'] = 'Please select your country';
}

if (!$privacy_agreed) {
    $errors['privacy'] = 'You must agree to the Privacy Policy';
}

// If validation errors, return them
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Handle file upload
$profile_image = 'default.png';
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $uploadResult = uploadFile($_FILES['profile_image']);
    if ($uploadResult['success']) {
        $profile_image = $uploadResult['filename'];
    } else {
        echo json_encode(['success' => false, 'errors' => ['profile_image' => $uploadResult['error']]]);
        exit;
    }
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert user
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password, gender, country, profile_image, privacy_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$full_name, $email, $hashedPassword, $gender, $country, $profile_image, $privacy_agreed]);
    
    $newUserId = $pdo->lastInsertId();
    
    // Get the newly created user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$newUserId]);
    $newUser = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please login.',
        'user' => $newUser
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ]);
}
?>
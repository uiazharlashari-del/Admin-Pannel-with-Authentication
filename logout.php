<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Get CSRF token from request body
$data = json_decode(file_get_contents('php://input'), true);
$csrf_token = $data['csrf_token'] ?? '';

if (!validateCSRFToken($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Destroy session
$_SESSION = [];
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>
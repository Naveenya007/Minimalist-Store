<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Only POST allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    json_response(['success' => false, 'message' => 'Invalid JSON'], 400);
}

$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($email) || empty($password)) {
    json_response(['success' => false, 'message' => 'Email and password required'], 400);
}

try {
    $db = store_db();
    
    // Find user by email
    $stmt = $db->prepare('SELECT id, email, password, name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'Email not found'], 401);
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        json_response(['success' => false, 'message' => 'Invalid password'], 401);
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    
    json_response(['success' => true, 'message' => 'Login successful!', 'user' => ['name' => $user['name'], 'email' => $user['email']]]);
} catch (Exception $e) {
    json_response(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()], 500);
}

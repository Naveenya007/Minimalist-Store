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
$name = trim($input['name'] ?? '');
$confirmPassword = trim($input['confirmPassword'] ?? '');

// Validation
if (empty($email) || empty($password) || empty($name) || empty($confirmPassword)) {
    json_response(['success' => false, 'message' => 'All fields are required'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Invalid email format'], 400);
}

if (strlen($password) < 6) {
    json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
}

if ($password !== $confirmPassword) {
    json_response(['success' => false, 'message' => 'Passwords do not match'], 400);
}

try {
    $db = store_db();
    
    // Check if email already exists
    $existingUser = $db->prepare('SELECT id FROM users WHERE email = ?');
    $existingUser->execute([$email]);
    
    if ($existingUser->fetch()) {
        json_response(['success' => false, 'message' => 'Email already registered'], 409);
    }
    
    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (email, password, name) VALUES (?, ?, ?)');
    $stmt->execute([$email, $hashedPassword, $name]);
    
    // Auto login after signup
    $_SESSION['user_id'] = $db->lastInsertId();
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    
    json_response(['success' => true, 'message' => 'Signup successful! Redirecting...']);
} catch (Exception $e) {
    json_response(['success' => false, 'message' => 'Signup failed: ' . $e->getMessage()], 500);
}

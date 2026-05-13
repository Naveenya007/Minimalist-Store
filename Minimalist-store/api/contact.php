<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode((string) file_get_contents('php://input'), true);
$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$message = trim((string) ($input['message'] ?? ''));

if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    json_response(['success' => false, 'message' => 'Please fill in all fields with a valid email address.'], 422);
}

$pdo = store_db();
$statement = $pdo->prepare('INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)');
$statement->execute([
    ':name' => $name,
    ':email' => $email,
    ':message' => $message,
]);

json_response(['success' => true, 'message' => 'Thanks. Your message has been saved.']);

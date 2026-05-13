<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    json_response(['success' => false, 'message' => 'You must be logged in to place an order'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode((string) file_get_contents('php://input'), true);
$items = $input['items'] ?? [];
$total = (int) ($input['total'] ?? 0);

if (!is_array($items) || $total <= 0) {
    json_response(['success' => false, 'message' => 'Please provide a valid order payload.'], 422);
}

$pdo = store_db();
$statement = $pdo->prepare('INSERT INTO orders (customer_name, email, items_json, total) VALUES (:customer_name, :email, :items_json, :total)');
$statement->execute([
    ':customer_name' => $_SESSION['user_name'],
    ':email' => $_SESSION['user_email'],
    ':items_json' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ':total' => $total,
]);

json_response(['success' => true, 'message' => 'Order saved successfully.']);

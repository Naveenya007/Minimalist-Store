<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$pdo = store_db();
$products = $pdo->query('SELECT id, name, price, image, description, featured FROM products ORDER BY featured DESC, id ASC')->fetchAll();

json_response(['success' => true, 'products' => $products]);

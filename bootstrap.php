<?php
declare(strict_types=1);

function store_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dataDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }

    $databasePath = $dataDir . DIRECTORY_SEPARATOR . 'store.sqlite';
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price INTEGER NOT NULL,
        image TEXT NOT NULL,
        description TEXT NOT NULL,
        featured INTEGER NOT NULL DEFAULT 0
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_name TEXT NOT NULL,
        email TEXT NOT NULL,
        items_json TEXT NOT NULL,
        total INTEGER NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        message TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        name TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($count === 0) {
        $seed = $pdo->prepare('INSERT INTO products (name, price, image, description, featured) VALUES (:name, :price, :image, :description, :featured)');
        $products = [
            ['Reusable Bottle', 499, 'images/bottle.jpg', 'A clean everyday bottle for low-waste living.', 1],
            ['Minimal Planner', 299, 'images/planner.jpg', 'A calm paper planner for focused days.', 1],
            ['Desk Organizer', 899, 'images/organizer.jpg', 'Keeps essentials tidy without visual clutter.', 1],
            ['Bamboo Toothbrush Set', 199, 'images/bamboo-brush.jpg', 'Eco-friendly oral care for daily use.', 0],
            ['Linen Cushion Cover', 699, 'images/linen-cushion.jpg', 'Neutral textures that soften the room.', 0],
            ['Ceramic Coffee Mug', 349, 'images/ceramic-mug.jpg', 'Simple form and a comfortable grip.', 0],
            ['Soy Wax Scented Candle', 399, 'images/soy-candle.jpg', 'A warm scent with a clean burn.', 0],
            ['Wooden Phone Stand', 249, 'images/wood-phone-stand.jpg', 'A minimal stand for desks and bedside tables.', 0],
            ['Cotton Tote Bag', 299, 'images/tote-bag.jpg', 'A reusable bag for everyday carrying.', 0],
            ['Minimal Wall Clock', 1199, 'images/wall-clock.jpg', 'A quiet clock with a timeless shape.', 0],
            ['Glass Storage Jars', 899, 'images/glass-jars.jpg', 'A set of jars to organize pantry basics.', 0],
            ['Linen Table Runner', 649, 'images/table-runner.jpg', 'An understated accent for the table.', 0],
            ['Ceramic Plant Pot', 549, 'images/plant-pot.jpg', 'Simple planter for desks and shelves.', 0],
            ['Natural Cotton Bath Towel', 799, 'images/bath-towel.jpg', 'Soft, absorbent, and easy to live with.', 0],
            ['Essential Oil Diffuser', 1499, 'images/diffuser.jpg', 'A compact diffuser for slow evenings.', 0],
            ['Minimal Desk Lamp', 1299, 'images/desk-lamp.jpg', 'Task lighting with a clean silhouette.', 0],
        ];

        foreach ($products as $product) {
            $seed->execute([
                ':name' => $product[0],
                ':price' => $product[1],
                ':image' => $product[2],
                ':description' => $product[3],
                ':featured' => $product[4],
            ]);
        }
    }

    return $pdo;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/bootstrap.php';

if (isset($_SESSION['user_id'])) {
    json_response([
        'success' => true,
        'loggedIn' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ]
    ]);
} else {
    json_response(['success' => true, 'loggedIn' => false, 'user' => null]);
}

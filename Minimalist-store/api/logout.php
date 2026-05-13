<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/bootstrap.php';

// Destroy session
session_destroy();

json_response(['success' => true, 'message' => 'Logged out successfully']);

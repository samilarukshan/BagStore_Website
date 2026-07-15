<?php

// Database config
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'bagstore');
define('DB_USER', 'bagstoreuser');
define('DB_PASS', 'bagstore');

try {
    // Create PDO connection with port
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );

    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die(json_encode([
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]));
}
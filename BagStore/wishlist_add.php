<?php
session_start();
require_once 'db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Please login first']);
    exit();
}

$userId = $_SESSION['user_id'];
$itemId = $_POST['id'] ?? 0;

// Validate input
if (!$itemId) {
    echo json_encode(['message' => 'Invalid item']);
    exit();
}

try {
    // Insert (prevent duplicates using UNIQUE constraint)
    $stmt = $pdo->prepare("
        INSERT INTO wishlist (user_id, item_id)
        VALUES (:user_id, :item_id)
    ");

    $stmt->execute([
        'user_id' => $userId,
        'item_id' => $itemId
    ]);

    echo json_encode(['message' => 'Added to wishlist ❤️']);

} catch (PDOException $e) {

    // Duplicate entry (already added)
    if ($e->getCode() == 23000) {
        echo json_encode(['message' => 'Already in wishlist ❤️']);
    } else {
        echo json_encode(['message' => 'Something went wrong']);
    }
}
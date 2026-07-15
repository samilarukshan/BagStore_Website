<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];
$bagId = $_POST['id'] ?? 0;



$stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND item_id = :bag_id");

$stmt->execute([
    'user_id' => $userId,
    'bag_id' => $bagId
]);

echo json_encode(['message' => 'Removed from wishlist']);
<?php
session_start();
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    die("Необхідно увійти.");
}

$userId = $_SESSION["user_id"];
$productId = $_POST["product_id"];
$rating = (int) $_POST["rating"];
$comment = trim($_POST["comment"]);

// Перевірка
if ($rating < 1 || $rating > 5 || empty($comment)) {
    die("Невірні дані.");
}

// Додати до БД
$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiis", $productId, $userId, $rating, $comment);
$stmt->execute();

header("Location: product.php?id=$productId");
exit;
?>

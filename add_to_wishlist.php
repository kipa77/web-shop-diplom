<?php
session_start();
include 'config.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["product_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$productId = (int) $_POST["product_id"];

// Перевірка чи вже є в списку
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
}

header("Location: wishlist.php");
exit;

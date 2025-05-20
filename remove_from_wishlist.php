<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Користувач не авторизований";
    exit;
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    http_response_code(400);
    echo "Некоректний запит";
    exit;
}

$userId = $_SESSION['user_id'];
$productId = (int) $_POST['product_id'];

// Видалення товару зі списку бажань
$query = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $productId);

if ($stmt->execute()) {
    // Перенаправлення на wishlist.php після успішного видалення
    header("Location: wishlist.php");
    exit;
} else {
    http_response_code(500);
    echo "Помилка видалення";
    exit;
}



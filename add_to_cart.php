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

// Перевірка, чи товар вже в корзині
$checkQuery = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    http_response_code(500);
    echo "Помилка перевірки товару";
    exit;
}

if ($result->num_rows > 0) {
    // Оновлення кількості
    $updateQuery = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $userId, $productId);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo "Помилка оновлення товару";
        exit;
    }
} else {
    // Додавання нового товару
    $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ii", $userId, $productId);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo "Помилка додавання товару";
        exit;
    }
}

// Видалення товару зі списку бажань
$deleteQuery = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute(); // навіть якщо видалення не вдалось — не критично

// Успіх — перенаправлення на сторінку корзини
header("Location: cart.php");
exit;
?>

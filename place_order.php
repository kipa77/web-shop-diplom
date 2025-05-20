<?php
session_start();
include 'config.php';

// Перевірка авторизації
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$deliveryPointId = isset($_POST["delivery_point_id"]) ? (int)$_POST["delivery_point_id"] : 0;

// Отримати товари з корзини
$stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cartItems) || $deliveryPointId === 0) {
    die("Немає товарів у кошику або не обрано пункт доставки.");
}

// Отримати місто за delivery_point_id
$stmt = $conn->prepare("SELECT city FROM delivery_points WHERE id = ?");
$stmt->bind_param("i", $deliveryPointId);
$stmt->execute();
$cityResult = $stmt->get_result();

if ($cityResult->num_rows === 0) {
    die("Пункт доставки не знайдено.");
}
$city = $cityResult->fetch_assoc()['city'];

// Отримати ціну доставки з таблиці delivery_prices_by_city
$stmt = $conn->prepare("SELECT delivery_price FROM delivery_prices_by_city WHERE city = ?");
$stmt->bind_param("s", $city);
$stmt->execute();
$priceResult = $stmt->get_result();

if ($priceResult->num_rows === 0) {
    die("Ціна доставки для цього міста не знайдена.");
}
$deliveryPrice = $priceResult->fetch_assoc()['delivery_price'];

// Розрахунок загальної ціни товарів
$totalPrice = 0;
foreach ($cartItems as $item) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $item["product_id"]);
    $stmt->execute();
    $price = $stmt->get_result()->fetch_assoc()["price"];
    $totalPrice += $price * $item["quantity"];
}

// Створення замовлення
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status, created_at) VALUES (?, ?, 'очікується', NOW())");
$stmt->bind_param("id", $userId, $totalPrice);
$stmt->execute();
$orderId = $stmt->insert_id;

// Додати інформацію про доставку
$stmt = $conn->prepare("INSERT INTO order_delivery (order_id, delivery_point_id, delivery_price, delivery_status, created_at) VALUES (?, ?, ?, 'очікується', NOW())");
$stmt->bind_param("iid", $orderId, $deliveryPointId, $deliveryPrice);
$stmt->execute();

// Очистити кошик
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

// Перенаправлення на сторінку успішного замовлення
header("Location: order_success.php?order_id=" . $orderId);
exit;
?>

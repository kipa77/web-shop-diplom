<?php
// get_orders.php
include 'config.php';
header('Content-Type: application/json');

// Очікуємо email в GET
if (!isset($_GET['email'])) {
    echo json_encode(['error' => 'Не вказано email']);
    exit;
}

$email = $_GET['email'];

// Отримати user_id по email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$userId = $userResult->fetch_assoc()['id'];

// Отримати замовлення користувача
$stmt = $conn->prepare("SELECT id, status, created_at FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);

<?php
// get_order_status.php
include 'config.php';
header('Content-Type: application/json');

if (!isset($_GET['order_id'])) {
    echo json_encode(['error' => 'Не вказано ID замовлення']);
    exit;
}

$orderId = (int)$_GET['order_id'];

$query = "
SELECT 
    o.id, o.status, o.total_price, o.created_at,
    od.delivery_status, dp.city, dp.address, dp.service_name
FROM orders o
LEFT JOIN order_delivery od ON o.id = od.order_id
LEFT JOIN delivery_points dp ON od.delivery_point_id = dp.id
WHERE o.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Замовлення не знайдено']);
    exit;
}

echo json_encode($result->fetch_assoc());

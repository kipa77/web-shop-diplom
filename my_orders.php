<?php
session_start();
include 'config.php';

// Перевірка авторизації
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Запит на отримання замовлень користувача з деталями доставки
$query = "
    SELECT 
    o.id AS order_id,
    o.total_price,
    o.status AS order_status,
    o.created_at AS order_date,
    dpc.delivery_price,
    od.delivery_status,
    dp.service_name,
    dp.city,
    dp.address,
    dp.point_number
FROM orders o
LEFT JOIN order_delivery od ON o.id = od.order_id
LEFT JOIN delivery_points dp ON od.delivery_point_id = dp.id
LEFT JOIN delivery_prices_by_city dpc ON dp.city = dpc.city
WHERE o.user_id = ?
ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мої замовлення</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>

<div class="orders">
    <h1>Мої замовлення</h1>

    <?php if (empty($orders)): ?>
        <p>У вас ще немає замовлень.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <h3>Замовлення #<?= $order['order_id'] ?> (<?= htmlspecialchars($order['order_status']) ?>)</h3>
                <p><strong>Дата замовлення:</strong> <?= $order['order_date'] ?></p>
                <p><strong>Сума товарів:</strong> <?= number_format($order['total_price'], 2) ?> $</p>
                <p><strong>Ціна доставки:</strong> <?= number_format($order['delivery_price'], 2) ?> $</p>
                <p><strong>Статус доставки:</strong> <?= htmlspecialchars($order['delivery_status']) ?></p>

                <h4>Пункт доставки:</h4>
                <p><?= htmlspecialchars($order['service_name']) ?>, м. <?= htmlspecialchars($order['city']) ?>,</p>
                <p><?= htmlspecialchars($order['address']) ?> (№<?= htmlspecialchars($order['point_number']) ?>)</p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div>
        <button onclick="window.location.href='account.php'" class="btn">← Назад до акаунту</button>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

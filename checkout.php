<?php
session_start();
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

// Отримати товари з корзини
$query = "
    SELECT 
        p.id, p.name, p.price, c.quantity,
        (p.price * c.quantity) AS total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Помилка підготовки запиту: " . $conn->error);
}

$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    die("Помилка виконання запиту: " . $stmt->error);
}

$result = $stmt->get_result();
$cartItems = [];
$totalSum = 0;
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $totalSum += $row["total"];
}

// Отримати список унікальних міст
$citiesResult = $conn->query("SELECT DISTINCT city FROM delivery_points ORDER BY city");
if ($citiesResult === false) {
    die("Помилка запиту до міст: " . $conn->error);
}
$cities = $citiesResult->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function fetchPoints(city) {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get_points.php?city=" + encodeURIComponent(city), true);
            xhr.onload = function () {
                document.getElementById("delivery_point_select").innerHTML = this.responseText;
                fetchPrice(city);
            };
            xhr.send();
        }

        function fetchPrice(city) {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get_delivery_price.php?city=" + encodeURIComponent(city), true);
            xhr.onload = function () {
                document.getElementById("delivery_price").textContent = this.responseText + " $";
            };
            xhr.send();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1 class="checkout-title">Оформлення замовлення</h1>

        <div class="checkout-container">
            <?php if (empty($cartItems)): ?>
                <p class="empty-checkout-message">Корзина порожня.</p>
                <a href="index.php" class="continue-shopping">← Продовжити покупки</a>
            <?php else: ?>
                <form method="post" action="place_order.php" class="checkout-form">
                    <h3 class="section-title">Ваше замовлення:</h3>
                    <ul class="order-list">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="order-item">
                                <?= htmlspecialchars($item['name']) ?> — 
                                <span class="quantity"><?= $item['quantity'] ?> шт.</span> 
                                (<?= number_format($item['total'], 2) ?> $)
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <p class="total-sum"><strong>Загальна сума товарів:</strong> <?= number_format($totalSum, 2) ?> $</p>

                    <h3 class="section-title">Оберіть місто доставки:</h3>
                    <select name="city" class="city-select" onchange="fetchPoints(this.value)" required>
                        <option value="">-- Виберіть місто --</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city['city']) ?>"><?= htmlspecialchars($city['city']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <h3 class="section-title">Оберіть пункт доставки:</h3>
                    <select name="delivery_point_id" id="delivery_point_select" class="point-select" required>
                        <option value="">-- Спочатку оберіть місто --</option>
                    </select>

                    <p class="delivery-cost"><strong>Ціна доставки:</strong> <span id="delivery_price">0.00 $</span></p>

                    <button type="submit" class="checkout-button">Оформити замовлення</button>
                </form>

                <a href="index.php" class="continue-shopping">← Продовжити покупки</a>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
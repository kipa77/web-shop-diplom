<?php
session_start();
include 'config.php';

$notLoggedIn = false;

if (!isset($_SESSION["user_id"])) {
    $notLoggedIn = true;
} else {
    $userId = $_SESSION["user_id"];

    // Отримання товарів у корзині
    $query = "
        SELECT 
            p.id, 
            p.name, 
            p.price, 
            c.quantity,
            (p.price * c.quantity) AS total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = [];
    $totalSum = 0;

    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $totalSum += $row["total"];
    }
}


// Отримання товарів у корзині
$query = "
    SELECT 
        p.id, 
        p.name, 
        p.price, 
        c.quantity,
        (p.price * c.quantity) AS total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$totalSum = 0;

while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $totalSum += $row["total"];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 class="cart-title">Ваш кошик</h1>
    <div class="cart-container">
    <?php if ($notLoggedIn): ?>
        <p class="empty-cart-message">Будь ласка, увійдіть у систему, щоб переглянути кошик.</p>
    <?php elseif (empty($cartItems)): ?>
        <p class="empty-cart-message">Кошик порожній.</p>
    <?php else: ?>
        <table class="cart-table">
    <thead>
        <tr>
            <th class="cart-header">Назва</th>
            <th class="cart-header">Ціна</th>
            <th class="cart-header">Кількість</th>
            <th class="cart-header">Сума</th>
            <th class="cart-header">Дії</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cartItems as $item): ?>
            <tr class="cart-row">
                <td class="cart-cell" data-label="Назва"><?= htmlspecialchars($item["name"]) ?></td>
                <td class="cart-cell" data-label="Ціна"><?= number_format($item["price"], 2) ?> $</td>
                <td class="cart-cell" data-label="Кількість"><?= htmlspecialchars($item["quantity"]) ?></td>
                <td class="cart-cell" data-label="Сума"><?= number_format($item["total"], 2) ?> $</td>
                <td class="cart-cell" data-label="Дії">
                    <form action="update_cart.php" method="post" class="cart-action-form">
                        <input type="hidden" name="product_id" value="<?= $item["id"] ?>">
                        <button class="cart-btn" name="action" value="increase">+</button>
                        <button class="cart-btn" name="action" value="decrease">−</button>
                        <button class="cart-btn" name="action" value="remove">Видалити</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
            <h3 class="cart-total">Загальна сума: <?= number_format($totalSum, 2) ?> $</h3>
        <?php endif; ?>
        <?php if (!empty($cartItems)): ?>
    <form action="checkout.php" method="get">
        <button type="submit" class="checkout-button">Оформити замовлення</button>
    </form>
<?php endif; ?>

        <a class="continue-shopping" href="index.php">← Продовжити покупки</a>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
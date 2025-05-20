<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Отримати товари у списку бажань
$query = "
    SELECT p.id, p.name, p.price,
        (SELECT image_data FROM product_images WHERE product_id = p.id LIMIT 1) AS image_data
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
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
$items = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список бажань</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1 class="wishlist-title">Мій список бажань (<?= count($items) ?>)</h1>

        <div class="wishlist-container">
            <?php if (empty($items)): ?>
                <p class="empty-wishlist-message">Список бажань порожній.</p>
                <a href="index.php" class="continue-shopping">← Продовжити покупки</a>
            <?php else: ?>
                <ul class="wishlist-items">
                    <?php foreach ($items as $item): ?>
                        <li class="wishlist-item">
                            <div class="item-img">
                                <img src="<?= !empty($item['image_data']) ? 'data:image/jpeg;base64,' . base64_encode($item['image_data']) : 'default.png' ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="wishlist-img">
                            </div>
                            <div class="item-details">
                                <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="item-price"><?= number_format($item['price'], 2) ?> $</span>
                                <div class="wishlist-actions">
                                    <form method="post" action="add_to_cart.php" class="wishlist-action-form">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="wishlist-btn add-to-cart-btn">Додати до корзини</button>
                                    </form>
                                    <form method="post" action="remove_from_wishlist.php" class="wishlist-action-form">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="wishlist-btn remove-btn">Видалити</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="account.php" class="back-to-account">← Назад до акаунту</a>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
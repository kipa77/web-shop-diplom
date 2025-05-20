<?php
session_start();
include 'config.php';

// Перевірка, чи передано ID товару та чи це число
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Некоректний ідентифікатор товару.");
}

$productId = (int) $_GET['id'];

// Отримання основної інформації про товар
$productQuery = "SELECT id, name, price, description FROM products WHERE id = ?";
$stmt = $conn->prepare($productQuery);
$stmt->bind_param("i", $productId);
$stmt->execute();
$productResult = $stmt->get_result();

if (!$productResult || $productResult->num_rows === 0) {
    die("Товар не знайдено.");
}

$product = $productResult->fetch_assoc();

// Отримання всіх зображень товару
$imageQuery = "SELECT image_data FROM product_images WHERE product_id = ?";
$stmt = $conn->prepare($imageQuery);
$stmt->bind_param("i", $productId);
$stmt->execute();
$imageResult = $stmt->get_result();

$images = [];
while ($row = $imageResult->fetch_assoc()) {
    $images[] = 'data:image/jpeg;base64,' . base64_encode($row['image_data']);
}

// Якщо немає зображень — використати зображення за замовчуванням
if (empty($images)) {
    $images[] = 'default.png';
}
// Отримати відгуки
$reviewQuery = "
    SELECT r.rating, r.comment, r.created_at, u.username
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($reviewQuery);
$stmt->bind_param("i", $productId);
$stmt->execute();
$reviewResult = $stmt->get_result();

$reviews = [];
while ($row = $reviewResult->fetch_assoc()) {
    $reviews[] = $row;
}

?>


<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product["name"]) ?></title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">

</head>
<body>

    <div class="back">
    <button class="btn_back" onclick="window.location.href='index.php'">Назад</button>
    </div>
    

    <div class="product">

        <!-- Вкладки -->
        <div class="tabs">
            <button class="tab-button active" onclick="showTab('about')">Про товар</button>
            <button class="tab-button" onclick="showTab('specs')">Характеристики</button>
            <button class="tab-button" onclick="showTab('reviews')">Відгуки</button>
        </div>

        <!-- Вкладка: Усе про товар -->
        <div class="tab-content" id="about">
            <h3>Про товар</h3>
            <div class="product_info">
                <!-- Основне зображення -->
                <img id="mainImage" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product["name"]) ?>" class="product_img">

                <!-- Мініатюри -->
                <div class="thumbnails">
                    <?php foreach ($images as $index => $src): ?>
                        <img 
                            src="<?= htmlspecialchars($src) ?>" 
                            alt="Зображення <?= $index + 1 ?>" 
                            onclick="changeImage(this)" 
                            class="<?= $index === 0 ? 'active' : '' ?>"
                        >
                    <?php endforeach; ?>    
                </div>

                <!-- Деталі товару -->
                <div class="product_details">
                    <h2><?= htmlspecialchars($product["name"]) ?></h2>
                    <p>Ціна: <?= number_format($product["price"], 2) ?> $</p>
                    
                    <button class="btn_add" id="addToCart">Додати до корзини</button>
                </div>
                <form action="add_to_wishlist.php" method="post">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button class="wishlist_btn "type="submit">У бажане ❤️</button>
                </form>
            </div>
        </div>

        <!-- Вкладка: Характеристики -->
        <div class="tab-content" id="specs" style="display: none;">
            <h3>Характеристики</h3>
            <p><?= nl2br(htmlspecialchars($product["description"])) ?></p>
        </div>

        <!-- Вкладка: Відгуки -->
<div class="tab-content" id="reviews" style="display: none;">
    <h3>Відгуки</h3>

    <?php if (empty($reviews)): ?>
        <p>Відгуків поки немає.</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review">
                <strong><?= htmlspecialchars($review['username']) ?></strong>
                <span>(<?= htmlspecialchars($review['created_at']) ?>)</span><br>
                <span>Оцінка: <?= str_repeat("⭐", (int)$review['rating']) ?></span>
                <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr>

    <?php if (isset($_SESSION["user_id"])): ?>
        <h4>Залишити відгук</h4>
        <form method="post" action="submit_review.php">
            <input type="hidden" name="product_id" value="<?= $productId ?>">
            <label>Оцінка:
                <select name="rating" required>
                    <option value="5">5 - Супер</option>
                    <option value="4">4 - Добре</option>
                    <option value="3">3 - Нормально</option>
                    <option value="2">2 - Слабо</option>
                    <option value="1">1 - Жахливо</option>
                </select>
            </label><br>
            <label>Коментар:<br>
                <textarea name="comment" rows="4" cols="50" required></textarea>
            </label><br>
            <button type="submit" class="btn">Надіслати</button>
        </form>
    <?php else: ?>
        <p>Щоб залишити відгук, будь ласка, <a href="login.php">увійдіть у систему</a>.</p>
    <?php endif; ?>
</div>

    </div>

    <?php include 'footer.php'; ?>

    
    
    <script>
         function changeImage(element) {
            document.getElementById('mainImage').src = element.src;
            document.querySelectorAll('.thumbnails img').forEach(img => img.classList.remove('active'));
            element.classList.add('active');
        }

        function showTab(tabId) {
            // Приховати всі вкладки
            document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

            // Показати вибрану
            document.getElementById(tabId).style.display = 'block';
            event.target.classList.add('active');
            
        }
       document.getElementById("addToCart").addEventListener("click", function () {
    fetch("add_to_cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "product_id=<?= $productId ?>"
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                alert("Ви повинні увійти, щоб додати товар до корзини.");
                throw new Error("Unauthorized");
            } else {
                throw new Error("Помилка при додаванні до корзини.");
            }
        }
        return response.json();
    })
    .then(data => {
        if(data.status === 'success') {
            alert(data.message);
        } else {
            alert("Сталася помилка: " + data.message);
        }
    })
    .catch(error => {
        if(error.message !== "Unauthorized") {
            alert(error.message);
        }
        console.error(error);
    });
});
    </script>
</body>
</html>

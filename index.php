<?php 
include 'config.php';

// Перевірка підключення
if ($conn->connect_error) {
    die("Помилка з'єднання з базою: " . $conn->connect_error);
}

// Масиви для умов, параметрів та типів
$conditions = [];
$params = [];
$types = "";

// Мінімальна ціна
if (!empty($_GET["min_price"])) {
    $conditions[] = "p.price >= ?";
    $params[] = $_GET["min_price"];
    $types .= "d";
}

// Максимальна ціна
if (!empty($_GET["max_price"])) {
    $conditions[] = "p.price <= ?";
    $params[] = $_GET["max_price"];
    $types .= "d";
}

// Пошук по назві
if (!empty($_GET["search"])) {
    $conditions[] = "p.name LIKE ?";
    $params[] = '%' . $_GET["search"] . '%';
    $types .= "s";
}

// Фільтр за категоріями
if (!empty($_GET["category_id"]) && is_array($_GET["category_id"])) {
    $placeholders = implode(',', array_fill(0, count($_GET["category_id"]), '?'));
    $conditions[] = "p.category_id IN ($placeholders)";
    foreach ($_GET["category_id"] as $cat_id) {
        $params[] = $cat_id;
        $types .= "i";
    }
}

// Побудова основного запиту
$query = "
    SELECT p.id, p.name, p.price,
        (SELECT image_data FROM product_images WHERE product_id = p.id LIMIT 1) AS image_data
    FROM products p
";
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Сортування
$sortOption = $_GET['sort'] ?? '';
$allowedSorts = [
    'price_desc' => 'p.price DESC',
    'price_asc' => 'p.price ASC',
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC',
];

if (isset($allowedSorts[$sortOption])) {
    $query .= " ORDER BY " . $allowedSorts[$sortOption];
} else {
    $query .= " ORDER BY p.id DESC"; // За замовчуванням — нові спочатку
}

// Отримання категорій
$categories = [];
$categoryQuery = "SELECT id, name FROM categories";
$categoryResult = $conn->query($categoryQuery);
if ($categoryResult) {
    while ($cat = $categoryResult->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// Кількість товарів по категоріях
$categoryCounts = [];
$countQuery = "SELECT category_id, COUNT(*) as count FROM products GROUP BY category_id";
$countResult = $conn->query($countQuery);
if ($countResult) {
    while ($row = $countResult->fetch_assoc()) {
        $categoryCounts[$row['category_id']] = $row['count'];
    }
}

// Підготовка запиту
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Помилка запиту: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список товарів</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <?php include 'tg_script.php'; ?>
    
</head>
<body>

<div class="container">
    <button class="filter-button" id="filterButton">Фільтр</button>

    <div class="search-container">
        <button class="search-toggle" id="searchToggle">🔍</button>
        <form method="GET" class="search-form" id="searchForm">
            <input type="text" name="search" placeholder="Пошук..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
    </div>

    <!-- Сортування -->
    <form method="GET" style="margin-bottom: 10px;">
        <?php foreach ($_GET as $key => $value): ?>
            <?php if ($key !== 'sort') {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($v) . '">';
                    }
                } else {
                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                }
            } ?>
        <?php endforeach; ?>
        <label for="sort">Сортувати за:</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="">— Обрати —</option>
            <option value="price_asc" <?= $sortOption == 'price_asc' ? 'selected' : '' ?>>Ціна ↑</option>
            <option value="price_desc" <?= $sortOption == 'price_desc' ? 'selected' : '' ?>>Ціна ↓</option>
            <option value="name_asc" <?= $sortOption == 'name_asc' ? 'selected' : '' ?>>Назва А-Я</option>
            <option value="name_desc" <?= $sortOption == 'name_desc' ? 'selected' : '' ?>>Назва Я-А</option>
        </select>
    </form>

    <!-- Список товарів -->
    <div class="boxs">
        <?php   
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $imageSrc = !empty($row["image_data"]) 
                    ? 'data:image/jpeg;base64,' . base64_encode($row["image_data"]) 
                    : 'default.png'; 

                echo '
                <a href="product.php?id=' . $row["id"] . '" class="item-link">
                    <div class="item">
                        <div class="item_img">
                            <img src="' . $imageSrc . '" alt="' . htmlspecialchars($row["name"]) . '" class="img">
                        </div>
                        <div class="item_info">
                            <div class="item_name">' . htmlspecialchars($row["name"]) . '</div>
                            <div class="item_cost">' . number_format($row["price"], 2) . ' $</div>
                            <div class="item_btn">
                                <button class="btn" id="btn' . $row["id"] . '">Додати</button>
                            </div>
                        </div>
                    </div>
                </a>';
            }
        } else {
            echo "<p>Товари не знайдено.</p>";
        }

        $conn->close();
        ?>
    </div>
</div>

<!-- Панель фільтрів -->
<div class="filter" id="filterPanel">
    <form method="GET" class="filter-form">
        <div class="price_filter">
            <label>Ціна від: <input type="number" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>"></label><br>
            <label>Ціна до: <input type="number" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>"></label>
        </div>

        <div class="filter-section">
            <strong>Категорії:</strong><br>
            <?php foreach ($categories as $category): ?>
                <label>
                    <input type="checkbox" name="category_id[]" value="<?= $category['id'] ?>" 
                        <?= (in_array($category['id'], $_GET['category_id'] ?? []) ? 'checked' : '') ?>>
                    <?= htmlspecialchars($category['name']) ?> 
                    (<?= $categoryCounts[$category['id']] ?? 0 ?>)
                </label><br>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn">Застосувати</button>
        <a href="?" class="btn">Очистити</a>
    </form>
</div>

<!-- Кнопка "вгору" -->
<button onclick="scrollToTop()" id="backToTop" title="Вгору">⬆</button>

<?php include 'footer.php'; ?>

<script>
    // Показ/приховування форми пошуку
    const searchToggle = document.getElementById('searchToggle');
    const searchForm = document.getElementById('searchForm');

    searchToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        searchForm.style.display = searchForm.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
        if (!searchForm.contains(e.target) && !searchToggle.contains(e.target)) {
            searchForm.style.display = 'none';
        }
    });

    // Показ/приховування фільтра
    const filterButton = document.getElementById('filterButton');
    const filterPanel = document.getElementById('filterPanel');

    filterButton.addEventListener('click', () => {
        filterPanel.classList.toggle('open');
    });

    document.addEventListener('click', (event) => {
        if (!filterButton.contains(event.target) && !filterPanel.contains(event.target)) {
            filterPanel.classList.remove('open');
        }
    });

    // Кнопка "вгору"
    window.onscroll = function () {
        const btn = document.getElementById("backToTop");
        btn.style.display = window.scrollY > 300 ? "block" : "none";
    };

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
<script>
   document.querySelectorAll(".item_btn .btn").forEach(button => {
    button.addEventListener("click", function (event) {
        event.preventDefault(); 

        const productId = this.id.replace("btn", "");

        fetch("add_to_cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "product_id=" + encodeURIComponent(productId)
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    alert("Ви повинні увійти, щоб додати товар до корзини.");
                    // При бажанні — перенаправлення на логін
                    // window.location.href = "login.php";
                    throw new Error("Unauthorized");
                } else {
                    throw new Error("Помилка при додаванні до корзини.");
                }
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
            } else {
                alert("Сталася помилка: " + data.message);
            }
        })
        .catch(error => {
            if (error.message !== "Unauthorized") {
                alert(error.message);
            }
            console.error(error);
        });
    });
});

</script>

</body>
</html>

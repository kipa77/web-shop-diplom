<?php
$orderId = (int) $_GET["order_id"];
echo "<h2>Дякуємо! Ваше замовлення №$orderId прийнято.</h2>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

</head>
<body>
    <button class="btn_back">
        <a href="index.php">Повернутися до магазину</a>
    </button>
</body>
</html>
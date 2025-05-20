<?php
session_start();
include 'config.php';

// Перевірка, чи користувач увійшов в систему
$loggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Account</title>
</head>
<body>
<div class="account">
    <h1>Інформація об аккаунті</h1>
    
    <?php if ($loggedIn): ?>
    <!-- Якщо користувач увійшов -->
    <div class="info">
        <strong>Ім'я:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?>
        <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?>
        <strong>Номер телефону:</strong> <?php echo htmlspecialchars($_SESSION['phone']); ?>
    </div>
    <div>
        <button class="acc_btn" onclick="window.location.href='my_orders.php'">Мої замовлення</button>
    </div>
     <div>
        <button class="acc_btn" onclick="window.location.href='wishlist.php'">Список бажань</button>
    </div>
    <div>
        <form action="logout.php" method="post">
            <button class="acc_btn logout-btn">Вийти</button>
        </form>
    </div>
    <?php else: ?>
        <!-- Якщо користувач НЕ увійшов -->
        <div class="acc_unk">
            <p>Увійдіть до свого аккунта або зареєструйтесь</p>
            <div>
                <button class="acc_btn" onclick="window.location.href='register.php'">Реєстрація</button>
            </div>
            <div>
                <button class="acc_btn" onclick="window.location.href='login.php'">Увійти</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>


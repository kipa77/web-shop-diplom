<?php
session_start();
include 'config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Підготовлений запит для перевірки користувача
    $stmt = $conn->prepare("SELECT id, username, email, phone, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Перевірка пароля
        if (password_verify($password, $row['password'])) {
            // Створення сесії
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];  // Зберігаємо email
            $_SESSION['phone'] = $row['phone'];  // Зберігаємо телефон
            $_SESSION['user_id'] = $row['id'];  // Зберігаємо id користувача
            header("Location: account.php"); // Перенаправлення на сторінку акаунту
            exit();
        } else {
            echo "Невірний пароль!";
        }
    } else {
        echo "Користувача не знайдено!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="back">
        <button class="btn_back"  onclick="window.location.href='account.php'">Назад</button>
    </div>
    <div class="form">
        <h1>Увійти</h1>
        <form method="POST" action="login.php">
            <label for="username">Ім'я:</label><br>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Пароль:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" value="Увійти">
        </form>
    </div>
</body>

</html>

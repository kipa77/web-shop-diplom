<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone = $_POST['phone']; // Замінили 'adress' на 'phone'

    // Перевірка, чи всі поля заповнені
    if (!empty($username) && !empty($password) && !empty($email) && !empty($phone)) {
        // Хешуємо пароль для безпеки
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Підготовка та прив'язка SQL запиту для вставки даних
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $phone);

        // Виконання запиту та перевірка успішності
        if ($stmt->execute()) {
            echo "User registered successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Закриваємо запит
        $stmt->close();
    } else {
        echo "Please fill all fields.";
    }
}


$conn->close();
?>

    <!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="back">
    <button class="btn_back"  onclick="window.location.href='account.php'">Назад</button>
</div>

<div class="form">
    <h1>Реєстрація</h1>
    <form method="POST" action="register.php">
        <label for="username">Ім'я:</label><br>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Пароль:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="email">Пошта:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="phone">Номер телефону:</label><br> 
        <input type="text" id="phone" name="phone" required><br><br>

        <input type="submit" value="Зареєструватись">
    </form>
</div>

</body>

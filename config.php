<?php
$servername = "localhost"; // Адреса сервера бази даних
$username = "root";        // Ім'я користувача бази даних (зазвичай root для XAMPP)
$password = "";            // Пароль (порожній за замовчуванням для XAMPP)
$dbname = "shop_db";       // Назва вашої бази даних

// Створюємо з'єднання
$conn = new mysqli($servername, $username, $password, $dbname);

// Перевірка з'єднання
if ($conn->connect_error) {
    die("Помилка підключення до бази даних: " . $conn->connect_error);
}
?>

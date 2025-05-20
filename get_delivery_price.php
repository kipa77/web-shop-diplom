<?php
include 'config.php';

if (isset($_GET['city'])) {
    $city = $_GET['city'];
    $stmt = $conn->prepare("SELECT delivery_price FROM delivery_prices_by_city WHERE city = ?");
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo $result ? number_format($result['delivery_price'], 2) : "0.00";
}
<?php
session_start();
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    die("Потрібен вхід.");
}

$userId = $_SESSION["user_id"];
$productId = (int) $_POST["product_id"];
$action = $_POST["action"] ?? "";

switch ($action) {
    case "increase":
        $query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        break;
    case "decrease":
        $query = "UPDATE cart SET quantity = GREATEST(quantity - 1, 1) WHERE user_id = ? AND product_id = ?";
        break;
    case "remove":
        $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        break;
    default:
        die("Невірна дія.");
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();

header("Location: cart.php");
exit;

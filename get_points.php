<?php
include 'config.php';

if (isset($_GET['city'])) {
    $city = $_GET['city'];
    $stmt = $conn->prepare("SELECT id, service_name, address, point_number FROM delivery_points WHERE city = ?");
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>"
            . htmlspecialchars("{$row['service_name']} - {$row['address']} (№{$row['point_number']})")
            . "</option>";
    }
}

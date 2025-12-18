<?php
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

if ($action === "read") {
    $sql = "SELECT p.*, 
                   b.CheckInDate, b.CheckOutDate,
                   t.FullName AS TenantName
            FROM payment p
            LEFT JOIN booking b ON p.BookingID = b.BookingID
            LEFT JOIN tenant t ON b.TenantID = t.TenantID
            ORDER BY p.PaymentID DESC";

    $result = $conn->query($sql);
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
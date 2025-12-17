<?php
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

if ($action === "read") {
    $sql = "SELECT b.*, 
                   t.FullName AS TenantName,
                   t.Email AS TenantEmail
            FROM booking b
            LEFT JOIN tenant t ON b.TenantID = t.TenantID
            ORDER BY b.BookingDate DESC";

    $result = $conn->query($sql);
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
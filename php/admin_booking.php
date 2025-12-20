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

if ($action === "delete" && $_SERVER['REQUEST_METHOD'] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $bookingID = $input['bookingID'] ?? null;

    if (!$bookingID || !is_numeric($bookingID)) {
        echo json_encode(["success" => false, "message" => "Invalid Booking ID"]);
        exit;
    }

    // Optional: Add any cleanup logic here (e.g., cancel related payments, notifications, etc.)

    $sql = "DELETE FROM booking WHERE BookingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingID);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Booking deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Booking not found"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
    exit;
}

echo json_encode(["error" => "Invalid action"]);
?>
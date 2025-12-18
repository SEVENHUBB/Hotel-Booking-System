<?php
session_start();
include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    die("Please login first.");
}

$booking_id = $_GET['id'] ?? null;
if (!$booking_id) {
    die("Invalid request.");
}

// 删除指定booking
$stmt = $conn->prepare("DELETE FROM booking WHERE BookingID=? AND TenantID=?");
$stmt->bind_param("ii", $booking_id, $tenant_id);
$stmt->execute();

header("Location: cart.php");
exit();

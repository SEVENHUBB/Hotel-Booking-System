<?php
session_start();
include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    die("Please login first.");
}

$payment_method = $_POST['payment_method'] ?? '';
$total_amount = $_POST['total_amount'] ?? 0;

if (!$payment_method) {
    die("Please select a payment method.");
}

// 查询当前用户所有未付款订单
$sql = "SELECT BookingID, HotelID, RoomType, RoomQuantity, CheckInDate, CheckOutDate 
        FROM booking 
        WHERE TenantID=? AND Status='UNPAID'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No unpaid bookings found.");
}

while ($row = $result->fetch_assoc()) {
    $checkin = new DateTime($row['CheckInDate']);
    $checkout = new DateTime($row['CheckOutDate']);
    $days = max(1, $checkin->diff($checkout)->days);

    // 获取房价
    $r = $conn->prepare("SELECT RoomPrice FROM room WHERE HotelID=? AND RoomType=?");
    $r->bind_param("is", $row['HotelID'], $row['RoomType']);
    $r->execute();
    $rp = $r->get_result()->fetch_assoc();
    $subtotal = $rp['RoomPrice'] * $days * $row['RoomQuantity'];

    // 插入 payment 表
    $p = $conn->prepare("INSERT INTO payment (BookingID, Amount, PaymentMethod, PaymentStatus) VALUES (?, ?, ?, 'PAID')");
    $p->bind_param("ids", $row['BookingID'], $subtotal, $payment_method);
    $p->execute();

    // 更新 booking 状态为已付款
    $u = $conn->prepare("UPDATE booking SET Status='PAID' WHERE BookingID=?");
    $u->bind_param("i", $row['BookingID']);
    $u->execute();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Success</title>
<link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .btn { padding: 10px 20px; margin: 10px; display: inline-block; text-decoration: none; background-color: #4CAF50; color: #fff; border-radius: 5px; }
</style>
</head>
<body>
    <h1>Payment Successful!</h1>
    <p>Your payment of RM <?= number_format($total_amount, 2) ?> using <strong><?= htmlspecialchars($payment_method) ?></strong> has been completed.</p>
    <a href="index.php" class="btn">Back to Home</a>
    <a href="cart.php" class="btn">Back to Cart</a>
</body>
</html>

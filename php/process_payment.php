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


// Query all unpaid bookings for current user
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

    // Get the price

    // Get room price
    $r = $conn->prepare("SELECT RoomPrice FROM room WHERE HotelID=? AND RoomType=?");
    $r->bind_param("is", $row['HotelID'], $row['RoomType']);
    $r->execute();
    $rp = $r->get_result()->fetch_assoc();
    $subtotal = $rp['RoomPrice'] * $days * $row['RoomQuantity'];

    // Insert payment table
    // Insert into payment table
    $p = $conn->prepare("INSERT INTO payment (BookingID, Amount, PaymentMethod, PaymentStatus) VALUES (?, ?, ?, 'PAID')");
    $p->bind_param("ids", $row['BookingID'], $subtotal, $payment_method);
    $p->execute();


    // Update Booking status to paid
    // Update booking status to paid
    $u = $conn->prepare("UPDATE booking SET Status='PAID' WHERE BookingID=?");
    $u->bind_param("i", $row['BookingID']);
    $u->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Hotel Booking System</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/payment_success.css">
</head>
<body>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#4CAF50" stroke-width="2"/>
                    <path d="M8 12L11 15L16 9" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <h1>Payment Successful!</h1>
            
            <div class="payment-details">
                <div class="detail-row">
                    <span class="label">Amount Paid:</span>
                    <span class="value">RM <?= number_format($total_amount, 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment Method:</span>
                    <span class="value"><?= htmlspecialchars($payment_method) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Status:</span>
                    <span class="value status-paid">Paid</span>
                </div>
            </div>

            <p class="message">Thank you for your payment. Your booking has been confirmed.</p>

            <div class="button-group">
                <a href="index.php" class="btn btn-primary">Back to Home</a>
                <a href="cart.php" class="btn btn-secondary">View Cart</a>
            </div>
        </div>
    </div>
</body>
</html>
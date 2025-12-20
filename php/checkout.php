<?php
session_start();
include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    die("Please login first.");
}

// 查询用户未付款订单
$sql = "
    SELECT 
        b.BookingID, b.HotelID, b.RoomType, b.CheckInDate, b.CheckOutDate, b.RoomQuantity, b.BookingDate,
        h.HotelName, r.RoomPrice
    FROM booking b
    JOIN hotel h ON b.HotelID = h.HotelID
    JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
    WHERE b.TenantID = ? AND b.Status='UNPAID'
    ORDER BY b.BookingDate DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
$total = 0;
while($row = $result->fetch_assoc()) {
    $checkin = new DateTime($row['CheckInDate']);
    $checkout = new DateTime($row['CheckOutDate']);
    $days = max(1, $checkin->diff($checkout)->days);
    $subtotal = $row['RoomPrice'] * $days * $row['RoomQuantity'];
    $total += $subtotal;

    $row['days'] = $days;
    $row['subtotal'] = $subtotal;
    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Hotel Booking</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
    <link rel="stylesheet" href="/Hotel_Booking_System/css/checkout.css">
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <p>Review your order and complete payment</p>
        </div>

        <?php if(count($bookings) > 0): ?>
        <form action="process_payment.php" method="post" class="checkout-form">
            
            <!-- Order Summary Section -->
            <div class="order-section">
                <h2>Order Summary</h2>
                <div class="table-wrapper">
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Hotel</th>
                                <th>Room Type</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Rooms</th>
                                <th>Price/Night</th>
                                <th>Nights</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['HotelName']) ?></td>
                                <td><?= htmlspecialchars($b['RoomType']) ?></td>
                                <td><?= date('d M Y', strtotime($b['CheckInDate'])) ?></td>
                                <td><?= date('d M Y', strtotime($b['CheckOutDate'])) ?></td>
                                <td><?= $b['RoomQuantity'] ?></td>
                                <td>RM <?= number_format($b['RoomPrice'], 2) ?></td>
                                <td><?= $b['days'] ?></td>
                                <td class="subtotal">RM <?= number_format($b['subtotal'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="7">Total Amount</td>
                                <td class="total-amount">RM <?= number_format($total, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payment Method Section -->
            <div class="payment-section">
                <h2>Payment Method</h2>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Credit Card" required>
                        <span class="option-content">
                            <span class="option-title">Credit Card</span>
                            <span class="option-desc">Visa, Mastercard, American Express</span>
                        </span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Debit Card">
                        <span class="option-content">
                            <span class="option-title">Debit Card</span>
                            <span class="option-desc">Direct debit from your bank account</span>
                        </span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="E-wallet">
                        <span class="option-content">
                            <span class="option-title">E-wallet</span>
                            <span class="option-desc">Touch 'n Go, GrabPay, Boost</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <input type="hidden" name="total_amount" value="<?= $total ?>">
                <a href="cart.php" class="btn btn-back">Back to Cart</a>
                <button type="submit" class="btn btn-pay">Pay Now - RM <?= number_format($total, 2) ?></button>
            </div>
        </form>

        <?php else: ?>
        <div class="empty-checkout">
            <p>No unpaid bookings in your cart.</p>
            <a href="index.php" class="btn btn-primary">Back to Home</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
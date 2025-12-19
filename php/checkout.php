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
<title>Checkout</title>
<link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
<style>
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    th { background-color: #f5f5f5; }
    .total { font-weight: bold; }
    .btn { padding: 5px 10px; cursor: pointer; margin: 2px; }
    .back-btn { text-decoration: none; }
</style>
</head>
<body>
<h1>Checkout</h1>

<?php if(count($bookings) > 0): ?>
<form action="process_payment.php" method="post">
    <table>
        <thead>
            <tr>
                <th>Hotel</th>
                <th>Room Type</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Quantity</th>
                <th>Price (RM)</th>
                <th>Days</th>
                <th>Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($bookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['HotelName']) ?></td>
                <td><?= htmlspecialchars($b['RoomType']) ?></td>
                <td><?= $b['CheckInDate'] ?></td>
                <td><?= $b['CheckOutDate'] ?></td>
                <td><?= $b['RoomQuantity'] ?></td>
                <td><?= number_format($b['RoomPrice'], 2) ?></td>
                <td><?= $b['days'] ?></td>
                <td><?= number_format($b['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="total">Total</td>
                <td class="total"><?= number_format($total, 2) ?> RM</td>
            </tr>
        </tfoot>
    </table>

    <h3>Select Payment Method:</h3>
    <input type="radio" name="payment_method" value="Credit Card" required> Credit Card<br>
    <input type="radio" name="payment_method" value="Debit Card"> Debit Card<br>
    <input type="radio" name="payment_method" value="E-wallet"> E-wallet<br><br>

    <input type="hidden" name="total_amount" value="<?= $total ?>">
    <button type="submit" class="btn">Pay Now</button>
    <a href="cart.php" class="btn back-btn">← Back to Cart</a>
</form>
<?php else: ?>
<p>No unpaid bookings in your cart.</p>
<a href="index.php" class="btn back-btn">← Back to Home</a>
<?php endif; ?>

</body>
</html>

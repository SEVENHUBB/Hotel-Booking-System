<?php
session_start();
include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    die("Please login first.");
}

// 查询用户购物车 / 未付款订单
$sql = "
    SELECT 
        b.BookingID, b.HotelID, b.RoomType, b.CheckInDate, b.CheckOutDate, b.RoomQuantity, b.BookingDate,
        h.HotelName, r.RoomPrice
    FROM booking b
    JOIN hotel h ON b.HotelID = h.HotelID
    JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
    WHERE b.TenantID = ? AND b.Status = 'UNPAID'
    ORDER BY b.BookingDate DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart</title>
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
    <h1>My Cart</h1>

<?php if ($result->num_rows > 0): ?>
    <form action="update_cart.php" method="post">
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $total = 0;
                while($row = $result->fetch_assoc()): 
                    $checkin = new DateTime($row['CheckInDate']);
                    $checkout = new DateTime($row['CheckOutDate']);
                    $days = $checkin->diff($checkout)->days;
                    if ($days <= 0) $days = 1;

                    $subtotal = $row['RoomPrice'] * $days * $row['RoomQuantity'];
                    $total += $subtotal;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['HotelName']); ?></td>
                <td><?php echo htmlspecialchars($row['RoomType']); ?></td>
                <td><input type="date" name="checkin[<?php echo $row['BookingID']; ?>]" value="<?php echo $row['CheckInDate']; ?>" required></td>
                <td><input type="date" name="checkout[<?php echo $row['BookingID']; ?>]" value="<?php echo $row['CheckOutDate']; ?>" required></td>
                <td><input type="number" name="qty[<?php echo $row['BookingID']; ?>]" value="<?php echo $row['RoomQuantity']; ?>" min="1" required></td>
                <td><?php echo number_format($row['RoomPrice'], 2); ?></td>
                <td><?php echo $days; ?></td>
                <td><?php echo number_format($subtotal, 2); ?></td>
                <td>
                    <a href="remove_from_cart.php?id=<?php echo $row['BookingID']; ?>" class="btn">Remove</a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="total">Total</td>
                    <td colspan="2" class="total"><?php echo number_format($total, 2); ?> RM</td>
                </tr>
            </tfoot>
        </table>
        <button type="submit" class="btn">Update Cart</button>
        <a href="checkout.php" class="btn">Proceed to Checkout</a>
        <a href="index.php" class="btn back-btn">← Back to Home</a>
    </form>
<?php else: ?>
    <p>Your cart is empty.</p>
    <a href="index.php" class="btn back-btn">← Back to Home</a>
<?php endif; ?>

</body>
</html>

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Hotel Booking</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
    <link rel="stylesheet" href="/Hotel_Booking_System/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="cart-container">
        <div class="cart-header">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <h1><i class="fas fa-shopping-cart"></i> My Shopping Cart</h1>
            <p class="cart-subtitle">Review your bookings before checkout</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <form action="update_cart.php" method="post" class="cart-form">
                <div class="cart-items">
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
                    <div class="cart-item">
                        <div class="item-header">
                            <h3 class="hotel-name">
                                <i class="fas fa-hotel"></i>
                                <?php echo htmlspecialchars($row['HotelName']); ?>
                            </h3>
                            <a href="remove_from_cart.php?id=<?php echo $row['BookingID']; ?>" 
                               class="remove-btn" 
                               onclick="return confirm('Are you sure you want to remove this item?');">
                                <i class="fas fa-trash-alt"></i> Remove
                            </a>
                        </div>
                        
                        <div class="item-details">
                            <div class="detail-group">
                                <label><i class="fas fa-bed"></i> Room Type</label>
                                <p><?php echo htmlspecialchars($row['RoomType']); ?></p>
                            </div>
                            
                            <div class="detail-group">
                                <label><i class="fas fa-calendar-check"></i> Check-in</label>
                                <input type="date" 
                                       name="checkin[<?php echo $row['BookingID']; ?>]" 
                                       value="<?php echo $row['CheckInDate']; ?>" 
                                       required>
                            </div>
                            
                            <div class="detail-group">
                                <label><i class="fas fa-calendar-times"></i> Check-out</label>
                                <input type="date" 
                                       name="checkout[<?php echo $row['BookingID']; ?>]" 
                                       value="<?php echo $row['CheckOutDate']; ?>" 
                                       required>
                            </div>
                            
                            <div class="detail-group">
                                <label><i class="fas fa-door-open"></i> Rooms</label>
                                <input type="number" 
                                       name="qty[<?php echo $row['BookingID']; ?>]" 
                                       value="<?php echo $row['RoomQuantity']; ?>" 
                                       min="1" 
                                       required>
                            </div>
                            
                            <div class="detail-group">
                                <label><i class="fas fa-clock"></i> Nights</label>
                                <p class="nights-count"><?php echo $days; ?></p>
                            </div>
                            
                            <div class="detail-group">
                                <label><i class="fas fa-tag"></i> Price per Night</label>
                                <p class="price">RM <?php echo number_format($row['RoomPrice'], 2); ?></p>
                            </div>
                        </div>
                        
                        <div class="item-footer">
                            <span class="subtotal-label">Subtotal:</span>
                            <span class="subtotal-amount">RM <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="cart-summary">
                    <div class="summary-content">
                        <h2>Order Summary</h2>
                        <div class="summary-row">
                            <span>Total Items:</span>
                            <span><?php echo $result->num_rows; ?></span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total Amount:</span>
                            <span class="total-amount">RM <?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="checkout.php" class="btn btn-checkout">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Start exploring our hotels and add your favorite rooms to the cart!</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Browse Hotels
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 
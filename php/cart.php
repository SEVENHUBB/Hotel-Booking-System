<?php
session_start();
include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    header('Location: login.php');
    exit;
}

// Query user's cart (unpaid bookings only)
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
    <title>My Cart - Hotel Booking System</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F8FAFC;
            min-height: 100vh;
            color: #1A1A2E;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .page-header h1 svg { color: #0066FF; }
        .cart-count {
            background: #0066FF;
            color: white;
            font-size: 14px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748B;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .back-link:hover { background: white; color: #0066FF; }
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
        }
        .cart-items { display: flex; flex-direction: column; gap: 16px; }
        .cart-item {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .item-info h3 { font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .item-info .room-type { color: #64748B; font-size: 14px; margin-bottom: 16px; }
        .item-details { display: flex; gap: 24px; flex-wrap: wrap; }
        .detail-group { display: flex; flex-direction: column; gap: 4px; }
        .detail-group label {
            font-size: 12px;
            color: #94A3B8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-group input {
            padding: 10px 14px;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .detail-group input:focus { outline: none; border-color: #0066FF; }
        .detail-group input[type="date"] { min-width: 150px; }
        .detail-group input[type="number"] { width: 80px; }
        .item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
        }
        .item-price { font-size: 22px; font-weight: 700; color: #0066FF; }
        .item-price small { font-size: 13px; color: #94A3B8; font-weight: 400; }
        .btn-remove {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            background: #FEE2E2;
            color: #DC2626;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-remove:hover { background: #FECACA; }
        .cart-summary {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        .summary-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #E2E8F0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 14px;
        }
        .summary-row .label { color: #64748B; }
        .summary-row .value { font-weight: 600; }
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 2px solid #E2E8F0;
            font-size: 18px;
            font-weight: 700;
        }
        .summary-total .value { color: #0066FF; }
        .summary-actions { margin-top: 24px; display: flex; flex-direction: column; gap: 12px; }
        .btn {
            padding: 16px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 102, 255, 0.3);
        }
        .btn-secondary { background: #F1F5F9; color: #1A1A2E; }
        .btn-secondary:hover { background: #E2E8F0; }
        .empty-cart {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 16px;
        }
        .empty-cart svg { width: 100px; height: 100px; color: #CBD5E1; margin-bottom: 24px; }
        .empty-cart h2 { font-size: 22px; margin-bottom: 8px; }
        .empty-cart p { color: #64748B; margin-bottom: 24px; }
        .secure-notice {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12px;
            color: #64748B;
            margin-top: 16px;
        }
        .secure-notice svg { color: #00C853; }
        @media (max-width: 900px) {
            .cart-layout { grid-template-columns: 1fr; }
            .cart-summary { position: static; }
            .item-details { flex-direction: column; gap: 12px; }
            .cart-item { grid-template-columns: 1fr; }
            .item-actions { flex-direction: row; align-items: center; margin-top: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                My Cart
                <?php if ($result->num_rows > 0): ?>
                    <span class="cart-count"><?php echo $result->num_rows; ?> item<?php echo $result->num_rows > 1 ? 's' : ''; ?></span>
                <?php endif; ?>
            </h1>
            <a href="/Hotel_Booking_System/php/index.php" class="back-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Home
            </a>
        </div>

        <?php if ($result->num_rows > 0): ?>
        <form action="update_cart.php" method="post">
            <div class="cart-layout">
                <div class="cart-items">
                    <?php 
                        $total = 0;
                        while($row = $result->fetch_assoc()): 
                            $checkin = new DateTime($row['CheckInDate'] ?? 'now');
                            $checkout = new DateTime($row['CheckOutDate'] ?? date('Y-m-d', strtotime('+1 day')));
                            $days = max(1, $checkin->diff($checkout)->days);
                            $quantity = $row['RoomQuantity'] ?? 1;
                            $subtotal = $row['RoomPrice'] * $days * $quantity;
                            $total += $subtotal;
                    ?>
                    <div class="cart-item">
                        <div class="item-info">
                            <h3><?php echo htmlspecialchars($row['HotelName']); ?></h3>
                            <div class="room-type"><?php echo htmlspecialchars($row['RoomType']); ?></div>
                            <div class="item-details">
                                <div class="detail-group">
                                    <label>Check-in</label>
                                    <input type="date" name="checkin[<?php echo $row['BookingID']; ?>]" 
                                           value="<?php echo $row['CheckInDate'] ?? date('Y-m-d'); ?>" required>
                                </div>
                                <div class="detail-group">
                                    <label>Check-out</label>
                                    <input type="date" name="checkout[<?php echo $row['BookingID']; ?>]" 
                                           value="<?php echo $row['CheckOutDate'] ?? date('Y-m-d', strtotime('+1 day')); ?>" required>
                                </div>
                                <div class="detail-group">
                                    <label>Rooms</label>
                                    <input type="number" name="qty[<?php echo $row['BookingID']; ?>]" 
                                           value="<?php echo $quantity; ?>" min="1" max="10" required>
                                </div>
                            </div>
                        </div>
                        <div class="item-actions">
                            <div class="item-price">
                                RM <?php echo number_format($subtotal, 2); ?>
                                <br><small>RM <?php echo number_format($row['RoomPrice'], 2); ?>/night × <?php echo $days; ?> night<?php echo $days > 1 ? 's' : ''; ?></small>
                            </div>
                            <a href="remove_from_cart.php?id=<?php echo $row['BookingID']; ?>" class="btn-remove">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                                Remove
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="cart-summary">
                    <h2 class="summary-title">Order Summary</h2>
                    <?php 
                        $tax = $total * 0.06;
                        $grandTotal = $total + $tax;
                    ?>
                    <div class="summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value">RM <?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Tax (6% SST)</span>
                        <span class="value">RM <?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="summary-total">
                        <span class="label">Total</span>
                        <span class="value">RM <?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                    <div class="summary-actions">
                        <button type="submit" class="btn btn-secondary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Update Cart
                        </button>
                        <a href="/Hotel_Booking_System/php/payment.php" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                            Proceed to Checkout
                        </a>
                    </div>
                    <div class="secure-notice">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        Secure checkout with SSL encryption
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
        <div class="empty-cart">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any rooms to your cart yet.</p>
            <a href="/Hotel_Booking_System/php/index.php" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Browse Hotels
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
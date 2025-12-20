<?php
session_start();
include 'db_hotel.php';

// Check if user is logged in
if (!isset($_SESSION['tenant_id'])) {
    header('Location: login.php');
    exit;
}

$tenant_id = $_SESSION['tenant_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id <= 0) {
    header('Location: my_booking.php');
    exit;
}

// Get booking details
$sql = "
    SELECT 
        b.BookingID,
        b.HotelID,
        b.RoomType,
        b.CheckInDate,
        b.CheckOutDate,
        b.RoomQuantity,
        b.BookingDate,
        b.Status,
        h.HotelName,
        h.Description as HotelDescription,
        h.Address,
        h.City,
        h.Country,
        h.ImagePath,
        h.StarRating,
        h.NumRooms,
        r.RoomPrice,
        r.RoomDesc,
        r.Capacity,
        r.RoomImage,
        p.PaymentID,
        p.Amount as PaidAmount,
        p.PaymentMethod,
        p.PaymentStatus,
        t.FullName,
        t.TenantName,
        t.Email,
        t.PhoneNo,
        t.Country as GuestCountry
    FROM booking b
    JOIN hotel h ON b.HotelID = h.HotelID
    LEFT JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
    LEFT JOIN payment p ON p.BookingID = b.BookingID
    JOIN tenant t ON b.TenantID = t.TenantID
    WHERE b.BookingID = ? AND b.TenantID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header('Location: my_booking.php');
    exit;
}

// Calculate details
$checkin = new DateTime($booking['CheckInDate']);
$checkout = new DateTime($booking['CheckOutDate']);
$nights = max(1, $checkin->diff($checkout)->days);
$room_qty = $booking['RoomQuantity'] ?? 1;
$room_price = $booking['RoomPrice'] ?? 0;
$subtotal = $room_price * $nights * $room_qty;
$tax = $subtotal * 0.06;
$total = $subtotal + $tax;

$is_paid = ($booking['Status'] === 'PAID');
$is_upcoming = ($checkout >= new DateTime());

// Generate booking reference
$booking_ref = 'BK-' . str_pad($booking['BookingID'], 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details #<?php echo $booking['BookingID']; ?> - Super Booking System</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F8FAFC;
            min-height: 100vh;
            color: #1A1A2E;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748B;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            transition: all 0.2s;
        }

        .back-link:hover {
            color: #0066FF;
        }

        /* Header Card */
        .booking-header-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }

        .header-top {
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            color: white;
            padding: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-top.paid {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .header-top.unpaid {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header-title p {
            opacity: 0.9;
            font-size: 14px;
        }

        .booking-status-large {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .status-badge-large {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .booking-ref {
            font-size: 13px;
            opacity: 0.9;
            font-family: monospace;
        }

        .header-bottom {
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #E2E8F0;
        }

        .hotel-quick-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .hotel-thumb {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
        }

        .hotel-quick-details h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .hotel-quick-details p {
            font-size: 13px;
            color: #64748B;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .star-rating {
            color: #F59E0B;
            font-size: 12px;
            margin-top: 4px;
        }

        .date-summary {
            display: flex;
            gap: 32px;
            text-align: center;
        }

        .date-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .date-label {
            font-size: 11px;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .date-value {
            font-size: 16px;
            font-weight: 700;
            color: #1A1A2E;
        }

        .date-day {
            font-size: 12px;
            color: #64748B;
        }

        .nights-badge {
            background: #E0E7FF;
            color: #4F46E5;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1A1A2E;
        }

        .card-title i {
            color: #0066FF;
        }

        /* Room Details */
        .room-card {
            display: flex;
            gap: 20px;
            padding: 20px;
            background: #F8FAFC;
            border-radius: 12px;
        }

        .room-image {
            width: 120px;
            height: 90px;
            border-radius: 8px;
            object-fit: cover;
        }

        .room-info h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .room-info p {
            font-size: 13px;
            color: #64748B;
            margin-bottom: 4px;
        }

        .room-amenities {
            display: flex;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .amenity {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #64748B;
        }

        /* Guest Details */
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #E2E8F0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 14px;
            color: #64748B;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: #1A1A2E;
        }

        /* Price Summary */
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }

        .price-row.total {
            border-top: 2px solid #E2E8F0;
            margin-top: 12px;
            padding-top: 16px;
            font-size: 18px;
            font-weight: 700;
        }

        .price-row.total .price-value {
            color: #0066FF;
        }

        .price-calculation {
            font-size: 12px;
            color: #94A3B8;
        }

        /* Payment Info */
        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }

        .payment-badge.success {
            background: #D1FAE5;
            color: #059669;
        }

        .payment-badge.pending {
            background: #FEF3C7;
            color: #D97706;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0066FF;
            color: white;
        }

        .btn-primary:hover {
            background: #0052CC;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #F1F5F9;
            color: #1A1A2E;
        }

        .btn-secondary:hover {
            background: #E2E8F0;
        }

        .btn-warning {
            background: #F59E0B;
            color: white;
        }

        .btn-warning:hover {
            background: #D97706;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #E2E8F0;
            color: #64748B;
        }

        .btn-outline:hover {
            border-color: #0066FF;
            color: #0066FF;
        }

        /* Hotel Full Info */
        .hotel-description {
            font-size: 14px;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .hotel-address {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 14px;
            color: #64748B;
        }

        .hotel-address i {
            color: #0066FF;
            margin-top: 2px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }
            
            .back-link, .action-buttons, .btn {
                display: none !important;
            }
            
            .container {
                max-width: 100%;
                padding: 20px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #E2E8F0;
            }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .header-bottom {
                flex-direction: column;
                gap: 24px;
            }
            
            .date-summary {
                width: 100%;
                justify-content: space-around;
            }
        }

        @media (max-width: 600px) {
            .header-top {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }
            
            .booking-status-large {
                align-items: center;
            }
            
            .hotel-quick-info {
                flex-direction: column;
                text-align: center;
            }
            
            .date-summary {
                flex-direction: column;
                gap: 16px;
            }
            
            .room-card {
                flex-direction: column;
            }
            
            .room-image {
                width: 100%;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="my_booking.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to My Bookings
        </a>

        <!-- Header Card -->
        <div class="booking-header-card">
            <div class="header-top <?php echo $is_paid ? 'paid' : 'unpaid'; ?>">
                <div class="header-title">
                    <h1>
                        <?php if ($is_paid): ?>
                            <i class="fas fa-check-circle"></i> Booking Confirmed
                        <?php else: ?>
                            <i class="fas fa-clock"></i> Pending Payment
                        <?php endif; ?>
                    </h1>
                    <p>Thank you for choosing <?php echo htmlspecialchars($booking['HotelName']); ?></p>
                </div>
                <div class="booking-status-large">
                    <span class="status-badge-large">
                        <?php if ($is_paid): ?>
                            <i class="fas fa-check"></i> PAID
                        <?php else: ?>
                            <i class="fas fa-hourglass-half"></i> UNPAID
                        <?php endif; ?>
                    </span>
                    <span class="booking-ref"><?php echo $booking_ref; ?></span>
                </div>
            </div>

            <div class="header-bottom">
                <div class="hotel-quick-info">
                    <img src="../<?php echo !empty($booking['ImagePath']) ? htmlspecialchars($booking['ImagePath']) : 'images/default.png'; ?>" 
                         alt="<?php echo htmlspecialchars($booking['HotelName']); ?>" 
                         class="hotel-thumb">
                    <div class="hotel-quick-details">
                        <h2><?php echo htmlspecialchars($booking['HotelName']); ?></h2>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['City'] . ', ' . $booking['Country']); ?></p>
                        <div class="star-rating">
                            <?php for ($i = 0; $i < $booking['StarRating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="date-summary">
                    <div class="date-item">
                        <span class="date-label">Check-in</span>
                        <span class="date-value"><?php echo $checkin->format('d M'); ?></span>
                        <span class="date-day"><?php echo $checkin->format('l'); ?></span>
                    </div>
                    <span class="nights-badge"><?php echo $nights; ?> Night<?php echo $nights > 1 ? 's' : ''; ?></span>
                    <div class="date-item">
                        <span class="date-label">Check-out</span>
                        <span class="date-value"><?php echo $checkout->format('d M'); ?></span>
                        <span class="date-day"><?php echo $checkout->format('l'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <div class="main-content">
                <!-- Room Details -->
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-bed"></i> Room Details</h3>
                    <div class="room-card">
                        <?php if (!empty($booking['RoomImage'])): ?>
                        <img src="../<?php echo htmlspecialchars($booking['RoomImage']); ?>" alt="Room" class="room-image">
                        <?php else: ?>
                        <img src="../images/room_default.png" alt="Room" class="room-image">
                        <?php endif; ?>
                        <div class="room-info">
                            <h3><?php echo htmlspecialchars($booking['RoomType']); ?></h3>
                            <p><?php echo htmlspecialchars($booking['RoomDesc'] ?? 'Comfortable room with modern amenities'); ?></p>
                            <p><i class="fas fa-users"></i> Max <?php echo $booking['Capacity'] ?? 2; ?> guests per room</p>
                            <p><i class="fas fa-door-open"></i> <?php echo $room_qty; ?> room(s) booked</p>
                            <div class="room-amenities">
                                <span class="amenity"><i class="fas fa-wifi"></i> Free WiFi</span>
                                <span class="amenity"><i class="fas fa-snowflake"></i> Air Conditioning</span>
                                <span class="amenity"><i class="fas fa-tv"></i> TV</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guest Details -->
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-user"></i> Guest Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Guest Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['FullName'] ?? $booking['TenantName']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['Email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['PhoneNo']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Country</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['GuestCountry'] ?? '-'); ?></span>
                    </div>
                </div>

                <!-- Hotel Information -->
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-hotel"></i> Hotel Information</h3>
                    <p class="hotel-description"><?php echo htmlspecialchars($booking['HotelDescription'] ?? 'A wonderful place to stay with excellent service and amenities.'); ?></p>
                    <div class="hotel-address">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($booking['Address'] . ', ' . $booking['City'] . ', ' . $booking['Country']); ?></span>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <!-- Price Summary -->
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-receipt"></i> Price Summary</h3>
                    
                    <div class="price-row">
                        <span>
                            Room Price
                            <br><span class="price-calculation">RM <?php echo number_format($room_price, 2); ?> × <?php echo $nights; ?> night(s) × <?php echo $room_qty; ?> room(s)</span>
                        </span>
                        <span>RM <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="price-row">
                        <span>Tax (6% SST)</span>
                        <span>RM <?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <div class="price-row total">
                        <span>Total</span>
                        <span class="price-value">RM <?php echo number_format($is_paid ? $booking['PaidAmount'] : $total, 2); ?></span>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="payment-badge <?php echo $is_paid ? 'success' : 'pending'; ?>">
                            <i class="fas fa-<?php echo $is_paid ? 'check-circle' : 'clock'; ?>"></i>
                            <?php echo $is_paid ? 'Paid' : 'Pending'; ?>
                        </span>
                    </div>
                    
                    <?php if ($is_paid && $booking['PaymentMethod']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Method</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['PaymentMethod']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment ID</span>
                        <span class="detail-value">#<?php echo $booking['PaymentID']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-row">
                        <span class="detail-label">Booked On</span>
                        <span class="detail-value"><?php echo date('d M Y, H:i', strtotime($booking['BookingDate'])); ?></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <?php if (!$is_paid): ?>
                        <a href="cart.php" class="btn btn-warning">
                            <i class="fas fa-shopping-cart"></i> Go to Cart & Pay
                        </a>
                    <?php endif; ?>
                    
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print Booking
                    </button>
                    
 
                    <?php if (!$is_paid): ?>
                        <a href="remove_from_cart.php?id=<?php echo $booking['BookingID']; ?>" 
                           class="btn btn-outline" 
                           style="color: #DC2626; border-color: #FECACA;"
                           onclick="return confirm('Are you sure you want to cancel this booking?');">
                            <i class="fas fa-times"></i> Cancel Booking
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
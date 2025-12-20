<?php
session_start();
include 'db_hotel.php';

// Check if user is logged in
if (!isset($_SESSION['tenant_id'])) {
    header('Location: login.php');
    exit;
}

$tenant_id = $_SESSION['tenant_id'];

// Get all bookings for this user
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
        h.Address,
        h.City,
        h.Country,
        h.ImagePath,
        h.StarRating,
        r.RoomPrice,
        p.PaymentID,
        p.Amount as PaidAmount,
        p.PaymentMethod,
        p.PaymentStatus
    FROM booking b
    JOIN hotel h ON b.HotelID = h.HotelID
    LEFT JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
    LEFT JOIN payment p ON p.BookingID = b.BookingID
    WHERE b.TenantID = ?
    ORDER BY b.BookingDate DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

// Separate bookings by status
$upcoming_bookings = [];
$past_bookings = [];
$unpaid_bookings = [];

while ($row = $result->fetch_assoc()) {
    $checkout = new DateTime($row['CheckOutDate'] ?? 'now');
    $today = new DateTime();
    
    if ($row['Status'] === 'UNPAID' || empty($row['Status'])) {
        $unpaid_bookings[] = $row;
    } elseif ($checkout >= $today) {
        $upcoming_bookings[] = $row;
    } else {
        $past_bookings[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Super Booking System</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

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

        .page-header h1 i {
            color: #0066FF;
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

        .back-link:hover {
            background: white;
            color: #0066FF;
        }

        /* Tabs */
        .booking-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            background: white;
            padding: 8px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .tab-btn {
            flex: 1;
            padding: 14px 20px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #64748B;
        }

        .tab-btn:hover {
            background: #F1F5F9;
        }

        .tab-btn.active {
            background: #0066FF;
            color: white;
        }

        .tab-btn .badge {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        .tab-btn:not(.active) .badge {
            background: #E2E8F0;
            color: #64748B;
        }

        .tab-btn.unpaid-tab.active {
            background: #F59E0B;
        }

        /* Booking Cards */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: 200px 1fr auto;
            transition: all 0.2s;
        }

        .booking-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .booking-image {
            width: 200px;
            height: 100%;
            min-height: 180px;
            object-fit: cover;
        }

        .booking-details {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .hotel-name {
            font-size: 18px;
            font-weight: 700;
            color: #1A1A2E;
            margin-bottom: 4px;
        }

        .hotel-location {
            font-size: 13px;
            color: #64748B;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .booking-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background: #D1FAE5;
            color: #059669;
        }

        .status-unpaid {
            background: #FEF3C7;
            color: #D97706;
        }

        .status-completed {
            background: #E0E7FF;
            color: #4F46E5;
        }

        .booking-info {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 11px;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1A1A2E;
        }

        .booking-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: #64748B;
            padding-top: 12px;
            border-top: 1px solid #E2E8F0;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .booking-actions {
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            border-left: 1px solid #E2E8F0;
            min-width: 180px;
        }

        .booking-price {
            text-align: center;
        }

        .price-label {
            font-size: 12px;
            color: #64748B;
        }

        .price-value {
            font-size: 24px;
            font-weight: 700;
            color: #0066FF;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0066FF;
            color: white;
        }

        .btn-primary:hover {
            background: #0052CC;
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

        .btn-danger {
            background: #FEE2E2;
            color: #DC2626;
        }

        .btn-danger:hover {
            background: #FECACA;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 16px;
        }

        .empty-state i {
            font-size: 60px;
            color: #CBD5E1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 8px;
            color: #1A1A2E;
        }

        .empty-state p {
            color: #64748B;
            margin-bottom: 24px;
        }

        /* Star Rating */
        .star-rating {
            color: #F59E0B;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .booking-card {
                grid-template-columns: 1fr;
            }

            .booking-image {
                width: 100%;
                height: 200px;
            }

            .booking-actions {
                border-left: none;
                border-top: 1px solid #E2E8F0;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .booking-tabs {
                flex-wrap: wrap;
            }

            .tab-btn {
                flex: 1 1 45%;
            }
        }

        @media (max-width: 500px) {
            .page-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .booking-info {
                gap: 16px;
            }

            .booking-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-calendar-check"></i>
                My Bookings
            </h1>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>

        <!-- Tabs -->
        <div class="booking-tabs">
            <button class="tab-btn active" data-tab="upcoming">
                <i class="fas fa-clock"></i>
                Upcoming
                <span class="badge"><?php echo count($upcoming_bookings); ?></span>
            </button>
            <button class="tab-btn unpaid-tab" data-tab="unpaid">
                <i class="fas fa-exclamation-circle"></i>
                Pending Payment
                <span class="badge"><?php echo count($unpaid_bookings); ?></span>
            </button>
            <button class="tab-btn" data-tab="past">
                <i class="fas fa-history"></i>
                Past Bookings
                <span class="badge"><?php echo count($past_bookings); ?></span>
            </button>
        </div>

        <!-- Upcoming Bookings -->
        <div class="tab-content active" id="upcoming">
            <?php if (count($upcoming_bookings) > 0): ?>
                <div class="bookings-list">
                    <?php foreach ($upcoming_bookings as $booking): 
                        $checkin = new DateTime($booking['CheckInDate']);
                        $checkout = new DateTime($booking['CheckOutDate']);
                        $nights = max(1, $checkin->diff($checkout)->days);
                        $totalPrice = $booking['PaidAmount'] ?? ($booking['RoomPrice'] * $nights * ($booking['RoomQuantity'] ?? 1));
                    ?>
                    <div class="booking-card">
                        <img src="../<?php echo !empty($booking['ImagePath']) ? htmlspecialchars($booking['ImagePath']) : 'images/default.png'; ?>" 
                             alt="<?php echo htmlspecialchars($booking['HotelName']); ?>" 
                             class="booking-image">
                        
                        <div class="booking-details">
                            <div class="booking-header">
                                <div>
                                    <h3 class="hotel-name"><?php echo htmlspecialchars($booking['HotelName']); ?></h3>
                                    <p class="hotel-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($booking['City'] . ', ' . $booking['Country']); ?>
                                    </p>
                                    <div class="star-rating">
                                        <?php for ($i = 0; $i < $booking['StarRating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="booking-status status-paid">
                                    <i class="fas fa-check-circle"></i> Confirmed
                                </span>
                            </div>

                            <div class="booking-info">
                                <div class="info-item">
                                    <span class="info-label">Check-in</span>
                                    <span class="info-value"><?php echo $checkin->format('D, d M Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Check-out</span>
                                    <span class="info-value"><?php echo $checkout->format('D, d M Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Room Type</span>
                                    <span class="info-value"><?php echo htmlspecialchars($booking['RoomType']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Rooms</span>
                                    <span class="info-value"><?php echo $booking['RoomQuantity'] ?? 1; ?> room(s)</span>
                                </div>
                            </div>

                            <div class="booking-meta">
                                <span class="meta-item">
                                    <i class="fas fa-hashtag"></i>
                                    Booking #<?php echo $booking['BookingID']; ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    Booked on <?php echo date('d M Y', strtotime($booking['BookingDate'])); ?>
                                </span>
                                <?php if ($booking['PaymentMethod']): ?>
                                <span class="meta-item">
                                    <i class="fas fa-credit-card"></i>
                                    <?php echo htmlspecialchars($booking['PaymentMethod']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="booking-actions">
                            <div class="booking-price">
                                <div class="price-label">Total Paid</div>
                                <div class="price-value">RM <?php echo number_format($totalPrice, 2); ?></div>
                            </div>
                            <a href="booking_details.php?id=<?php echo $booking['BookingID']; ?>" class="btn btn-secondary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>No upcoming bookings</h3>
                    <p>You don't have any upcoming stays. Start planning your next trip!</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Hotels
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Unpaid/Pending Bookings -->
        <div class="tab-content" id="unpaid">
            <?php if (count($unpaid_bookings) > 0): ?>
                <div class="bookings-list">
                    <?php foreach ($unpaid_bookings as $booking): 
                        $checkin = new DateTime($booking['CheckInDate'] ?? 'now');
                        $checkout = new DateTime($booking['CheckOutDate'] ?? 'tomorrow');
                        $nights = max(1, $checkin->diff($checkout)->days);
                        $totalPrice = ($booking['RoomPrice'] ?? 0) * $nights * ($booking['RoomQuantity'] ?? 1);
                    ?>
                    <div class="booking-card">
                        <img src="../<?php echo !empty($booking['ImagePath']) ? htmlspecialchars($booking['ImagePath']) : 'images/default.png'; ?>" 
                             alt="<?php echo htmlspecialchars($booking['HotelName']); ?>" 
                             class="booking-image">
                        
                        <div class="booking-details">
                            <div class="booking-header">
                                <div>
                                    <h3 class="hotel-name"><?php echo htmlspecialchars($booking['HotelName']); ?></h3>
                                    <p class="hotel-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($booking['City'] . ', ' . $booking['Country']); ?>
                                    </p>
                                    <div class="star-rating">
                                        <?php for ($i = 0; $i < $booking['StarRating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="booking-status status-unpaid">
                                    <i class="fas fa-clock"></i> Pending Payment
                                </span>
                            </div>

                            <div class="booking-info">
                                <div class="info-item">
                                    <span class="info-label">Check-in</span>
                                    <span class="info-value"><?php echo $checkin->format('D, d M Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Check-out</span>
                                    <span class="info-value"><?php echo $checkout->format('D, d M Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Room Type</span>
                                    <span class="info-value"><?php echo htmlspecialchars($booking['RoomType']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Rooms</span>
                                    <span class="info-value"><?php echo $booking['RoomQuantity'] ?? 1; ?> room(s)</span>
                                </div>
                            </div>

                            <div class="booking-meta">
                                <span class="meta-item">
                                    <i class="fas fa-hashtag"></i>
                                    Booking #<?php echo $booking['BookingID']; ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    Added on <?php echo date('d M Y', strtotime($booking['BookingDate'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="booking-actions">
                            <div class="booking-price">
                                <div class="price-label">Amount Due</div>
                                <div class="price-value">RM <?php echo number_format($totalPrice * 1.06, 2); ?></div>
                            </div>
                            <a href="cart.php" class="btn btn-warning">
                                <i class="fas fa-shopping-cart"></i> Go to Cart
                            </a>
                            <a href="remove_from_cart.php?id=<?php echo $booking['BookingID']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Remove this booking from cart?');">
                                <i class="fas fa-trash"></i> Remove
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>All caught up!</h3>
                    <p>You have no pending payments. All your bookings are confirmed.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Hotels
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Past Bookings -->
        <div class="tab-content" id="past">
            <?php if (count($past_bookings) > 0): ?>
                <div class="bookings-list">
                    <?php foreach ($past_bookings as $booking): 
                        $checkin = new DateTime($booking['CheckInDate']);
                        $checkout = new DateTime($booking['CheckOutDate']);
                        $nights = max(1, $checkin->diff($checkout)->days);
                        $totalPrice = $booking['PaidAmount'] ?? ($booking['RoomPrice'] * $nights * ($booking['RoomQuantity'] ?? 1));
                    ?>
                    <div class="booking-card" style="opacity: 0.85;">
                        <img src="../<?php echo !empty($booking['ImagePath']) ? htmlspecialchars($booking['ImagePath']) : 'images/default.png'; ?>" 
                             alt="<?php echo htmlspecialchars($booking['HotelName']); ?>" 
                             class="booking-image">
                        
                        <div class="booking-details">
                            <div class="booking-header">
                                <div>
                                    <h3 class="hotel-name"><?php echo htmlspecialchars($booking['HotelName']); ?></h3>
                                    <p class="hotel-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($booking['City'] . ', ' . $booking['Country']); ?>
                                    </p>
                                    <div class="star-rating">
                                        <?php for ($i = 0; $i < $booking['StarRating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="booking-status status-completed">
                                    <i class="fas fa-check"></i> Completed
                                </span>
                            </div>

                            <div class="booking-info">
                                <div class="info-item">
                                    <span class="info-label">Check-in</span>
                                    <span class="info-value"><?php echo $checkin->format('D, d M Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Check-out</span>
                                    <span class="info-value"><?php echo $checkout->format('D, d M Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Room Type</span>
                                    <span class="info-value"><?php echo htmlspecialchars($booking['RoomType']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Duration</span>
                                    <span class="info-value"><?php echo $nights; ?> night(s)</span>
                                </div>
                            </div>

                            <div class="booking-meta">
                                <span class="meta-item">
                                    <i class="fas fa-hashtag"></i>
                                    Booking #<?php echo $booking['BookingID']; ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    Stayed on <?php echo $checkin->format('M Y'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="booking-actions">
                            <div class="booking-price">
                                <div class="price-label">Total Paid</div>
                                <div class="price-value">RM <?php echo number_format($totalPrice, 2); ?></div>
                            </div>
                            <a href="user_room.php?hotel_id=<?php echo $booking['HotelID']; ?>" class="btn btn-primary">
                                <i class="fas fa-redo"></i> Book Again
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No past bookings</h3>
                    <p>Your completed stays will appear here.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Hotels
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active from all tabs and content
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active to clicked tab
                this.classList.add('active');
                
                // Show corresponding content
                const tabId = this.dataset.tab;
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>
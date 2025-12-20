<?php
session_start();
include 'db_hotel.php';

if (!isset($_SESSION['tenant_id'])) {
    header('Location: login.php');
    exit;
}

$tenant_id = $_SESSION['tenant_id'];

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$sql = "
    SELECT 
        b.BookingID, b.HotelID, b.RoomType, b.CheckInDate, b.CheckOutDate,
        b.RoomQuantity, b.BookingDate, b.Status,
        h.HotelName, h.Address, h.City, h.Country, h.ImagePath, h.StarRating,
        r.RoomPrice,
        p.PaymentID, p.Amount as PaidAmount, p.PaymentMethod, p.PaymentStatus,
        ref.RefundID, ref.RefundAmount, ref.RefundPercentage, ref.RefundStatus, ref.RefundDate
    FROM booking b
    JOIN hotel h ON b.HotelID = h.HotelID
    LEFT JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
    LEFT JOIN payment p ON p.BookingID = b.BookingID
    LEFT JOIN refund ref ON ref.BookingID = b.BookingID
    WHERE b.TenantID = ?
    ORDER BY b.BookingDate DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

$upcoming = []; $past = []; $unpaid = []; $cancelled = [];

while ($row = $result->fetch_assoc()) {
    $checkout = new DateTime($row['CheckOutDate'] ?? 'now');
    $today = new DateTime();
    
    if ($row['Status'] === 'CANCELLED') {
        $cancelled[] = $row;
    } elseif ($row['Status'] === 'UNPAID' || empty($row['Status'])) {
        $unpaid[] = $row;
    } elseif ($checkout >= $today) {
        $upcoming[] = $row;
    } else {
        $past[] = $row;
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
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:#F8FAFC;min-height:100vh;color:#1A1A2E}
        .container{max-width:1200px;margin:0 auto;padding:40px 20px}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px}
        .page-header h1{font-size:28px;font-weight:700;display:flex;align-items:center;gap:12px}
        .page-header h1 i{color:#0066FF}
        .back-link{display:inline-flex;align-items:center;gap:8px;color:#64748B;text-decoration:none;font-size:14px;font-weight:500;padding:10px 16px;border-radius:8px;transition:all .2s}
        .back-link:hover{background:#fff;color:#0066FF}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-size:14px;animation:slideDown .3s ease-out}
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
        .alert-success{background:#D1FAE5;color:#059669;border:1px solid #059669}
        .alert-error{background:#FEE2E2;color:#DC2626;border:1px solid #DC2626}
        .alert i{font-size:20px}
        .alert-close{margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;opacity:.7}
        .alert-close:hover{opacity:1}
        .booking-tabs{display:flex;gap:8px;margin-bottom:24px;background:#fff;padding:8px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05);flex-wrap:wrap}
        .tab-btn{flex:1;min-width:100px;padding:14px 12px;border:none;background:transparent;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;color:#64748B}
        .tab-btn:hover{background:#F1F5F9}
        .tab-btn.active{background:#0066FF;color:#fff}
        .tab-btn .badge{background:rgba(255,255,255,.2);padding:2px 8px;border-radius:10px;font-size:12px}
        .tab-btn:not(.active) .badge{background:#E2E8F0;color:#64748B}
        .tab-btn.unpaid-tab.active{background:#F59E0B}
        .tab-btn.cancelled-tab.active{background:#DC2626}
        .tab-content{display:none}
        .tab-content.active{display:block}
        .bookings-list{display:flex;flex-direction:column;gap:16px}
        .booking-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);display:grid;grid-template-columns:200px 1fr auto;transition:all .2s}
        .booking-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);transform:translateY(-2px)}
        .booking-card.cancelled{opacity:.8}
        .booking-image{width:200px;height:100%;min-height:180px;object-fit:cover}
        .booking-details{padding:24px;display:flex;flex-direction:column;gap:12px}
        .booking-header{display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:12px}
        .hotel-name{font-size:18px;font-weight:700;color:#1A1A2E;margin-bottom:4px}
        .hotel-location{font-size:13px;color:#64748B;display:flex;align-items:center;gap:4px}
        .booking-status{padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;text-transform:uppercase;display:flex;align-items:center;gap:4px}
        .status-paid{background:#D1FAE5;color:#059669}
        .status-unpaid{background:#FEF3C7;color:#D97706}
        .status-completed{background:#E0E7FF;color:#4F46E5}
        .status-cancelled{background:#FEE2E2;color:#DC2626}
        .booking-info{display:flex;gap:24px;flex-wrap:wrap}
        .info-item{display:flex;flex-direction:column;gap:4px}
        .info-label{font-size:11px;color:#94A3B8;text-transform:uppercase;letter-spacing:.5px;font-weight:500}
        .info-value{font-size:14px;font-weight:600;color:#1A1A2E}
        .booking-meta{display:flex;gap:16px;font-size:13px;color:#64748B;padding-top:12px;border-top:1px solid #E2E8F0;flex-wrap:wrap}
        .meta-item{display:flex;align-items:center;gap:6px}
        .refund-info{background:#D1FAE5;color:#059669;padding:8px 12px;border-radius:8px;font-size:13px;display:flex;align-items:center;gap:8px}
        .refund-info.no-refund{background:#FEE2E2;color:#DC2626}
        .booking-actions{padding:24px;display:flex;flex-direction:column;justify-content:center;gap:12px;border-left:1px solid #E2E8F0;min-width:180px}
        .booking-price{text-align:center}
        .price-label{font-size:12px;color:#64748B}
        .price-value{font-size:24px;font-weight:700;color:#0066FF}
        .price-value.refunded{text-decoration:line-through;color:#94A3B8;font-size:18px}
        .btn{padding:12px 20px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .2s}
        .btn-primary{background:#0066FF;color:#fff}
        .btn-primary:hover{background:#0052CC}
        .btn-secondary{background:#F1F5F9;color:#1A1A2E}
        .btn-secondary:hover{background:#E2E8F0}
        .btn-warning{background:#F59E0B;color:#fff}
        .btn-warning:hover{background:#D97706}
        .btn-danger{background:#FEE2E2;color:#DC2626}
        .btn-danger:hover{background:#FECACA}
        .empty-state{text-align:center;padding:60px 40px;background:#fff;border-radius:16px}
        .empty-state i{font-size:60px;color:#CBD5E1;margin-bottom:20px}
        .empty-state h3{font-size:20px;margin-bottom:8px;color:#1A1A2E}
        .empty-state p{color:#64748B;margin-bottom:24px}
        .star-rating{color:#F59E0B;font-size:12px}
        @media(max-width:900px){.booking-card{grid-template-columns:1fr}.booking-image{width:100%;height:200px}.booking-actions{border-left:none;border-top:1px solid #E2E8F0;flex-direction:row;justify-content:space-between;align-items:center;flex-wrap:wrap}}
        @media(max-width:600px){.page-header{flex-direction:column;gap:16px;align-items:flex-start}.booking-info{gap:16px}.booking-actions{flex-direction:column}.tab-btn{min-width:80px;font-size:12px;padding:12px 8px}}
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-check"></i> My Bookings</h1>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>

    <?php if ($success_message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success_message); ?>
        <button class="alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error_message); ?>
        <button class="alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    <?php endif; ?>

    <div class="booking-tabs">
        <button class="tab-btn active" data-tab="upcoming"><i class="fas fa-clock"></i> Upcoming <span class="badge"><?php echo count($upcoming); ?></span></button>
        <button class="tab-btn unpaid-tab" data-tab="unpaid"><i class="fas fa-exclamation-circle"></i> Pending <span class="badge"><?php echo count($unpaid); ?></span></button>
        <button class="tab-btn" data-tab="past"><i class="fas fa-history"></i> Past <span class="badge"><?php echo count($past); ?></span></button>
        <button class="tab-btn cancelled-tab" data-tab="cancelled"><i class="fas fa-ban"></i> Cancelled <span class="badge"><?php echo count($cancelled); ?></span></button>
    </div>

    <!-- Upcoming -->
    <div class="tab-content active" id="upcoming">
        <?php if (count($upcoming) > 0): ?>
        <div class="bookings-list">
            <?php foreach ($upcoming as $b): 
                $in = new DateTime($b['CheckInDate']); $out = new DateTime($b['CheckOutDate']);
                $nights = max(1, $in->diff($out)->days);
                $total = $b['PaidAmount'] ?? ($b['RoomPrice'] * $nights * ($b['RoomQuantity'] ?? 1));
            ?>
            <div class="booking-card">
                <img src="../<?php echo !empty($b['ImagePath']) ? htmlspecialchars($b['ImagePath']) : 'images/default.png'; ?>" class="booking-image">
                <div class="booking-details">
                    <div class="booking-header">
                        <div>
                            <h3 class="hotel-name"><?php echo htmlspecialchars($b['HotelName']); ?></h3>
                            <p class="hotel-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($b['City'] . ', ' . $b['Country']); ?></p>
                            <div class="star-rating"><?php for ($i = 0; $i < $b['StarRating']; $i++) echo '<i class="fas fa-star"></i>'; ?></div>
                        </div>
                        <span class="booking-status status-paid"><i class="fas fa-check-circle"></i> Confirmed</span>
                    </div>
                    <div class="booking-info">
                        <div class="info-item"><span class="info-label">Check-in</span><span class="info-value"><?php echo $in->format('D, d M Y'); ?></span></div>
                        <div class="info-item"><span class="info-label">Check-out</span><span class="info-value"><?php echo $out->format('D, d M Y'); ?></span></div>
                        <div class="info-item"><span class="info-label">Room</span><span class="info-value"><?php echo htmlspecialchars($b['RoomType']); ?></span></div>
                        <div class="info-item"><span class="info-label">Qty</span><span class="info-value"><?php echo $b['RoomQuantity'] ?? 1; ?></span></div>
                    </div>
                    <div class="booking-meta">
                        <span class="meta-item"><i class="fas fa-hashtag"></i> #<?php echo $b['BookingID']; ?></span>
                        <span class="meta-item"><i class="fas fa-credit-card"></i> <?php echo htmlspecialchars($b['PaymentMethod'] ?? 'Paid'); ?></span>
                    </div>
                </div>
                <div class="booking-actions">
                    <div class="booking-price">
                        <div class="price-label">Total Paid</div>
                        <div class="price-value">RM <?php echo number_format($total, 2); ?></div>
                    </div>
                    <a href="booking_details.php?id=<?php echo $b['BookingID']; ?>" class="btn btn-secondary"><i class="fas fa-eye"></i> Details</a>
                    <a href="cancel_booking.php?id=<?php echo $b['BookingID']; ?>" class="btn btn-danger"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-calendar-alt"></i><h3>No upcoming bookings</h3><p>Start planning your next trip!</p><a href="index.php" class="btn btn-primary"><i class="fas fa-search"></i> Browse Hotels</a></div>
        <?php endif; ?>
    </div>

    <!-- Pending -->
    <div class="tab-content" id="unpaid">
        <?php if (count($unpaid) > 0): ?>
        <div class="bookings-list">
            <?php foreach ($unpaid as $b): 
                $in = new DateTime($b['CheckInDate'] ?? 'now'); $out = new DateTime($b['CheckOutDate'] ?? 'tomorrow');
                $nights = max(1, $in->diff($out)->days);
                $total = ($b['RoomPrice'] ?? 0) * $nights * ($b['RoomQuantity'] ?? 1);
            ?>
            <div class="booking-card">
                <img src="../<?php echo !empty($b['ImagePath']) ? htmlspecialchars($b['ImagePath']) : 'images/default.png'; ?>" class="booking-image">
                <div class="booking-details">
                    <div class="booking-header">
                        <div>
                            <h3 class="hotel-name"><?php echo htmlspecialchars($b['HotelName']); ?></h3>
                            <p class="hotel-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($b['City'] . ', ' . $b['Country']); ?></p>
                        </div>
                        <span class="booking-status status-unpaid"><i class="fas fa-clock"></i> Pending Payment</span>
                    </div>
                    <div class="booking-info">
                        <div class="info-item"><span class="info-label">Check-in</span><span class="info-value"><?php echo $in->format('D, d M Y'); ?></span></div>
                        <div class="info-item"><span class="info-label">Check-out</span><span class="info-value"><?php echo $out->format('D, d M Y'); ?></span></div>
                        <div class="info-item"><span class="info-label">Room</span><span class="info-value"><?php echo htmlspecialchars($b['RoomType']); ?></span></div>
                    </div>
                    <div class="booking-meta">
                        <span class="meta-item"><i class="fas fa-hashtag"></i> #<?php echo $b['BookingID']; ?></span>
                        <span class="meta-item"><i class="fas fa-calendar"></i> Added <?php echo date('d M Y', strtotime($b['BookingDate'])); ?></span>
                    </div>
                </div>
                <div class="booking-actions">
                    <div class="booking-price">
                        <div class="price-label">Amount Due</div>
                        <div class="price-value">RM <?php echo number_format($total * 1.06, 2); ?></div>
                    </div>
                    <a href="cart.php" class="btn btn-warning"><i class="fas fa-shopping-cart"></i> Pay Now</a>
                    <a href="remove_from_cart.php?id=<?php echo $b['BookingID']; ?>" class="btn btn-danger" onclick="return confirm('Remove from cart?');"><i class="fas fa-trash"></i> Remove</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-check-circle"></i><h3>All caught up!</h3><p>No pending payments.</p><a href="index.php" class="btn btn-primary"><i class="fas fa-search"></i> Browse Hotels</a></div>
        <?php endif; ?>
    </div>

    <!-- Past -->
    <div class="tab-content" id="past">
        <?php if (count($past) > 0): ?>
        <div class="bookings-list">
            <?php foreach ($past as $b): 
                $in = new DateTime($b['CheckInDate']); $out = new DateTime($b['CheckOutDate']);
                $nights = max(1, $in->diff($out)->days);
                $total = $b['PaidAmount'] ?? ($b['RoomPrice'] * $nights * ($b['RoomQuantity'] ?? 1));
            ?>
            <div class="booking-card" style="opacity:.85">
                <img src="../<?php echo !empty($b['ImagePath']) ? htmlspecialchars($b['ImagePath']) : 'images/default.png'; ?>" class="booking-image">
                <div class="booking-details">
                    <div class="booking-header">
                        <div>
                            <h3 class="hotel-name"><?php echo htmlspecialchars($b['HotelName']); ?></h3>
                            <p class="hotel-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($b['City'] . ', ' . $b['Country']); ?></p>
                        </div>
                        <span class="booking-status status-completed"><i class="fas fa-check"></i> Completed</span>
                    </div>
                    <div class="booking-info">
                        <div class="info-item"><span class="info-label">Stayed</span><span class="info-value"><?php echo $in->format('d M') . ' - ' . $out->format('d M Y'); ?></span></div>
                        <div class="info-item"><span class="info-label">Room</span><span class="info-value"><?php echo htmlspecialchars($b['RoomType']); ?></span></div>
                        <div class="info-item"><span class="info-label">Duration</span><span class="info-value"><?php echo $nights; ?> night(s)</span></div>
                    </div>
                    <div class="booking-meta"><span class="meta-item"><i class="fas fa-hashtag"></i> #<?php echo $b['BookingID']; ?></span></div>
                </div>
                <div class="booking-actions">
                    <div class="booking-price">
                        <div class="price-label">Total Paid</div>
                        <div class="price-value">RM <?php echo number_format($total, 2); ?></div>
                    </div>
                    <a href="user_room.php?hotel_id=<?php echo $b['HotelID']; ?>" class="btn btn-primary"><i class="fas fa-redo"></i> Book Again</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-history"></i><h3>No past bookings</h3><p>Your completed stays will appear here.</p><a href="index.php" class="btn btn-primary"><i class="fas fa-search"></i> Browse Hotels</a></div>
        <?php endif; ?>
    </div>

    <!-- Cancelled -->
    <div class="tab-content" id="cancelled">
        <?php if (count($cancelled) > 0): ?>
        <div class="bookings-list">
            <?php foreach ($cancelled as $b): 
                $in = new DateTime($b['CheckInDate']); $out = new DateTime($b['CheckOutDate']);
                $nights = max(1, $in->diff($out)->days);
                $original = $b['PaidAmount'] ?? ($b['RoomPrice'] * $nights * ($b['RoomQuantity'] ?? 1));
                $refund = $b['RefundAmount'] ?? 0;
            ?>
            <div class="booking-card cancelled">
                <img src="../<?php echo !empty($b['ImagePath']) ? htmlspecialchars($b['ImagePath']) : 'images/default.png'; ?>" class="booking-image" style="filter:grayscale(50%)">
                <div class="booking-details">
                    <div class="booking-header">
                        <div>
                            <h3 class="hotel-name"><?php echo htmlspecialchars($b['HotelName']); ?></h3>
                            <p class="hotel-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($b['City'] . ', ' . $b['Country']); ?></p>
                        </div>
                        <span class="booking-status status-cancelled"><i class="fas fa-ban"></i> Cancelled</span>
                    </div>
                    <div class="booking-info">
                        <div class="info-item"><span class="info-label">Original Dates</span><span class="info-value"><?php echo $in->format('d M') . ' - ' . $out->format('d M Y'); ?></span></div>
                        <div class="info-item"><span class="info-label">Room</span><span class="info-value"><?php echo htmlspecialchars($b['RoomType']); ?></span></div>
                    </div>
                    <?php if ($b['RefundID']): ?>
                    <div class="refund-info <?php echo $refund <= 0 ? 'no-refund' : ''; ?>">
                        <?php if ($refund > 0): ?>
                        <i class="fas fa-check-circle"></i> Refunded RM <?php echo number_format($refund, 2); ?> (<?php echo $b['RefundPercentage']; ?>%)
                        <?php else: ?>
                        <i class="fas fa-info-circle"></i> No refund (cancelled within 3 days)
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="booking-meta">
                        <span class="meta-item"><i class="fas fa-hashtag"></i> #<?php echo $b['BookingID']; ?></span>
                        <?php if ($b['RefundDate']): ?>
                        <span class="meta-item"><i class="fas fa-calendar-times"></i> Cancelled <?php echo date('d M Y', strtotime($b['RefundDate'])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="booking-actions">
                    <div class="booking-price">
                        <div class="price-label">Original Amount</div>
                        <div class="price-value refunded">RM <?php echo number_format($original, 2); ?></div>
                    </div>
                    <a href="user_room.php?hotel_id=<?php echo $b['HotelID']; ?>" class="btn btn-primary"><i class="fas fa-redo"></i> Book Again</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-smile"></i><h3>No cancelled bookings</h3><p>Great! You haven't cancelled any bookings.</p><a href="index.php" class="btn btn-primary"><i class="fas fa-search"></i> Browse Hotels</a></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.animation = 'slideDown .3s ease-out reverse';
        setTimeout(() => a.remove(), 300);
    });
}, 5000);
</script>
</body>
</html>
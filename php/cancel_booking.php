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
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

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
        b.Status,
        h.HotelName,
        p.PaymentID,
        p.Amount as PaidAmount,
        p.PaymentMethod
    FROM booking b
    JOIN hotel h ON b.HotelID = h.HotelID
    LEFT JOIN payment p ON p.BookingID = b.BookingID
    WHERE b.BookingID = ? AND b.TenantID = ? AND b.Status = 'PAID'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    $_SESSION['error_message'] = "Booking not found or cannot be cancelled.";
    header('Location: my_booking.php');
    exit;
}

// Calculate refund based on cancellation policy
$checkin = new DateTime($booking['CheckInDate']);
$today = new DateTime();
$days_until_checkin = $today->diff($checkin)->days;
$is_future = $checkin > $today;

// Refund Policy:
// - 7+ days before check-in: 100% refund
// - 3-6 days before check-in: 50% refund
// - Less than 3 days: No refund (but can still cancel)
// - Past check-in date: Cannot cancel

if (!$is_future) {
    $_SESSION['error_message'] = "Cannot cancel booking after check-in date has passed.";
    header('Location: my_booking.php');
    exit;
}

$paid_amount = $booking['PaidAmount'];
if ($days_until_checkin >= 7) {
    $refund_percentage = 100;
    $refund_amount = $paid_amount;
    $policy_message = "Full refund (cancelled 7+ days before check-in)";
} elseif ($days_until_checkin >= 3) {
    $refund_percentage = 50;
    $refund_amount = $paid_amount * 0.5;
    $policy_message = "50% refund (cancelled 3-6 days before check-in)";
} else {
    $refund_percentage = 0;
    $refund_amount = 0;
    $policy_message = "No refund (cancelled less than 3 days before check-in)";
}

// Process cancellation if confirmed
if ($confirm && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $reason = isset($_GET['reason']) ? trim($_GET['reason']) : 'Customer requested cancellation';
    
    $conn->begin_transaction();
    
    try {
        // 1. Update booking status to CANCELLED
        $update_booking = $conn->prepare("UPDATE booking SET Status = 'CANCELLED' WHERE BookingID = ?");
        $update_booking->bind_param("i", $booking_id);
        $update_booking->execute();
        
        // 2. Create refund record
        $insert_refund = $conn->prepare("
            INSERT INTO refund (PaymentID, BookingID, RefundAmount, RefundPercentage, RefundReason, RefundStatus, ProcessedDate)
            VALUES (?, ?, ?, ?, ?, 'Completed', NOW())
        ");
        $insert_refund->bind_param("iidis", 
            $booking['PaymentID'], 
            $booking_id, 
            $refund_amount, 
            $refund_percentage,
            $reason
        );
        $insert_refund->execute();
        
        // 3. Restore room quantity
        $restore_room = $conn->prepare("
            UPDATE room 
            SET RoomQuantity = RoomQuantity + ? 
            WHERE HotelID = ? AND RoomType = ?
        ");
        $restore_room->bind_param("iis", $booking['RoomQuantity'], $booking['HotelID'], $booking['RoomType']);
        $restore_room->execute();
        
        $conn->commit();
        
        $_SESSION['success_message'] = "Booking cancelled successfully. " . 
            ($refund_amount > 0 ? "Refund of RM " . number_format($refund_amount, 2) . " will be processed." : "No refund applicable based on cancellation policy.");
        
        header('Location: my_booking.php');
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Failed to cancel booking. Please try again.";
        header('Location: my_booking.php');
        exit;
    }
}

// Show confirmation page
$checkin_formatted = $checkin->format('D, d M Y');
$checkout = new DateTime($booking['CheckOutDate']);
$checkout_formatted = $checkout->format('D, d M Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - Super Booking System</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1A1A2E;
        }

        .cancel-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .cancel-header {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }

        .cancel-header i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.9;
        }

        .cancel-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .cancel-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .cancel-content {
            padding: 32px;
        }

        .booking-summary {
            background: #F8FAFC;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .booking-summary h3 {
            font-size: 16px;
            margin-bottom: 16px;
            color: #1A1A2E;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .summary-row .label {
            color: #64748B;
        }

        .summary-row .value {
            font-weight: 600;
            color: #1A1A2E;
        }

        .refund-policy {
            background: #FEF3C7;
            border: 1px solid #F59E0B;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .refund-policy.full-refund {
            background: #D1FAE5;
            border-color: #059669;
        }

        .refund-policy.no-refund {
            background: #FEE2E2;
            border-color: #DC2626;
        }

        .refund-policy h4 {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .refund-policy.full-refund h4 { color: #059669; }
        .refund-policy.no-refund h4 { color: #DC2626; }
        .refund-policy h4 { color: #D97706; }

        .refund-amount {
            font-size: 28px;
            font-weight: 700;
            color: #1A1A2E;
            margin-bottom: 8px;
        }

        .refund-note {
            font-size: 13px;
            color: #64748B;
        }

        .reason-section {
            margin-bottom: 24px;
        }

        .reason-section label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1A1A2E;
        }

        .reason-section select,
        .reason-section textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .reason-section select:focus,
        .reason-section textarea:focus {
            outline: none;
            border-color: #0066FF;
        }

        .reason-section textarea {
            resize: vertical;
            min-height: 80px;
        }

        .warning-box {
            background: #FEF3C7;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .warning-box i {
            color: #F59E0B;
            font-size: 20px;
            margin-top: 2px;
        }

        .warning-box p {
            font-size: 13px;
            color: #92400E;
            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
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

        .btn-cancel {
            background: #DC2626;
            color: white;
        }

        .btn-cancel:hover {
            background: #B91C1C;
        }

        .btn-back {
            background: #F1F5F9;
            color: #1A1A2E;
        }

        .btn-back:hover {
            background: #E2E8F0;
        }

        .policy-details {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #E2E8F0;
        }

        .policy-details h4 {
            font-size: 14px;
            margin-bottom: 12px;
            color: #1A1A2E;
        }

        .policy-list {
            list-style: none;
        }

        .policy-list li {
            font-size: 13px;
            color: #64748B;
            padding: 6px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .policy-list li i {
            width: 16px;
            text-align: center;
        }

        .policy-list li.active {
            color: #059669;
            font-weight: 600;
        }

        .policy-list li.active i {
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="cancel-container">
        <div class="cancel-header">
            <i class="fas fa-calendar-times"></i>
            <h1>Cancel Booking</h1>
            <p>Please review the cancellation details below</p>
        </div>

        <div class="cancel-content">
            <!-- Booking Summary -->
            <div class="booking-summary">
                <h3><i class="fas fa-hotel"></i> <?php echo htmlspecialchars($booking['HotelName']); ?></h3>
                <div class="summary-row">
                    <span class="label">Booking ID</span>
                    <span class="value">#<?php echo $booking['BookingID']; ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">Room Type</span>
                    <span class="value"><?php echo htmlspecialchars($booking['RoomType']); ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">Check-in</span>
                    <span class="value"><?php echo $checkin_formatted; ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">Check-out</span>
                    <span class="value"><?php echo $checkout_formatted; ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">Amount Paid</span>
                    <span class="value">RM <?php echo number_format($paid_amount, 2); ?></span>
                </div>
            </div>

            <!-- Refund Policy -->
            <div class="refund-policy <?php echo $refund_percentage === 100 ? 'full-refund' : ($refund_percentage === 0 ? 'no-refund' : ''); ?>">
                <h4>
                    <?php if ($refund_percentage === 100): ?>
                        <i class="fas fa-check-circle"></i> Full Refund Eligible
                    <?php elseif ($refund_percentage === 50): ?>
                        <i class="fas fa-exclamation-circle"></i> Partial Refund
                    <?php else: ?>
                        <i class="fas fa-times-circle"></i> No Refund
                    <?php endif; ?>
                </h4>
                <div class="refund-amount">
                    RM <?php echo number_format($refund_amount, 2); ?>
                    <?php if ($refund_percentage > 0 && $refund_percentage < 100): ?>
                        <span style="font-size: 14px; color: #64748B; font-weight: normal;">(<?php echo $refund_percentage; ?>%)</span>
                    <?php endif; ?>
                </div>
                <p class="refund-note"><?php echo $policy_message; ?></p>
            </div>

            <!-- Reason Selection -->
            <form id="cancelForm" class="reason-section">
                <label for="reason">Reason for cancellation</label>
                <select id="reason" name="reason" required>
                    <option value="">Select a reason...</option>
                    <option value="Change of plans">Change of plans</option>
                    <option value="Found better deal">Found a better deal</option>
                    <option value="Travel dates changed">Travel dates changed</option>
                    <option value="Personal emergency">Personal emergency</option>
                    <option value="Weather concerns">Weather concerns</option>
                    <option value="Health issues">Health issues</option>
                    <option value="Other">Other</option>
                </select>
            </form>

            <!-- Warning -->
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <p>
                    <strong>This action cannot be undone.</strong><br>
                    Once cancelled, you will need to make a new booking if you change your mind.
                    <?php if ($refund_amount > 0): ?>
                    Refund will be processed within 5-7 business days to your original payment method.
                    <?php endif; ?>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="my_booking.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <button type="button" class="btn btn-cancel" onclick="confirmCancel()">
                    <i class="fas fa-times"></i> Cancel Booking
                </button>
            </div>

            <!-- Cancellation Policy Details -->
            <div class="policy-details">
                <h4><i class="fas fa-info-circle"></i> Cancellation Policy</h4>
                <ul class="policy-list">
                    <li class="<?php echo $days_until_checkin >= 7 ? 'active' : ''; ?>">
                        <i class="fas fa-<?php echo $days_until_checkin >= 7 ? 'check' : 'circle'; ?>"></i>
                        7+ days before check-in: 100% refund
                    </li>
                    <li class="<?php echo ($days_until_checkin >= 3 && $days_until_checkin < 7) ? 'active' : ''; ?>">
                        <i class="fas fa-<?php echo ($days_until_checkin >= 3 && $days_until_checkin < 7) ? 'check' : 'circle'; ?>"></i>
                        3-6 days before check-in: 50% refund
                    </li>
                    <li class="<?php echo $days_until_checkin < 3 ? 'active' : ''; ?>">
                        <i class="fas fa-<?php echo $days_until_checkin < 3 ? 'check' : 'circle'; ?>"></i>
                        Less than 3 days: No refund
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function confirmCancel() {
            const reason = document.getElementById('reason').value;
            if (!reason) {
                alert('Please select a reason for cancellation.');
                return;
            }
            
            const refundAmount = <?php echo $refund_amount; ?>;
            let message = 'Are you sure you want to cancel this booking?\n\n';
            if (refundAmount > 0) {
                message += 'You will receive a refund of RM ' + refundAmount.toFixed(2);
            } else {
                message += 'No refund will be provided based on the cancellation policy.';
            }
            
            if (confirm(message)) {
                window.location.href = 'cancel_booking.php?id=<?php echo $booking_id; ?>&confirm=yes&reason=' + encodeURIComponent(reason);
            }
        }
    </script>
</body>
</html>
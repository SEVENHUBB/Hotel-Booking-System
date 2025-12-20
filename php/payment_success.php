<?php
/**
 * Payment Success Page
 * Displays payment confirmation and receipt
 */

session_start();
include 'db_hotel.php';

// Check if user is logged in
$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    header('Location: /login.php');
    exit;
}

// Get payment info from session
$payment_info = $_SESSION['last_payment'] ?? null;
$payment_id = $_GET['payment_id'] ?? ($payment_info['payment_id'] ?? null);

if (!$payment_id) {
    header('Location: cart.php');
    exit;
}

// Fetch payment details
$payment_sql = "
    SELECT 
        p.PaymentID,
        p.BookingID,
        p.Amount,
        p.PaymentMethod,
        p.PaymentStatus,
        b.HotelID,
        b.RoomType,
        b.CheckInDate,
        b.CheckOutDate,
        b.RoomQuantity,
        b.BookingDate,
        h.HotelName,
        h.Address,
        h.City,
        h.Country as HotelCountry,
        t.FullName,
        t.TenantName,
        t.Email,
        t.PhoneNo
    FROM payment p
    JOIN booking b ON p.BookingID = b.BookingID
    JOIN hotel h ON b.HotelID = h.HotelID
    JOIN tenant t ON b.TenantID = t.TenantID
    WHERE p.PaymentID = ? AND b.TenantID = ?
";

$stmt = $conn->prepare($payment_sql);
$stmt->bind_param("ii", $payment_id, $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    header('Location: cart.php');
    exit;
}

// Calculate nights
$checkin = new DateTime($payment['CheckInDate']);
$checkout = new DateTime($payment['CheckOutDate']);
$nights = max(1, $checkin->diff($checkout)->days);

// Get payment reference
$payment_ref = $payment_info['payment_ref'] ?? 'PAY-' . strtoupper(dechex($payment_id));

// Clear payment session
unset($_SESSION['last_payment']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Hotel Booking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --success: #00C853;
            --success-light: #E8F5E9;
            --text-primary: #1A1A2E;
            --text-secondary: #64748B;
            --text-muted: #94A3B8;
            --bg-primary: #FFFFFF;
            --bg-secondary: #F8FAFC;
            --border: #E2E8F0;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-primary);
        }

        .success-container {
            max-width: 600px;
            width: 100%;
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success-header {
            background: linear-gradient(135deg, var(--success) 0%, #00A846 100%);
            padding: 50px 40px;
            text-align: center;
            color: white;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out 0.3s both;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            animation: checkmark 0.5s ease-out 0.5s both;
        }

        @keyframes checkmark {
            from { stroke-dashoffset: 50; }
            to { stroke-dashoffset: 0; }
        }

        .success-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .success-header p {
            opacity: 0.9;
            font-size: 15px;
        }

        .receipt-content {
            padding: 40px;
        }

        .receipt-section {
            margin-bottom: 30px;
        }

        .receipt-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .receipt-row:last-child {
            border-bottom: none;
        }

        .receipt-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .receipt-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            text-align: right;
        }

        .receipt-total {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: var(--radius-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .receipt-total .label {
            font-size: 16px;
            font-weight: 600;
        }

        .receipt-total .amount {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .payment-ref {
            background: var(--success-light);
            padding: 16px;
            border-radius: var(--radius-md);
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-ref .label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        .payment-ref .ref-number {
            font-size: 18px;
            font-weight: 700;
            color: var(--success);
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 16px 24px;
            border-radius: var(--radius-md);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
        }

        .btn svg {
            width: 18px;
            height: 18px;
        }

        .hotel-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: var(--radius-md);
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .hotel-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .hotel-info h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .hotel-info p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .confirmation-email {
            text-align: center;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            margin-top: 20px;
        }

        .confirmation-email svg {
            width: 24px;
            height: 24px;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .confirmation-email p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .confirmation-email strong {
            color: var(--text-primary);
        }

        @media (max-width: 500px) {
            .success-header {
                padding: 40px 24px;
            }

            .receipt-content {
                padding: 24px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .success-container {
                box-shadow: none;
                max-width: 100%;
            }

            .action-buttons,
            .confirmation-email {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray: 50;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h1>Payment Successful!</h1>
            <p>Your booking has been confirmed</p>
        </div>

        <div class="receipt-content">
            <div class="payment-ref">
                <div class="label">Payment Reference</div>
                <div class="ref-number"><?php echo htmlspecialchars($payment_ref); ?></div>
            </div>

            <div class="receipt-section">
                <h3>Booking Details</h3>
                <div class="hotel-card">
                    <div class="hotel-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l8-4v18"/>
                            <path d="M19 21V11l-6-4"/>
                            <path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
                        </svg>
                    </div>
                    <div class="hotel-info">
                        <h4><?php echo htmlspecialchars($payment['HotelName']); ?></h4>
                        <p><?php echo htmlspecialchars($payment['Address'] . ', ' . $payment['City']); ?></p>
                    </div>
                </div>

                <div class="receipt-row">
                    <span class="receipt-label">Room Type</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($payment['RoomType']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Check-in</span>
                    <span class="receipt-value"><?php echo $checkin->format('D, d M Y'); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Check-out</span>
                    <span class="receipt-value"><?php echo $checkout->format('D, d M Y'); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Duration</span>
                    <span class="receipt-value"><?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Rooms</span>
                    <span class="receipt-value"><?php echo $payment['RoomQuantity'] ?? 1; ?></span>
                </div>
            </div>

            <div class="receipt-section">
                <h3>Payment Information</h3>
                <div class="receipt-row">
                    <span class="receipt-label">Payment Method</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($payment['PaymentMethod']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Status</span>
                    <span class="receipt-value" style="color: var(--success);">✓ <?php echo htmlspecialchars($payment['PaymentStatus']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Transaction Date</span>
                    <span class="receipt-value"><?php echo date('d M Y, H:i'); ?></span>
                </div>

                <div class="receipt-total">
                    <span class="label">Total Paid</span>
                    <span class="amount">RM <?php echo number_format($payment['Amount'], 2); ?></span>
                </div>
            </div>

            <div class="receipt-section">
                <h3>Guest Information</h3>
                <div class="receipt-row">
                    <span class="receipt-label">Name</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($payment['FullName'] ?? $payment['TenantName']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Email</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($payment['Email']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Phone</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($payment['PhoneNo']); ?></span>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="window.print()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print Receipt
                </button>
                <a href="index.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Back to Home
                </a>
            </div>

            <div class="confirmation-email">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <p>A confirmation email has been sent to<br><strong><?php echo htmlspecialchars($payment['Email']); ?></strong></p>
            </div>
        </div>
    </div>
</body>
</html>
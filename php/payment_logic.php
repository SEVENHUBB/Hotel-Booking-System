<?php
/**
 * Payment Processing Logic
 * Handles payment submission and creates payment records
 */

session_start();
header('Content-Type: application/json');

include 'db_hotel.php';

// Check if user is logged in
$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login first'
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    // Get form data
    $booking_ids_str = $_POST['booking_ids'] ?? '';
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validate required fields
    if (empty($booking_ids_str) || $total_amount <= 0 || empty($payment_method)) {
        throw new Exception('Missing required payment information');
    }
    
    // Parse booking IDs
    $booking_ids = array_filter(array_map('intval', explode(',', $booking_ids_str)));
    if (empty($booking_ids)) {
        throw new Exception('No valid bookings found');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Verify bookings belong to this tenant and calculate total
    $placeholders = implode(',', array_fill(0, count($booking_ids), '?'));
    $types = str_repeat('i', count($booking_ids));
    
    $verify_sql = "
        SELECT 
            b.BookingID,
            b.TenantID,
            b.CheckInDate,
            b.CheckOutDate,
            b.RoomQuantity,
            r.RoomPrice
        FROM booking b
        JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
        WHERE b.BookingID IN ($placeholders)
    ";
    
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param($types, ...$booking_ids);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    $calculated_total = 0;
    $verified_booking_ids = [];
    
    while ($booking = $verify_result->fetch_assoc()) {
        // Check ownership
        if ($booking['TenantID'] != $tenant_id) {
            throw new Exception('Unauthorized access to booking');
        }
        
        // Calculate booking amount
        $checkin = new DateTime($booking['CheckInDate'] ?? 'now');
        $checkout = new DateTime($booking['CheckOutDate'] ?? 'tomorrow');
        $days = max(1, $checkin->diff($checkout)->days);
        $quantity = $booking['RoomQuantity'] ?? 1;
        $booking_amount = $booking['RoomPrice'] * $days * $quantity;
        $calculated_total += $booking_amount;
        
        $verified_booking_ids[] = $booking['BookingID'];
    }
    
    // Add 6% SST tax
    $calculated_total *= 1.06;
    $calculated_total = round($calculated_total, 2);
    
    // Verify amount matches (allow small difference for rounding)
    if (abs($calculated_total - $total_amount) > 0.10) {
        throw new Exception('Payment amount mismatch. Please refresh and try again.');
    }
    
    // Generate unique payment reference
    $payment_ref = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
    
    // Process each booking and create payment records
    foreach ($verified_booking_ids as $booking_id) {
        // Get booking amount for this specific booking
        $amount_sql = "
            SELECT 
                b.CheckInDate,
                b.CheckOutDate,
                b.RoomQuantity,
                r.RoomPrice
            FROM booking b
            JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
            WHERE b.BookingID = ?
        ";
        $amount_stmt = $conn->prepare($amount_sql);
        $amount_stmt->bind_param("i", $booking_id);
        $amount_stmt->execute();
        $amount_result = $amount_stmt->get_result();
        $booking_data = $amount_result->fetch_assoc();
        
        $checkin = new DateTime($booking_data['CheckInDate'] ?? 'now');
        $checkout = new DateTime($booking_data['CheckOutDate'] ?? 'tomorrow');
        $days = max(1, $checkin->diff($checkout)->days);
        $quantity = $booking_data['RoomQuantity'] ?? 1;
        $booking_amount = $booking_data['RoomPrice'] * $days * $quantity;
        $booking_amount_with_tax = round($booking_amount * 1.06, 2);
        
        // Insert payment record
        $payment_sql = "
            INSERT INTO payment (BookingID, Amount, PaymentMethod, PaymentStatus)
            VALUES (?, ?, ?, 'Completed')
        ";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param("ids", $booking_id, $booking_amount_with_tax, $payment_method);
        
        if (!$payment_stmt->execute()) {
            throw new Exception('Failed to create payment record');
        }
        
        $payment_id = $conn->insert_id;
    }
    
    // Get tenant info for bill
    $tenant_sql = "SELECT TenantName, FullName, PhoneNo, Gender, Email, Country FROM tenant WHERE TenantID = ?";
    $tenant_stmt = $conn->prepare($tenant_sql);
    $tenant_stmt->bind_param("i", $tenant_id);
    $tenant_stmt->execute();
    $tenant_result = $tenant_stmt->get_result();
    $tenant = $tenant_result->fetch_assoc();
    
    // Create bill record
    $bill_sql = "
        INSERT INTO bill (PaymentID, TenantName, PhoneNo, Gender, Email, Country)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $bill_stmt = $conn->prepare($bill_sql);
    $tenant_name = $tenant['FullName'] ?? $tenant['TenantName'] ?? 'Guest';
    $bill_stmt->bind_param("isssss", 
        $payment_id,
        $tenant_name,
        $tenant['PhoneNo'],
        $tenant['Gender'],
        $tenant['Email'],
        $tenant['Country']
    );
    
    if (!$bill_stmt->execute()) {
        throw new Exception('Failed to create bill record');
    }
    
    $bill_id = $conn->insert_id;
    
    // Commit transaction
    $conn->commit();
    
    // Store payment info in session for success page
    $_SESSION['last_payment'] = [
        'payment_id' => $payment_id,
        'payment_ref' => $payment_ref,
        'bill_id' => $bill_id,
        'amount' => $total_amount,
        'method' => $payment_method,
        'booking_ids' => $verified_booking_ids,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'payment_id' => $payment_id,
        'payment_ref' => $payment_ref,
        'bill_id' => $bill_id
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    if ($conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
<?php
/**
 * Payment Processing Logic
 */

session_start();
header('Content-Type: application/json');

include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $booking_ids_str = $_POST['booking_ids'] ?? '';
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($booking_ids_str) || $total_amount <= 0 || empty($payment_method)) {
        throw new Exception('Missing required payment information');
    }
    
    $booking_ids = array_filter(array_map('intval', explode(',', $booking_ids_str)));
    if (empty($booking_ids)) throw new Exception('No valid bookings found');
    
    $conn->begin_transaction();
    
    // Verify bookings
    $placeholders = implode(',', array_fill(0, count($booking_ids), '?'));
    $types = str_repeat('i', count($booking_ids));
    
    $verify_sql = "
        SELECT b.BookingID, b.TenantID, b.CheckInDate, b.CheckOutDate, b.RoomQuantity, r.RoomPrice
        FROM booking b
        JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
        WHERE b.BookingID IN ($placeholders)
    ";
    
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param($types, ...$booking_ids);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    $calculated_total = 0;
    $verified_ids = [];
    
    while ($booking = $verify_result->fetch_assoc()) {
        if ($booking['TenantID'] != $tenant_id) throw new Exception('Unauthorized');
        
        $checkin = new DateTime($booking['CheckInDate'] ?? 'now');
        $checkout = new DateTime($booking['CheckOutDate'] ?? 'tomorrow');
        $days = max(1, $checkin->diff($checkout)->days);
        $quantity = $booking['RoomQuantity'] ?? 1;
        $calculated_total += $booking['RoomPrice'] * $days * $quantity;
        $verified_ids[] = $booking['BookingID'];
    }
    
    $calculated_total *= 1.06; // Add tax
    
    if (abs($calculated_total - $total_amount) > 0.10) {
        throw new Exception('Amount mismatch. Please refresh.');
    }
    
    $payment_ref = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
    $payment_id = null;
    
    // Create payment records
    foreach ($verified_ids as $booking_id) {
        $amt_sql = "SELECT b.CheckInDate, b.CheckOutDate, b.RoomQuantity, r.RoomPrice
                    FROM booking b JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
                    WHERE b.BookingID = ?";
        $amt_stmt = $conn->prepare($amt_sql);
        $amt_stmt->bind_param("i", $booking_id);
        $amt_stmt->execute();
        $data = $amt_stmt->get_result()->fetch_assoc();
        
        $checkin = new DateTime($data['CheckInDate'] ?? 'now');
        $checkout = new DateTime($data['CheckOutDate'] ?? 'tomorrow');
        $days = max(1, $checkin->diff($checkout)->days);
        $amount = round($data['RoomPrice'] * $days * ($data['RoomQuantity'] ?? 1) * 1.06, 2);
        
        $pay_stmt = $conn->prepare("INSERT INTO payment (BookingID, Amount, PaymentMethod, PaymentStatus) VALUES (?, ?, ?, 'Completed')");
        $pay_stmt->bind_param("ids", $booking_id, $amount, $payment_method);
        $pay_stmt->execute();
        $payment_id = $conn->insert_id;
    }
    
    // Create bill
    $tenant_stmt = $conn->prepare("SELECT TenantName, FullName, PhoneNo, Gender, Email, Country FROM tenant WHERE TenantID = ?");
    $tenant_stmt->bind_param("i", $tenant_id);
    $tenant_stmt->execute();
    $tenant = $tenant_stmt->get_result()->fetch_assoc();
    
    $bill_stmt = $conn->prepare("INSERT INTO bill (PaymentID, TenantName, PhoneNo, Gender, Email, Country) VALUES (?, ?, ?, ?, ?, ?)");
    $name = $tenant['FullName'] ?? $tenant['TenantName'] ?? 'Guest';
    $bill_stmt->bind_param("isssss", $payment_id, $name, $tenant['PhoneNo'], $tenant['Gender'], $tenant['Email'], $tenant['Country']);
    $bill_stmt->execute();
    
    // Mark all bookings as PAID
    $update_placeholders = implode(',', array_fill(0, count($verified_ids), '?'));
    $update_types = str_repeat('i', count($verified_ids));
    $update_sql = "UPDATE booking SET Status = 'PAID' WHERE BookingID IN ($update_placeholders)";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param($update_types, ...$verified_ids);
    $update_stmt->execute();
    
    $conn->commit();
    
    $_SESSION['last_payment'] = [
        'payment_id' => $payment_id,
        'payment_ref' => $payment_ref,
        'amount' => $total_amount,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment successful',
        'payment_id' => $payment_id,
        'payment_ref' => $payment_ref
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
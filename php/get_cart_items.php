<?php
/**
 * Get Cart Items API
 * Returns cart items in JSON format for the payment page
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

try {
    // Query unpaid bookings (cart items)
    $sql = "
        SELECT 
            b.BookingID,
            b.HotelID,
            b.RoomType,
            b.CheckInDate,
            b.CheckOutDate,
            b.RoomQuantity,
            b.BookingDate,
            h.HotelName,
            r.RoomPrice
        FROM booking b
        JOIN hotel h ON b.HotelID = h.HotelID
        JOIN room r ON r.HotelID = b.HotelID AND r.RoomType = b.RoomType
        WHERE b.TenantID = ?
        ORDER BY b.BookingDate DESC
    ";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    $booking_ids = [];
    $subtotal = 0;
    
    while ($row = $result->fetch_assoc()) {
        // Calculate days
        $checkin = new DateTime($row['CheckInDate'] ?? 'now');
        $checkout = new DateTime($row['CheckOutDate'] ?? 'tomorrow');
        $days = max(1, $checkin->diff($checkout)->days);
        
        // Calculate item subtotal
        $quantity = $row['RoomQuantity'] ?? 1;
        $item_subtotal = $row['RoomPrice'] * $days * $quantity;
        $subtotal += $item_subtotal;
        
        $booking_ids[] = $row['BookingID'];
        
        $items[] = [
            'booking_id' => $row['BookingID'],
            'hotel_id' => $row['HotelID'],
            'hotel_name' => $row['HotelName'],
            'room_type' => $row['RoomType'],
            'check_in' => $checkin->format('d M Y'),
            'check_out' => $checkout->format('d M Y'),
            'quantity' => $quantity,
            'days' => $days,
            'price_per_night' => floatval($row['RoomPrice']),
            'subtotal' => $item_subtotal
        ];
    }
    
    // Calculate tax (6% SST for Malaysia)
    $tax_rate = 0.06;
    $tax = $subtotal * $tax_rate;
    $total = $subtotal + $tax;
    
    // Get user info for pre-filling form
    $user_sql = "SELECT Email, PhoneNo FROM tenant WHERE TenantID = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $tenant_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'booking_ids' => $booking_ids,
        'subtotal' => round($subtotal, 2),
        'tax' => round($tax, 2),
        'total' => round($total, 2),
        'user_email' => $user['Email'] ?? '',
        'user_phone' => $user['PhoneNo'] ?? ''
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading cart: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
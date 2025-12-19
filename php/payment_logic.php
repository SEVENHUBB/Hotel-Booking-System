<?php
session_start();
if (!isset($_SESSION['TenantID'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$tenantID = $_SESSION['TenantID'];
$bookingID = isset($_GET['bookingID']) ? intval($_GET['bookingID']) : 0;

$paymentError = '';
$booking = null;
$subtotal = 0;
$tax = 0;
$totalAmount = 0;

if ($bookingID <= 0) {
    $paymentError = "Invalid booking ID.";
} else {
    // Fetch booking
    $stmt = $pdo->prepare("SELECT b.*, r.RoomPrice, r.RoomType, r.RoomStatus, h.HotelName,
        DATEDIFF(b.CheckOutDate, b.CheckInDate) AS Nights
        FROM booking b
        JOIN room r ON b.RoomID = r.RoomID
        JOIN hotel h ON r.HotelID = h.HotelID
        WHERE b.BookingID = :bookingID AND b.TenantID = :tenantID");
    $stmt->execute(['bookingID' => $bookingID, 'tenantID' => $tenantID]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking || $booking['RoomStatus'] !== 'Available') {
        $paymentError = "Invalid or unavailable booking.";
    } else {
        // Calculate total
        $subtotal = $booking['RoomPrice'] * $booking['Nights'];
        $tax = $subtotal * 0.10;
        $totalAmount = $subtotal + $tax;

        // Check if already paid
        $stmt = $pdo->prepare("SELECT PaymentID FROM payment WHERE BookingID = :bookingID");
        $stmt->execute(['bookingID' => $bookingID]);
        if ($existing = $stmt->fetch()) {
            header("Location: ../receipt.php?paymentID=" . $existing['PaymentID']);
            exit();
        }
    }
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($paymentError)) {
    $paymentMethod = $_POST['paymentMethod'] ?? '';

    if (empty($paymentMethod)) {
        $paymentError = "Please select a payment method.";
    } else {
        $transactionID = 'TXN' . rand(100000, 999999);
        $paymentStatus = 'Paid';

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO payment 
                (BookingID, Amount, PaymentMethod, PaymentStatus, TransactionID, PaymentDate, Currency)
                VALUES (:bookingID, :amount, :method, :status, :transID, NOW(), 'MYR')");
            $stmt->execute([
                'bookingID' => $bookingID,
                'amount' => $totalAmount,
                'method' => $paymentMethod,
                'status' => $paymentStatus,
                'transID' => $transactionID
            ]);
            $paymentID = $pdo->lastInsertId();

            $stmt = $pdo->prepare("UPDATE room SET RoomStatus = 'Booked' WHERE RoomID = :roomID");
            $stmt->execute(['roomID' => $booking['RoomID']]);

            $pdo->commit();
            header("Location: ../receipt.php?paymentID=$paymentID");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $paymentError = "Payment failed. Please try again.";
        }
    }
}
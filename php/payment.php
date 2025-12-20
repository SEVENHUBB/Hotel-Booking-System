<?php
session_start();

if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$tenantID = (int)$_SESSION['tenant_id'];

$paymentID = isset($_GET['paymentID']) ? intval($_GET['paymentID']) : 0;

$receipt = null;
$subtotal = 0;
$tax = 0;
$totalAmount = 0;
$billID = 0;

if ($paymentID <= 0) {
    die("Invalid payment ID.");
}

// Fetch data
$stmt = $pdo->prepare("SELECT t.FullName, t.Email, t.PhoneNo,
    h.HotelName, r.RoomType, r.RoomPrice,
    b.CheckInDate, b.CheckOutDate, b.NumberOfTenant,
    p.Amount, p.PaymentMethod, p.PaymentStatus, p.TransactionID, p.PaymentDate,
    DATEDIFF(b.CheckOutDate, b.CheckInDate) AS Nights
    FROM payment p
    JOIN booking b ON p.BookingID = b.BookingID
    JOIN room r ON b.RoomID = r.RoomID
    JOIN hotel h ON r.HotelID = h.HotelID
    JOIN tenant t ON b.TenantID = t.TenantID
    WHERE p.PaymentID = :paymentID AND b.TenantID = :tenantID");
$stmt->execute(['paymentID' => $paymentID, 'tenantID' => $tenantID]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$receipt || $receipt['PaymentStatus'] !== 'Paid') {
    die("Invalid or unpaid receipt.");
}

$subtotal = $receipt['RoomPrice'] * $receipt['Nights'];
$tax = $subtotal * 0.10;
$totalAmount = $subtotal + $tax;
$itemized = json_encode(['room' => $subtotal, 'tax' => $tax, 'total' => $totalAmount]);

// Insert bill if not exists
$stmt = $pdo->prepare("SELECT BillID FROM bill WHERE PaymentID = :paymentID");
$stmt->execute(['paymentID' => $paymentID]);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO bill 
        (PaymentID, TotalAmount, ItemizedDetails, GenerationDate, ReceiptStatus,
         TenantName, PhoneNo, Gender, Email, Country)
        VALUES (:paymentID, :total, :itemized, NOW(), 'Generated',
         :name, :phone, '', :email, '')");
    $stmt->execute([
        'paymentID' => $paymentID,
        'total' => $totalAmount,
        'itemized' => $itemized,
        'name' => $receipt['FullName'],
        'phone' => $receipt['PhoneNo'],
        'email' => $receipt['Email']
    ]);
    $billID = $pdo->lastInsertId();
} else {
    $stmt = $pdo->prepare("SELECT BillID FROM bill WHERE PaymentID = :paymentID");
    $stmt->execute(['paymentID' => $paymentID]);
    $billID = $stmt->fetchColumn();
}
<?php
include "db_hotel.php";

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if ($hotel_id <= 0) {
    die("Invalid hotel ID");
}

// check hotel information
$stmt = $conn->prepare("SELECT * FROM hotel WHERE HotelID = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

if (!$hotel) {
    die("Hotel not found");
}

// Query room information
$stmt2 = $conn->prepare("
    SELECT RoomType, RoomPrice, RoomDesc, RoomImage, Capacity, RoomQuantity
    FROM room
    WHERE HotelID = ? AND RoomStatus = 'Available'
");
$stmt2->bind_param("i", $hotel_id);
$stmt2->execute();
$rooms = $rooms_result = $stmt2->get_result();

// Prepare the data to be passed to HTML
$hotel_name = htmlspecialchars($hotel['HotelName']);
$hotel_description = htmlspecialchars($hotel['Description']);
$min_checkin_date = date('Y-m-d');
$min_checkout_date = date('Y-m-d', strtotime('+1 day'));

$stmt->close();
$stmt2->close();

include '../room.html';

$conn->close();
?>
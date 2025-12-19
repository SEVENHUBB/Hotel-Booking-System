<?php
include "db_hotel.php";

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if ($hotel_id <= 0) {
    die("Invalid hotel ID");
}

// 查询酒店信息
$stmt = $conn->prepare("SELECT * FROM hotel WHERE HotelID = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

if (!$hotel) {
    die("Hotel not found");
}

// 查询房间信息
$stmt2 = $conn->prepare("
    SELECT RoomType, RoomPrice, RoomDesc, RoomImage, Capacity, RoomQuantity
    FROM room
    WHERE HotelID = ? AND RoomStatus = 'Available'
");
$stmt2->bind_param("i", $hotel_id);
$stmt2->execute();
$rooms = $rooms_result = $stmt2->get_result();

// 准备要传递给 HTML 的数据
$hotel_name = htmlspecialchars($hotel['HotelName']);
$hotel_description = htmlspecialchars($hotel['Description']);
$min_checkin_date = date('Y-m-d');
$min_checkout_date = date('Y-m-d', strtotime('+1 day'));

// 关闭 statement
$stmt->close();
$stmt2->close();

// 引入 HTML 文件
include '../room.html';

// 关闭连接
$conn->close();
?>
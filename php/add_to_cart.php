<?php
session_start();
include 'db_hotel.php';

// 获取表单数据
$hotel_id = $_POST['HotelID'];
$room_type = $_POST['RoomType'];
$room_price = $_POST['RoomPrice'];
$room_qty = $_POST['qty'];
$checkin_date = $_POST['checkin_date'];
$checkout_date = $_POST['checkout_date'];
$tenant_id = $_SESSION['tenant_id'] ?? null;

if (!$tenant_id) {
    die("Please login first.");
}

// 1️⃣ 检查库存总量
$stmtRoom = $conn->prepare("
    SELECT RoomQuantity 
    FROM room 
    WHERE HotelID = ? AND RoomType = ? AND RoomStatus='Available'
");
$stmtRoom->bind_param("is", $hotel_id, $room_type);
$stmtRoom->execute();
$resultRoom = $stmtRoom->get_result();
$rowRoom = $resultRoom->fetch_assoc();

if (!$rowRoom) {
    die("Room not found.");
}

$total_available = $rowRoom['RoomQuantity'];

// 2️⃣ 检查日期冲突（已有预订）
$stmtBooking = $conn->prepare("
    SELECT SUM(RoomQuantity) as booked
    FROM booking
    WHERE HotelID = ? AND RoomType = ? AND (
        (CheckInDate <= ? AND CheckOutDate > ?) OR
        (CheckInDate < ? AND CheckOutDate >= ?)
    )
");
$stmtBooking->bind_param("isssss", $hotel_id, $room_type, $checkin_date, $checkin_date, $checkout_date, $checkout_date);
$stmtBooking->execute();
$resultBooking = $stmtBooking->get_result();
$rowBooking = $resultBooking->fetch_assoc();

$booked_qty = $rowBooking['booked'] ?? 0;

// 可用数量 = 总库存 - 已预订数量
$available_qty = $total_available - $booked_qty;

if ($room_qty > $available_qty) {
    die("Not enough rooms available for selected dates. Available: $available_qty");
}

// 3️⃣ 插入 booking 表
$stmtInsert = $conn->prepare("
    INSERT INTO booking (HotelID, RoomType, TenantID, CheckInDate, CheckOutDate, RoomQuantity, BookingDate)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmtInsert->bind_param("isissi", $hotel_id, $room_type, $tenant_id, $checkin_date, $checkout_date, $room_qty);

if ($stmtInsert->execute()) {
    // 可选：更新 room 表库存（如果想在这里直接扣库存）
    // $stmtUpdate = $conn->prepare("UPDATE room SET RoomQuantity = RoomQuantity - ? WHERE HotelID=? AND RoomType=?");
    // $stmtUpdate->bind_param("iis", $room_qty, $hotel_id, $room_type);
    // $stmtUpdate->execute();

    // 跳转到购物车页面
    header("Location: cart.php");
    exit();
} else {
    die("Error adding to cart: " . $stmtInsert->error);
}

<?php
session_start();
include 'db_hotel.php';

// 超时设置（秒）
$timeout = 2 * 60; // 2分钟

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    die("Please login first.");
}

// 获取表单数据
$hotel_id = isset($_POST['HotelID']) ? (int)$_POST['HotelID'] : 0;
$room_type = $_POST['RoomType'] ?? '';
$room_qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
$checkin_date = $_POST['checkin_date'] ?? '';
$checkout_date = $_POST['checkout_date'] ?? '';

if ($hotel_id <= 0 || empty($room_type) || $room_qty <= 0 || empty($checkin_date) || empty($checkout_date)) {
    die("Invalid input.");
}

// 1️⃣ 清理超时未付款订单，恢复库存
$stmtExpired = $conn->prepare("
    SELECT BookingID, HotelID, RoomType, RoomQuantity
    FROM booking
    WHERE TenantID=? AND Status='UNPAID' AND TIMESTAMPDIFF(SECOND, BookingDate, NOW()) > ?
");
$stmtExpired->bind_param("ii", $tenant_id, $timeout);
$stmtExpired->execute();
$expired = $stmtExpired->get_result();

while ($row = $expired->fetch_assoc()) {
    // 恢复库存
    $stmtRestore = $conn->prepare("
        UPDATE room 
        SET RoomQuantity = RoomQuantity + ? 
        WHERE HotelID=? AND RoomType=?
    ");
    $stmtRestore->bind_param("iis", $row['RoomQuantity'], $row['HotelID'], $row['RoomType']);
    $stmtRestore->execute();

    // 删除过期订单
    $stmtDel = $conn->prepare("DELETE FROM booking WHERE BookingID=?");
    $stmtDel->bind_param("i", $row['BookingID']);
    $stmtDel->execute();
}

// 2️⃣ 查询房间总库存
$stmtRoom = $conn->prepare("
    SELECT RoomQuantity 
    FROM room 
    WHERE HotelID=? AND RoomType=? AND RoomStatus='Available'
");
$stmtRoom->bind_param("is", $hotel_id, $room_type);
$stmtRoom->execute();
$resultRoom = $stmtRoom->get_result();
$rowRoom = $resultRoom->fetch_assoc();

if (!$rowRoom) {
    die("Room not found.");
}

$total_available = $rowRoom['RoomQuantity'];

// 3️⃣ 检查已有未付款订单日期冲突
$stmtBooking = $conn->prepare("
    SELECT SUM(RoomQuantity) AS booked
    FROM booking
    WHERE HotelID=? AND RoomType=? AND Status='UNPAID' AND (
        (CheckInDate <= ? AND CheckOutDate > ?) OR
        (CheckInDate < ? AND CheckOutDate >= ?)
    )
");
$stmtBooking->bind_param("isssss", $hotel_id, $room_type, $checkin_date, $checkin_date, $checkout_date, $checkout_date);
$stmtBooking->execute();
$resultBooking = $stmtBooking->get_result();
$rowBooking = $resultBooking->fetch_assoc();

$booked_qty = $rowBooking['booked'] ?? 0;
$available_qty = $total_available - $booked_qty;

if ($room_qty > $available_qty) {
    die("Not enough rooms available for selected dates. Available: $available_qty");
}

// 4️⃣ 插入 booking 表（UNPAID）
$stmtInsert = $conn->prepare("
    INSERT INTO booking (HotelID, RoomType, TenantID, CheckInDate, CheckOutDate, RoomQuantity, Status, BookingDate)
    VALUES (?, ?, ?, ?, ?, ?, 'UNPAID', NOW())
");
$stmtInsert->bind_param("isissi", $hotel_id, $room_type, $tenant_id, $checkin_date, $checkout_date, $room_qty);
if (!$stmtInsert->execute()) {
    die("Error adding to cart: " . $stmtInsert->error);
}

// 5️⃣ 扣库存
$stmtUpdate = $conn->prepare("
    UPDATE room 
    SET RoomQuantity = RoomQuantity - ? 
    WHERE HotelID=? AND RoomType=? AND RoomQuantity >= ?
");
$stmtUpdate->bind_param("iisi", $room_qty, $hotel_id, $room_type, $room_qty);
$stmtUpdate->execute();

if ($stmtUpdate->affected_rows === 0) {
    // 如果扣库存失败，回滚订单
    $stmtDel = $conn->prepare("DELETE FROM booking WHERE BookingID=?");
    $stmtDel->bind_param("i", $stmtInsert->insert_id);
    $stmtDel->execute();
    die("Failed to deduct room quantity. Possibly not enough stock.");
}

// 6️⃣ 重定向到购物车
header("Location: cart.php");
exit();

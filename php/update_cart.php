<?php
session_start();
include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    die("Please login first.");
}

// 获取表单数据
$qty_arr = $_POST['qty'] ?? [];
$checkin_arr = $_POST['checkin'] ?? [];
$checkout_arr = $_POST['checkout'] ?? [];

foreach ($qty_arr as $booking_id => $qty) {
    $checkin_date = $checkin_arr[$booking_id] ?? null;
    $checkout_date = $checkout_arr[$booking_id] ?? null;

    if (!$checkin_date || !$checkout_date || $qty <= 0) {
        continue; // 输入不合法就跳过
    }

    // 1️⃣ 获取该 booking 的 HotelID 和 RoomType
    $stmtInfo = $conn->prepare("SELECT HotelID, RoomType FROM booking WHERE BookingID=? AND TenantID=?");
    if (!$stmtInfo) die("Prepare failed: " . $conn->error);

    $stmtInfo->bind_param("ii", $booking_id, $tenant_id);
    $stmtInfo->execute();
    $stmtInfo->bind_result($hotel_id, $room_type);
    if (!$stmtInfo->fetch()) {
        $stmtInfo->close();
        continue; // 找不到 booking 就跳过
    }
    $stmtInfo->close();

    // 2️⃣ 查询当前可用库存（room.RoomQuantity 已经扣掉未付款订单）
    $stmtRoom = $conn->prepare("SELECT RoomQuantity FROM room WHERE HotelID=? AND RoomType=? AND RoomStatus='Available'");
    if (!$stmtRoom) die("Prepare failed: " . $conn->error);

    $stmtRoom->bind_param("is", $hotel_id, $room_type);
    $stmtRoom->execute();
    $stmtRoom->bind_result($available_qty);
    if (!$stmtRoom->fetch()) {
        $stmtRoom->close();
        continue; // 找不到房间就跳过
    }
    $stmtRoom->close();

    // 3️⃣ 允许用户更新数量，不能超过当前可用库存 + 当前 booking 数量
    // 因为 room.RoomQuantity 已经扣了当前 booking 的数量
    $stmtCurrent = $conn->prepare("SELECT RoomQuantity FROM booking WHERE BookingID=?");
    $stmtCurrent->bind_param("i", $booking_id);
    $stmtCurrent->execute();
    $stmtCurrent->bind_result($current_qty);
    $stmtCurrent->fetch();
    $stmtCurrent->close();

    $max_allowed = $available_qty + $current_qty; // 最大允许数量
    if ($qty > $max_allowed) {
        die("Not enough rooms available for $room_type from $checkin_date to $checkout_date. Max allowed: $max_allowed");
    }

    // 4️⃣ 更新 booking
    $stmtUpdate = $conn->prepare("
        UPDATE booking
        SET RoomQuantity=?, CheckInDate=?, CheckOutDate=?
        WHERE BookingID=? AND TenantID=?
    ");
    if (!$stmtUpdate) die("Prepare failed: " . $conn->error);

    $stmtUpdate->bind_param("issii", $qty, $checkin_date, $checkout_date, $booking_id, $tenant_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // 5️⃣ 更新 room 表库存
    $diff = $current_qty - $qty; // 如果用户减少数量，库存要增加；增加数量，库存要减少
    if ($diff != 0) {
        $stmtRoomUpdate = $conn->prepare("
            UPDATE room
            SET RoomQuantity = RoomQuantity + ?
            WHERE HotelID=? AND RoomType=?
        ");
        $stmtRoomUpdate->bind_param("iis", $diff, $hotel_id, $room_type);
        $stmtRoomUpdate->execute();
        $stmtRoomUpdate->close();
    }
}

// 6️⃣ 完成后跳转回购物车页面
header("Location: cart.php");
exit();

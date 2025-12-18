<?php
session_start();
include 'db_hotel.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
if (!$tenant_id) {
    die("Please login first.");
}

$qty_arr = $_POST['qty'] ?? [];
$checkin_arr = $_POST['checkin'] ?? [];
$checkout_arr = $_POST['checkout'] ?? [];

foreach ($qty_arr as $booking_id => $qty) {
    $checkin_date = $checkin_arr[$booking_id];
    $checkout_date = $checkout_arr[$booking_id];

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

    // 2️⃣ 查询总库存
    $stmtRoom = $conn->prepare("SELECT RoomQuantity FROM room WHERE HotelID=? AND RoomType=? AND RoomStatus='Available'");
    if (!$stmtRoom) die("Prepare failed: " . $conn->error);
    $stmtRoom->bind_param("is", $hotel_id, $room_type);
    $stmtRoom->execute();
    $stmtRoom->bind_result($total_available);
    if (!$stmtRoom->fetch()) {
        $stmtRoom->close();
        continue; // 找不到房间就跳过
    }
    $stmtRoom->close();

    // 3️⃣ 查询已有预订数量（排除当前 booking）
    $stmtBooked = $conn->prepare("
        SELECT SUM(RoomQuantity) as booked
        FROM booking
        WHERE HotelID=? AND RoomType=? AND BookingID!=? AND (
            (CheckInDate <= ? AND CheckOutDate > ?) OR
            (CheckInDate < ? AND CheckOutDate >= ?)
        )
    ");
    if (!$stmtBooked) die("Prepare failed: " . $conn->error);

    $stmtBooked->bind_param("isissss", $hotel_id, $room_type, $booking_id, $checkin_date, $checkin_date, $checkout_date, $checkout_date);
    $stmtBooked->execute();
    $stmtBooked->bind_result($booked_qty);
    $stmtBooked->fetch();
    $booked_qty = $booked_qty ?? 0;
    $stmtBooked->close();

    // 4️⃣ 检查库存
    $available_qty = $total_available - $booked_qty;
    if ($qty > $available_qty) {
        die("Not enough rooms available for $room_type from $checkin_date to $checkout_date. Available: $available_qty");
    }

    // 5️⃣ 更新 booking
    $stmtUpdate = $conn->prepare("
        UPDATE booking
        SET RoomQuantity=?, CheckInDate=?, CheckOutDate=?
        WHERE BookingID=? AND TenantID=?
    ");
    if (!$stmtUpdate) die("Prepare failed: " . $conn->error);

    $stmtUpdate->bind_param("issii", $qty, $checkin_date, $checkout_date, $booking_id, $tenant_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}

// 完成后跳转回购物车页面
header("Location: cart.php");
exit();

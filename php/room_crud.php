<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';
$uploadDir = '../images/room_photo/';
$imagePath = null; // This will be NULL if no image

// Create upload folder if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($action === "create") {

    $imagePath = null;

    // ===== IMAGE UPLOAD =====
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === 0) {
        $file = $_FILES['room_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $newName = uniqid('room_') . '.' . $ext;
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'images/room_photo/' . $newName;
            }
        }
    }

    // ===== INSERT (TenantID 直接 NULL，不管它) =====
    $stmt = $conn->prepare("
        INSERT INTO room
        (HotelID, TenantID, RoomType, RoomPrice, RoomDesc, RoomImage, RoomStatus, Capacity)
        VALUES (?, NULL, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isdsssi",
        $_POST['HotelID'],     // HotelID
        $_POST['RoomType'],    // RoomType
        $_POST['RoomPrice'],   // RoomPrice
        $_POST['RoomDesc'],    // RoomDesc
        $imagePath,            // ✅ RoomImage
        $_POST['RoomStatus'],  // RoomStatus
        $_POST['Capacity']     // Capacity
    );

    $success = $stmt->execute();

    echo json_encode([
        "success" => $success,
        "error" => $stmt->error,
        "imagePath" => $imagePath
    ]);
    exit;
}

if ($action === "read") {
    $result = $conn->query("SELECT r.*, h.HotelName FROM room r LEFT JOIN hotel h ON r.HotelID = h.HotelID ORDER BY r.RoomID ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

if ($action === "delete") {
    $stmt = $conn->prepare("DELETE FROM room WHERE RoomID = ?");
    $stmt->bind_param("i", $_POST['RoomID']);
    $stmt->execute();
    echo json_encode(["success" => true]);
    exit;
}

// 返回所有酒店用于下拉列表
if ($action === "hotels") {
    $result = $conn->query("SELECT HotelID, HotelName FROM hotel ORDER BY HotelName ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

echo json_encode(["error" => "Invalid action"]);

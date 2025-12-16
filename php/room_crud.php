<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

if ($action === "create") {
    $stmt = $conn->prepare("INSERT INTO room (HotelID, RoomType, RoomPrice, RoomDesc, RoomStatus, Capacity) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "isdssi",
        $_POST['HotelID'],
        $_POST['RoomType'],
        $_POST['RoomPrice'],
        $_POST['RoomDesc'],
        $_POST['RoomStatus'],
        $_POST['Capacity']
    );
    $success = $stmt->execute();
    echo json_encode(["success" => $success, "error" => $stmt->error]);
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

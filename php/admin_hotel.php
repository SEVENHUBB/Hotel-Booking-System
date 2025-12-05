<?php
session_start();


header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

if ($action === "create") {
    $stmt = $conn->prepare("INSERT INTO hotel 
        (HotelID, HotelName, Description, Address, City, Country, NumRooms, Category, StarRating)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssisi",
        $_POST['HotelID'],
        $_POST['HotelName'],
        $_POST['Description'],
        $_POST['Address'],
        $_POST['City'],
        $_POST['Country'],
        $_POST['NumRooms'],
        $_POST['Category'],
        $_POST['StarRating']
    );
    echo json_encode(["success" => $stmt->execute(), "error" => $stmt->error]);
    exit;
}

if ($action === "read") {
    $result = $conn->query("SELECT * FROM hotel ORDER BY HotelID ASC");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

if ($action === "delete") {
    $id = intval($_POST['HotelID'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM hotel WHERE HotelID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
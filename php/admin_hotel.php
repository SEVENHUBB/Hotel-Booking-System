<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';
$uploadDir = '../images/hotel_photo/';
$imagePath = null; // This will be NULL if no image

// Create upload folder if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($action === "create") {
    // === IMAGE UPLOAD ===
    if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] === 0) {
        $file = $_FILES['hotel_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $newName = uniqid('hotel_') . '.' . $ext;
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'uploads/hotels/' . $newName;
            }
        }
    }

    // === INSERT INTO DATABASE ===
    $stmt = $conn->prepare("INSERT INTO hotel 
        (HotelID, HotelName, Description, Address, City, Country, NumRooms, Category, StarRating, ImagePath)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Use correct types: i = int, s = string, ImagePath can be NULL
    $stmt->bind_param("isssssisss",
        $_POST['HotelID'],
        $_POST['HotelName'],
        $_POST['Description'],
        $_POST['Address'],
        $_POST['City'],
        $_POST['Country'],
        $_POST['NumRooms'],
        $_POST['Category'],
        $_POST['StarRating'],
        $imagePath  // This is either a string or NULL
    );

    $success = $stmt->execute();
    echo json_encode([
        "success" => $success,
        "error" => $stmt->error,
        "imagePath" => $imagePath // for debugging
    ]);
    exit;
}

// READ - show hotels with image
if ($action === "read") {
    $result = $conn->query("SELECT * FROM hotel ORDER BY HotelID ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

// DELETE
if ($action === "delete") {
    $id = (int)$_POST['HotelID'];

    // Delete image file too
    $stmt = $conn->prepare("SELECT ImagePath FROM hotel WHERE HotelID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $img = '../' . $row['ImagePath'];
        if ($row['ImagePath'] && file_exists($img)) {
            unlink($img);
        }
    }

    $stmt = $conn->prepare("DELETE FROM hotel WHERE HotelID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
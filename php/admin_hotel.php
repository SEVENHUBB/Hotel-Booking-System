<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';
$uploadDir = '../images/hotel_photo/';
$imagePath = null;

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
                $imagePath = 'images/hotel_photo/' . $newName;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO hotel 
        (HotelID, HotelName, Description, Address, City, Country, NumRooms, Category, StarRating, ImagePath)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
        $imagePath
    );

    $success = $stmt->execute();
    echo json_encode([
        "success" => $success,
        "error" => $stmt->error
    ]);
    exit;
}

// NEW: UPDATE ACTION
if ($action === "update") {
    $id = (int)$_POST['HotelID'];

    // Handle new image (if uploaded)
    if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] === 0) {
        $file = $_FILES['hotel_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $newName = uniqid('hotel_') . '.' . $ext;
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'images/hotel_photo/' . $newName;

                // Delete old image
                $stmt = $conn->prepare("SELECT ImagePath FROM hotel WHERE HotelID = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc() && $row['ImagePath']) {
                    $oldImg = '../' . $row['ImagePath'];
                    if (file_exists($oldImg)) unlink($oldImg);
                }
            }
        }
    }

    // Build dynamic update
    $fields = [];
    $types = "";
    $values = [];

    $map = [
        'HotelName' => 's',
        'Description' => 's',
        'Address' => 's',
        'City' => 's',
        'Country' => 's',
        'NumRooms' => 'i',
        'Category' => 's',
        'StarRating' => 'i'
    ];

    foreach ($map as $field => $type) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $fields[] = "$field = ?";
            $types .= $type;
            $values[] = $_POST[$field];
        }
    }

    if ($imagePath !== null) {
        $fields[] = "ImagePath = ?";
        $types .= "s";
        $values[] = $imagePath;
    }

    if (empty($fields)) {
        echo json_encode(["success" => false, "error" => "No changes made"]);
        exit;
    }

    $sql = "UPDATE hotel SET " . implode(", ", $fields) . " WHERE HotelID = ?";
    $types .= "i";
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $success = $stmt->execute();

    echo json_encode([
        "success" => $success,
        "error" => $stmt->error
    ]);
    exit;
}

// READ
if ($action === "read") {
    $result = $conn->query("SELECT * FROM hotel ORDER BY HotelID ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

// DELETE
if ($action === "delete") {
    $id = (int)$_POST['HotelID'];

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
?>
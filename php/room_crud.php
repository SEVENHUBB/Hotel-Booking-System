<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';
$uploadDir = '../images/room_photo/';
$imagePath = null;

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($action === "create" || $action === "update") {
    $imagePath = null;

    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === 0) {
        $file = $_FILES['room_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if (in_array($ext, $allowed) && $file['size'] <= 5*1024*1024) {
            $newName = uniqid('room_') . '.' . $ext;
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'images/room_photo/' . $newName;

                // If updating, delete old image
                if ($action === "update") {
                    $stmt = $conn->prepare("SELECT RoomImage FROM room WHERE RoomID = ?");
                    $stmt->bind_param("i", $_POST['RoomID']);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc() && $row['RoomImage']) {
                        $old = '../' . $row['RoomImage'];
                        if (file_exists($old)) unlink($old);
                    }
                }
            }
        }
    }

    if ($action === "create") {
        $stmt = $conn->prepare("
            INSERT INTO room
            (HotelID, TenantID, RoomType, RoomPrice, RoomDesc, RoomImage, RoomStatus, Capacity, RoomQuantity)
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isdsssii",
            $_POST['HotelID'],
            $_POST['RoomType'],
            $_POST['RoomPrice'],
            $_POST['RoomDesc'],
            $imagePath,
            $_POST['RoomStatus'],
            $_POST['Capacity'],
            $_POST['RoomQuantity']
        );
    } else { // update
        $id = (int)$_POST['RoomID'];
        $fields = []; $types = ""; $values = [];

        $map = [
            'HotelID' => ['i', $_POST['HotelID']],
            'RoomType' => ['s', $_POST['RoomType']],
            'RoomPrice' => ['d', $_POST['RoomPrice']],
            'RoomDesc' => ['s', $_POST['RoomDesc'] ?? null],
            'RoomStatus' => ['s', $_POST['RoomStatus']],
            'Capacity' => ['i', $_POST['Capacity']],
            'RoomQuantity' => ['i', $_POST['RoomQuantity']]
        ];

        foreach ($map as $field => $info) {
            $fields[] = "$field = ?";
            $types .= $info[0];
            $values[] = $info[1];
        }

        if ($imagePath) {
            $fields[] = "RoomImage = ?";
            $types .= "s";
            $values[] = $imagePath;
        }

        $sql = "UPDATE room SET " . implode(", ", $fields) . " WHERE RoomID = ?";
        $types .= "i";
        $values[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
    }

    $success = $stmt->execute();

    echo json_encode([
        "success" => $success,
        "error" => $stmt->error
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
    $id = (int)$_POST['RoomID'];

    $stmt = $conn->prepare("SELECT RoomImage FROM room WHERE RoomID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc() && $row['RoomImage']) {
        $img = '../' . $row['RoomImage'];
        if (file_exists($img)) unlink($img);
    }

    $stmt = $conn->prepare("DELETE FROM room WHERE RoomID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

if ($action === "hotels") {
    $result = $conn->query("SELECT HotelID, HotelName FROM hotel ORDER BY HotelName ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
?>
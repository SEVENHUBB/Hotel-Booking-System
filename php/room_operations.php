<?php
// include 'db_room.php';
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    switch($_POST['action']) {
        case 'create':
            createRoom($conn, $_POST, $_FILES);
            break;
        case 'read':
            readRoom($conn);
            break;
        case 'update':
            updateRoom($conn, $_POST, $_FILES);
            break;
        case 'delete':
            deleteRoom($conn, $_POST['id']);
            break;
        case 'get_single':
            getSingleRoom($conn, $_POST['id']);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

// upload Image
function uploadImage($file) {
    $uploadDir = "uploads/room/";

    // Create folder if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    }

    return false;
}


// Create Room
function createRoom($conn, $data, $files) {
    try {
        $imagePath = null;
        
        // Handle image upload
        if (isset($files['roomImage']) && $files['roomImage']['error'] === 0) {
            $imagePath = uploadImage($files['roomImage']);
            if (!$imagePath) {
                echo json_encode(['success' => false, 'message' => 'Image upload failed']);
                return;
            }
        }
        
        // 👇 改这里：RoomImage 改成 ImagePath
        $sql = "INSERT INTO room (HotelID, TenantID, RoomType, RoomPrice, RoomDesc, ImagePath, RoomStatus, Capacity) 
                VALUES (:hotelid, :tenantid, :roomtype, :roomprice, :roomdesc, :imagepath, :roomstatus, :capacity)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            // ':hotelid' => $data['hotelId'],
            // ':tenantid' => !empty($data['tenantId']) ? $data['tenantId'] : null,
            ':roomtype' => $data['roomType'],
            ':roomprice' => $data['roomPrice'],
            ':roomdesc' => $data['roomDesc'],
            ':imagepath' => $imagePath,  // new
            ':roomstatus' => $data['roomStatus'],
            ':capacity' => $data['capacity']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Room created successfully!']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Read All Rooms
function readRoom($conn) {
    try {
        $sql = "SELECT * FROM room ORDER BY RoomID DESC";
        $stmt = $conn->query($sql);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $rooms]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Get Single Room
function getSingleRoom($conn, $id) {
    try {
        $sql = "SELECT * FROM room WHERE RoomID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $room]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Update Room
function updateRoom($conn, $data, $files) {
    try {

        // Get image
        $sql = "SELECT ImagePath FROM room WHERE RoomID =:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $data['roomId']]);
        $currentRoom = $stmt->fetch(PDO::FETCH_ASSOC);
        $imagePath = $currentRoom['ImagePath'];

        if (isset($files['roomImage']) && $files['roomImage']['error'] === 0) {
            // Delete old image if exists
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            $imagePath = uploadImage($files['roomImage']);
            if (!$imagePath) {
                echo json_encode(['success' => false, 'message' => 'Image upload failed']);
                return;
            }
        }

        $sql = "UPDATE room SET 
                HotelID = :hotelid,
                RoomType = :roomtype,
                RoomPrice = :roomprice,
                RoomDesc = :roomdesc,
                ImagePath = :imagepath,
                RoomStatus = :roomstatus,
                Capacity = :capacity
                WHERE RoomID = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['roomId'],
            ':hotelid' => $data['hotelId'] ?? 1,
            // ':tenantid' => $data['tenantId'] ?? null,
            ':roomtype' => $data['roomType'],
            ':roomprice' => $data['roomPrice'],
            ':roomdesc' => $data['roomDesc'],
            ':imagepath' => $imagePath,  //new
            ':roomstatus' => $data['roomStatus'],
            ':capacity' => $data['capacity']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Room updated successfully!']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Delete Room
function deleteRoom($conn, $id) {
    try {

        $sql = "SELECT ImagePath FROM room WHERE RoomID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);


        $sql = "DELETE FROM room WHERE RoomID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($room && $room['ImagePath'] && file_exists($room['ImagePath'])) {
            unlink($room['ImagePath']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Room deleted successfully!']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
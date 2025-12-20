<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

// Accept action from POST or GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uploadDir = '../images/tenant_photo/';

// Create upload directory if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// READ
if ($action === "read") {
    $result = $conn->query("SELECT * FROM tenant ORDER BY TenantID ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

// DELETE
if ($action === "delete") {
    $id = (int)$_POST['TenantID'];

    // Delete associated profile image if exists
    $stmt = $conn->prepare("SELECT profile_image FROM tenant WHERE TenantID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $img = '../' . $row['profile_image'];
        if ($row['profile_image'] && file_exists($img)) {
            unlink($img);
        }
    }

    // Delete the tenant record
    $stmt = $conn->prepare("DELETE FROM tenant WHERE TenantID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

// Invalid action fallback
echo json_encode(["error" => "Invalid action"]);
?>
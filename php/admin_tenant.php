<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';
$uploadDir = '../images/tenant_photo/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// READ
if ($action === "read") {
    $result = $conn->query("SELECT * FROM tenant ORDER BY TenantID ASC");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

// UPDATE
if ($action === "update") {
    $id = (int)$_POST['TenantID'];
    $imagePath = null;

    if (isset($_FILES['tenant_image']) && $_FILES['tenant_image']['error'] === 0) {
        $file = $_FILES['tenant_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $newName = uniqid('tenant_') . '.' . $ext;
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'images/tenant_photo/' . $newName;

                // Delete old image
                $stmt = $conn->prepare("SELECT ImagePath FROM tenant WHERE TenantID = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc() && $row['ImagePath']) {
                    $old = '../' . $row['ImagePath'];
                    if (file_exists($old)) unlink($old);
                }
            }
        }
    }

    $fields = []; $types = ""; $values = [];

    $map = [
        'RoleID' => 'i', 'TenantName' => 's', 'FullName' => 's',
        'Email' => 's', 'PhoneNo' => 's', 'Gender' => 's', 'Country' => 's'
    ];

    foreach ($map as $field => $type) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $fields[] = "$field = ?";
            $types .= $type;
            $values[] = $_POST[$field];
        }
    }

    if (!empty($_POST['Password'])) {
        $fields[] = "Password = ?";
        $types .= "s";
        $values[] = $_POST['Password']; // Hash in production
    }

    if ($imagePath) {
        $fields[] = "ImagePath = ?";
        $types .= "s";
        $values[] = $imagePath;
    }

    if (empty($fields)) {
        echo json_encode(["success" => false, "error" => "No changes"]);
        exit;
    }

    $sql = "UPDATE tenant SET " . implode(", ", $fields) . " WHERE TenantID = ?";
    $types .= "i";
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $success = $stmt->execute();

    echo json_encode(["success" => $success, "error" => $stmt->error]);
    exit;
}

// DELETE
if ($action === "delete") {
    $id = (int)$_POST['TenantID'];

    $stmt = $conn->prepare("SELECT ImagePath FROM tenant WHERE TenantID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc() && $row['ImagePath']) {
        $img = '../' . $row['ImagePath'];
        if (file_exists($img)) unlink($img);
    }

    $stmt = $conn->prepare("DELETE FROM tenant WHERE TenantID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
?>
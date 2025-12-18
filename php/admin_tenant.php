<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';  // 你的数据库连接文件
$conn = getDBConnection();

$action = $_GET['action'] ?? '';
$uploadDir = '../images/tenant_photo/';
$imagePath = null;  // 可选头像路径

// 创建上传目录（如果不存在）
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($action === "create") {
    // 处理头像上传（可选）
    if (isset($_FILES['tenant_image']) && $_FILES['tenant_image']['error'] === 0) {
        $file = $_FILES['tenant_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $newName = uniqid('tenant_') . '.' . $ext;
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'images/tenant_photo/' . $newName;
            }
        }
    }

    // 插入数据库（严格按照你的表字段顺序和类型）
    $stmt = $conn->prepare("INSERT INTO tenant 
        (TenantID, RoleID, TenantName, Password, PhoneNo, Gender, Email, FullName, Country, ImagePath)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // 类型：i = int, s = string
    $stmt->bind_param("iissssssss",
        $_POST['TenantID'],
        $_POST['RoleID'],
        $_POST['TenantName'],
        $_POST['Password'],
        $_POST['PhoneNo'],
        $_POST['Gender'],
        $_POST['Email'],
        $_POST['FullName'],
        $_POST['Country'],
        $imagePath  // 可以是 string 或 NULL
    );

    $success = $stmt->execute();

    echo json_encode([
        "success" => $success,
        "error"   => $stmt->error,
        "imagePath" => $imagePath
    ]);
    exit;
}

if ($action === "read") {
    $result = $conn->query("SELECT * FROM tenant ORDER BY TenantID ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    exit;
}

if ($action === "delete") {
    $id = (int)$_POST['TenantID'];

    // 删除关联的头像文件
    $stmt = $conn->prepare("SELECT ImagePath FROM tenant WHERE TenantID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $img = '../' . $row['ImagePath'];
        if ($row['ImagePath'] && file_exists($img)) {
            unlink($img);
        }
    }

    // 删除记录
    $stmt = $conn->prepare("DELETE FROM tenant WHERE TenantID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
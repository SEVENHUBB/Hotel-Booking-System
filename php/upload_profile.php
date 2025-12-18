<?php
session_start();
include "db_config.php";

if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();   // ⭐ 同样要这行

$tenant_id = $_SESSION['tenant_id'];
$name = $_POST['tenant_name'];
$phone = $_POST['phone'];

$profilePath = null;

/* 图片上传 */
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

    $allowed = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        die("Only JPG and PNG allowed");
    }

    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = "profile_" . $tenant_id . "." . $ext;
    $fullPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $fullPath)) {
        $profilePath = "uploads/" . $fileName;
    }
}

/* 更新资料 */
if ($profilePath) {
    $sql = "UPDATE tenant 
            SET TenantName=?, PhoneNo=?, profile_image=? 
            WHERE TenantID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $phone, $profilePath, $tenant_id);
} else {
    $sql = "UPDATE tenant 
            SET TenantName=?, PhoneNo=? 
            WHERE TenantID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $phone, $tenant_id);
}


$stmt->execute();

header("Location: index.php");
exit();

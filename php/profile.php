<?php
session_start();
include "db_config.php";

if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$tenant_id = $_SESSION['tenant_id'];

$sql = "SELECT TenantName, PhoneNo, profile_image 
        FROM tenant 
        WHERE TenantID = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$tenant = $result->fetch_assoc();

// 准备要传递给 HTML 的数据
$profile_image = !empty($tenant['profile_image']) 
    ? $tenant['profile_image'] 
    : 'images/default.png';
$tenant_name = htmlspecialchars($tenant['TenantName']);
$phone_no = htmlspecialchars($tenant['PhoneNo']);

// 关闭连接
$stmt->close();
$conn->close();

// 引入 HTML 文件
include '../profile.html';
?>
<?php
session_start();
header("Content-Type: application/json");
require 'db_config.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

if ($action === "create") {
    $stmt = $conn->prepare("INSERT INTO tenant 
        (TenantID, RoleID, TenantName, Password, PhoneNo, Gender, Email, FullName, Country)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Password hashing for security
    $hashedPassword = password_hash($_POST['Password'], PASSWORD_DEFAULT);
    
    $stmt->execute([
        $_POST['TenantID'],
        $_POST['RoleID'] ?: null,
        $_POST['TenantName'],
        $hashedPassword,
        $_POST['PhoneNo'] ?: null,
        $_POST['Gender'] ?: null,
        $_POST['Email'] ?: null,
        $_POST['FullName'] ?: null,
        $_POST['Country'] ?: null
    ]);

    echo json_encode(["success" => true, "message" => "Tenant added successfully"]);
    exit;
}

if ($action === "read") {
    $stmt = $conn->query("SELECT TenantID, RoleID, TenantName, FullName, Email, PhoneNo, Gender, Country FROM tenant ORDER BY TenantID DESC");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tenants);
    exit;
}

if ($action === "delete") {
    $id = (int)$_POST['TenantID'];
    $stmt = $conn->prepare("DELETE FROM tenant WHERE TenantID = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
?>
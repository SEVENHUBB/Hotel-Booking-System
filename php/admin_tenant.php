<?php
// admin_tenant.php - PHP logic file

// Database connection (UPDATE THESE WITH YOUR ACTUAL DETAILS)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name"; // ← Change this!

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$message_type = '';

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM tenant WHERE TenantID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Tenant deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Error deleting tenant.";
        $message_type = "error";
    }
    $stmt->close();
    
    // Redirect to prevent resubmission on page refresh
    header("Location: admin_tenant.php");
    exit();
}

// Fetch all tenants
$sql = "SELECT TenantID, TenantName, FullName, Email, PhoneNo, Gender, Country, RoleID 
        FROM tenant 
        ORDER BY TenantID DESC";
$result = $conn->query($sql);

$tenants = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tenants[] = $row;
    }
}

$conn->close();
?>
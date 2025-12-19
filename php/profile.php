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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/profile.css">
</head>
<body>

<h2>My Profile</h2>

<form action="upload_profile.php" method="POST" enctype="multipart/form-data">

    <div class="profile-box">
        <img
            id="previewImg"
            class="profile-img"
            src="../<?php 
                echo !empty($tenant['profile_image']) 
                ? $tenant['profile_image'] 
                : 'images/default.png'; 
            ?>"
        >

        <input type="file" name="profile_image" id="profileImage" accept="image/*">
        
        <input type="text" name="tenant_name"
            value="<?php echo htmlspecialchars($tenant['TenantName']); ?>">

        <input type="text" name="phone"
            value="<?php echo htmlspecialchars($tenant['PhoneNo']); ?>">


        <button type="submit">Save Profile</button>
    </div>

</form>

<script src="../js/profile.js"></script>
</body>
</html>

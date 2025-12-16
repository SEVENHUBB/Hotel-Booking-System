<?php
session_start();

$conn = new mysqli("localhost", "root", "", "hotel_booking_system");
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$email = trim($_POST['email']);
$pass  = trim($_POST['password']); // 这里处理密码

$sql = "SELECT * FROM admin WHERE email=?";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

if ($row) {
    if (password_verify($pass, $row['Password'])) { // 注意字段名首字母大写
        $_SESSION['admin_email'] = $email;
        header("Location: ../admin.html");
        exit();
    } else {
        echo "<script>alert('Wrong password!'); window.location.href='../admin-login.html';</script>";
    }
} else {
    echo "<script>alert('Email not found!'); window.location.href='../admin-login.html';</script>";
}

$stmt->close();
$conn->close();
?>

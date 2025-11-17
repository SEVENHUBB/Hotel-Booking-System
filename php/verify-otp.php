<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    // 验证输入
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.history.back();</script>";
        exit();
    }

    if (!preg_match('/^\d{6}$/', $otp)) {
        echo "<script>alert('Invalid OTP format'); window.history.back();</script>";
        exit();
    }

    $conn = getDBConnection();

    // 查询 OTP
    $stmt = $conn->prepare("SELECT otp, expires_at FROM password_reset_otp WHERE email = ?");
    
    if ($stmt === false) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('OTP not found or expired. Please request a new one.'); window.location.href='../forgot-password.html';</script>";
        exit();
    }

    $row = $result->fetch_assoc();
    $storedOtp = $row['otp'];
    $expiresAt = $row['expires_at'];

    // 检查 OTP 是否过期
    if (strtotime($expiresAt) < time()) {
        // 删除过期的 OTP
        $deleteStmt = $conn->prepare("DELETE FROM password_reset_otp WHERE email = ?");
        $deleteStmt->bind_param("s", $email);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        echo "<script>alert('OTP has expired. Please request a new one.'); window.location.href='../forgot-password.html';</script>";
        exit();
    }

    // 验证 OTP
    if ($otp !== $storedOtp) {
        echo "<script>alert('Invalid OTP. Please try again.'); window.history.back();</script>";
        exit();
    }

    // OTP 验证成功，跳转到重置密码页面
    echo "<script>alert('OTP verified successfully!'); window.location.href='../reset-password.html?email=" . urlencode($email) . "';</script>";

    $stmt->close();
    $conn->close();
    exit;

} else {
    die("Invalid request method");
}
?>
<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // 验证是否已通过OTP验证
    if (!isset($_SESSION['verified_email']) || $_SESSION['verified_email'] !== $email) {
        echo "<script>alert('Unauthorized access. Please verify OTP first.'); window.location.href='../forgot-password.html';</script>";
        exit();
    }
    
    // 验证密码
    if (strlen($new_password) < 6) {
        echo "<script>alert('Password must be at least 6 characters'); window.history.back();</script>";
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit();
    }
    
    // 加密新密码
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // 更新数据库中的密码
    // 示例代码：
    
    $conn = new mysqli("localhost", "username", "password", "hotel_booking");
    
    $update_sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ss", $hashed_password, $email);
    
    if ($stmt->execute()) {
        // 清除session
        unset($_SESSION['verified_email']);
        unset($_SESSION['otp_email']);
        unset($_SESSION['otp_time']);
        
        echo "<script>alert('Password reset successfully! Please login with your new password.'); window.location.href='../login.html';</script>";
    } else {
        echo "<script>alert('Failed to reset password. Please try again.'); window.history.back();</script>";
    }
    
    $stmt->close();
    $conn->close();
    
    
    // 临时成功消息（没有数据库时）
    unset($_SESSION['verified_email']);
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_time']);
    
    echo "<script>
            alert('Password reset successfully!\\nYou can now login with your new password.');
            window.location.href='../login.html';
          </script>";
    
} else {
    header("Location: ../forgot-password.html");
    exit();
}
?>
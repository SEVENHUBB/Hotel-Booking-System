`<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // 验证输入
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.history.back();</script>";
        exit();
    }

    // 验证密码长度
    if (strlen($newPassword) < 8) {
        echo "<script>alert('Password must be at least 8 characters long'); window.history.back();</script>";
        exit();
    }

    // 验证密码强度
    if (!preg_match('/[A-Z]/', $newPassword) || 
        !preg_match('/[a-z]/', $newPassword) || 
        !preg_match('/[0-9]/', $newPassword)) {
        echo "<script>alert('Password must contain at least one uppercase letter, one lowercase letter, and one number'); window.history.back();</script>";
        exit();
    }

    // 验证密码匹配
    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit();
    }

    $conn = getDBConnection();

    // 验证 OTP 是否存在（确保用户完成了 OTP 验证）
    $otpStmt = $conn->prepare("SELECT email FROM password_reset_otp WHERE email = ?");
    if ($otpStmt === false) {
        die("Database error: " . $conn->error);
    }
    
    $otpStmt->bind_param("s", $email);
    $otpStmt->execute();
    $otpResult = $otpStmt->get_result();
    
    if ($otpResult->num_rows === 0) {
        echo "<script>alert('Invalid session. Please restart the password reset process.'); window.location.href='../forgot-password.html';</script>";
        exit();
    }
    $otpStmt->close();

    // 检查用户是否存在
    $userStmt = $conn->prepare("SELECT TenantID FROM tenant WHERE Email = ?");
    if ($userStmt === false) {
        die("Database error: " . $conn->error);
    }
    
    $userStmt->bind_param("s", $email);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        echo "<script>alert('User not found'); window.location.href='../forgot-password.html';</script>";
        exit();
    }
    $userStmt->close();

    // 加密密码
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // 更新密码
    $updateStmt = $conn->prepare("UPDATE tenant SET Password = ? WHERE Email = ?");
    if ($updateStmt === false) {
        die("Database error: " . $conn->error);
    }
    
    $updateStmt->bind_param("ss", $hashedPassword, $email);
    
    if ($updateStmt->execute()) {
        // 删除已使用的 OTP
        $deleteStmt = $conn->prepare("DELETE FROM password_reset_otp WHERE email = ?");
        $deleteStmt->bind_param("s", $email);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        echo "<script>alert('Password reset successfully! You can now login with your new password.'); window.location.href='../login.html';</script>";
    } else {
        echo "<script>alert('Failed to reset password. Please try again.'); window.history.back();</script>";
    }

    $updateStmt->close();
    $conn->close();
    exit;

} else {
    die("Invalid request method");
}
?>
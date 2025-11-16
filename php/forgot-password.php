<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php/db_config.php';
require_once '../includes/email_service.php';  // 引入 email service

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.history.back();</script>";
        exit();
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT TenantID, Email FROM tenant WHERE Email = ?");
    
    if ($stmt === false) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Email not found!'); window.location.href='../forgot-password.html';</script>";
        exit();
    }

    $otp = sprintf("%06d", mt_rand(0, 999999));

    $deleteStmt = $conn->prepare("DELETE FROM password_reset_otp WHERE email = ?");
    if ($deleteStmt === false) {
        die("Database error: " . $conn->error);
    }
    $deleteStmt->bind_param("s", $email);
    $deleteStmt->execute();
    $deleteStmt->close();

    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $insertStmt = $conn->prepare("INSERT INTO password_reset_otp (email, otp, expires_at) VALUES (?, ?, ?)");
    
    if ($insertStmt === false) {
        die("Database error: " . $conn->error);
    }
    
    $insertStmt->bind_param("sss", $email, $otp, $expiresAt);
    
    if (!$insertStmt->execute()) {
        die("Failed to save OTP: " . $insertStmt->error);
    }

    // 发送邮件到真实的 Gmail
    $emailService = new EmailService();
    if ($emailService->sendOTP($email, $otp)) {
        echo "<script>alert('OTP has been sent to your email! Please check your inbox.'); window.location.href='/Hotel_Booking_System/verify-otp.html?email=" . urlencode($email) . "';</script>";
    } else {
        echo "<script>alert('Failed to send email. Please try again later.'); window.location.href='../forgot-password.html';</script>";
    }

    $insertStmt->close();
    $stmt->close();
    $conn->close();
    exit;

} else {
    die("Invalid request method");
}
?>
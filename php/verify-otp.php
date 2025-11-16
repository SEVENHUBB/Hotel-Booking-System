<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    
    // 验证OTP格式
    if (!preg_match('/^\d{6}$/', $otp)) {
        echo "<script>alert('Invalid OTP format'); window.history.back();</script>";
        exit();
    }
    
    // 检查session中的OTP
    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
        echo "<script>alert('OTP session expired. Please request a new OTP.'); window.location.href='../forgot-password.html';</script>";
        exit();
    }
    
    // 检查OTP是否过期（10分钟）
    if (time() - $_SESSION['otp_time'] > 600) {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);
        unset($_SESSION['otp_time']);
        echo "<script>alert('OTP has expired. Please request a new OTP.'); window.location.href='../forgot-password.html';</script>";
        exit();
    }
    
    // 验证OTP和邮箱
    if ($otp === $_SESSION['otp'] && $email === $_SESSION['otp_email']) {
        
        // OTP验证成功
        $_SESSION['verified_email'] = $email;
        unset($_SESSION['otp']); // 清除已使用的OTP
        
        echo "<script>
                alert('OTP verified successfully!');
                window.location.href='../reset-password.html?email=" . urlencode($email) . "';
              </script>";
        
    } else {
        echo "<script>alert('Invalid OTP. Please try again.'); window.history.back();</script>";
    }
    
} else {
    header("Location: ../forgot-password.html");
    exit();
}
?>
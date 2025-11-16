<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    
    // 验证邮箱格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.location.href='../forgot-password.html';</script>";
        exit();
    }
    
    // 引入数据库配置
    require_once 'db_config.php';
    $conn = getDBConnection();
    
    // 检查数据库中是否存在该邮箱 - 使用 tenant 表
    $check_sql = "SELECT TenantID, TenantName FROM tenant WHERE Email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "<script>alert('Email not found in our system!'); window.location.href='../forgot-password.html';</script>";
        $stmt->close();
        $conn->close();
        exit();
    }
    
    $stmt->close();
    $conn->close();
    
    // 生成6位数OTP
    $otp = sprintf("%06d", mt_rand(0, 999999));
    
    // 保存OTP到session
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_time'] = time();
    
    // 开发环境：直接显示OTP（不发送邮件）
    echo "<script>
            alert('OTP Generated Successfully!\\n\\nYour OTP is: $otp\\n\\nEmail: $email\\n\\n(In production, this will be sent via email)');
            window.location.href='../verify-otp.html?email=" . urlencode($email) . "';
          </script>";
    
} else {
    header("Location: ../forgot-password.html");
    exit();
}
?>
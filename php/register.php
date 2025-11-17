<?php
session_start();

// 引入数据库配置
require_once 'db_config.php';

// 检查是否有POST数据
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 获取表单数据并清理
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $country = trim($_POST['country']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);

    
    // 服务器端验证
    $errors = [];
    
    // 验证全名
    if (strlen($fullname) < 2) {
        $errors[] = "Full name must be at least 2 characters";
    }
    
    // 验证邮箱
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // 验证用户名
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    // 验证密码长度
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    // 验证密码强度 - 必须包含大写、小写、数字
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter (A-Z)";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter (a-z)";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number (0-9)";
    }
    
    // 验证密码匹配
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // ✅ Country 必填
    if (empty($country)) {
        $errors[] = "Country is required";
    }
    
    // 验证电话号码
    if (!preg_match('/^[0-9]{7,15}$/', $phone)) {
        $errors[] = "Invalid phone number format (7-15 digits required)";
    }

    // ✅ Gender 必填
    if (empty($gender)) {
        $errors[] = "Gender is required";
    }
    
    // 如果有错误，返回错误信息
    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.location.href='../register.html';</script>";
        exit();
    }
    
    // 连接数据库
    $conn = getDBConnection();
    
    // 检查用户名是否已存在 (使用 TenantName)
    $check_username = "SELECT TenantID FROM tenant WHERE TenantName = ?";
    $stmt = $conn->prepare($check_username);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Username already exists!'); window.location.href='../register.html';</script>";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // 检查邮箱是否已存在
    $check_email = "SELECT TenantID FROM tenant WHERE Email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='../register.html';</script>";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // 加密密码
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 插入新用户到 tenant 表
    $insert_sql = "INSERT INTO tenant (TenantName, Email, Password, FullName, Country, PhoneNo, Gender)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssssss", $username, $email, $hashed_password, $fullname, $country, $phone, $gender);

    
    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful!\\nUsername: $username\\nEmail: $email\\n\\nPlease login now.');
                window.location.href='../login.html?success=registered';
              </script>";
    } else {
        echo "<script>
                alert('Registration failed: " . $conn->error . "\\nPlease try again.');
                window.location.href='../register.html';
              </script>";
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    header("Location: ../register.html");
    exit();
}
?>
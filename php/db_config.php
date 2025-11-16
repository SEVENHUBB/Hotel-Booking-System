<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_USER', 'root');                      // XAMPP 默认用户名
define('DB_PASS', '');                          // XAMPP 默认密码为空
define('DB_NAME', 'hotel_booking_system');      // 你的数据库名

// 创建数据库连接函数
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // 检查连接
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // 设置字符集
    $conn->set_charset("utf8mb4");
    
    return $conn;
}
?>
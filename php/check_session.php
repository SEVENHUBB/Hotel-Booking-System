<?php
session_start();

// 检查用户是否已登录
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // 返回用户信息的 JSON
    echo json_encode([
        'logged_in' => true,
        'tenant_name' => $_SESSION['tenant_name'],
        'email' => $_SESSION['email']
    ]);
} else {
    // 未登录
    echo json_encode([
        'logged_in' => false
    ]);
}
?>
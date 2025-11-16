<?php
session_start();

// 清除所有session变量
$_SESSION = array();

// 销毁session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

session_destroy();

// 清除 Remember Me cookie
if (isset($_COOKIE['remembered_tenant'])) {
    setcookie('remembered_tenant', '', time()-3600, '/');
}

if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time()-3600, '/');
}

// 跳转到主页
header("Location: ../index.html");
exit();
?>
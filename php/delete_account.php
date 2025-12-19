<?php
session_start();
include "db_hotel.php";

// 确保用户已登录
if (!isset($_SESSION['tenant_id'])) {
    header("Location: index.php");
    exit;
}

$tenant_id = $_SESSION['tenant_id'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (empty($password)) {
        $error = "Please enter your password.";
    } else {
        // 获取用户密码
        $stmt = $conn->prepare("SELECT Password FROM tenant WHERE TenantID = ?");
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // 判断密码是否为加密（bcrypt开头为$2y$），否则认为是明文
            if (strpos($user['Password'], '$2y$') === 0) {
                $password_correct = password_verify($password, $user['Password']);
            } else {
                $password_correct = ($password === $user['Password']);
            }

            if ($password_correct) {
                // ✅ 删除外键依赖记录

                // 1. 删除 payment 表（先删除依赖的 payment）
                $stmtDelPayment = $conn->prepare(
                    "DELETE p FROM payment p
                     JOIN booking b ON p.BookingID = b.BookingID
                     WHERE b.TenantID = ?"
                );
                $stmtDelPayment->bind_param("i", $tenant_id);
                $stmtDelPayment->execute();
                $stmtDelPayment->close();

                // 2. 删除 booking 表
                $stmtDelBooking = $conn->prepare("DELETE FROM booking WHERE TenantID = ?");
                $stmtDelBooking->bind_param("i", $tenant_id);
                $stmtDelBooking->execute();
                $stmtDelBooking->close();

                // 3. 删除 tenant 表
                $stmtDelUser = $conn->prepare("DELETE FROM tenant WHERE TenantID = ?");
                $stmtDelUser->bind_param("i", $tenant_id);

                if ($stmtDelUser->execute()) {
                    // 关闭连接
                    $stmtDelUser->close();
                    $stmt->close();
                    $conn->close();

                    // 注销 session 并跳转
                    session_unset();
                    session_destroy();
                    header("Location: index.php?msg=account_deleted");
                    exit;
                } else {
                    $error = "Failed to delete account. Please try again.";
                }
                $stmtDelUser->close();

            } else {
                $error = "Incorrect password. Account not deleted.";
            }

        } else {
            $error = "User not found.";
        }
        $stmt->close();
    }
}

// 关闭连接
$conn->close();

// 引入 HTML 文件
include '../delete_account.html';
?>
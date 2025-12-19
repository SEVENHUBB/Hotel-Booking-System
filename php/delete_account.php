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

                // 2. 删除 booking 表
                $stmtDelBooking = $conn->prepare("DELETE FROM booking WHERE TenantID = ?");
                $stmtDelBooking->bind_param("i", $tenant_id);
                $stmtDelBooking->execute();

                // 3. 删除 tenant 表
                $stmtDelUser = $conn->prepare("DELETE FROM tenant WHERE TenantID = ?");
                $stmtDelUser->bind_param("i", $tenant_id);

                if ($stmtDelUser->execute()) {
                    // 注销 session 并跳转
                    session_unset();
                    session_destroy();
                    header("Location: index.php?msg=account_deleted");
                    exit;
                } else {
                    $error = "Failed to delete account. Please try again.";
                }

            } else {
                $error = "Incorrect password. Account not deleted.";
            }

        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Account</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/delete_account.css">
</head>
<body>
    <h2>Delete Account</h2>
    <p>Enter your password to confirm account deletion.</p>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit">Delete My Account</button>
        <a href="/Hotel_Booking_System/php/index.php">Cancel</a>
    </form>
</body>
</html>

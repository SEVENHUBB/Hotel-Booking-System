<?php
session_start();

// 引入数据库配置
require_once 'db_config.php';

// 检查是否有POST数据
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据并清理
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // 验证输入是否为空
    if (empty($username) || empty($password)) {
        header("Location: ../login.html?error=empty");
        exit();
    }

    // 验证用户名长度
    if (strlen($username) < 3) {
        echo "<script>
            alert('Username/Email must be at least 3 characters');
            window.location.href='../login.html';
        </script>";
        exit();
    }

    // 验证密码长度
    if (strlen($password) < 6) {
        echo "<script>
            alert('Password must be at least 6 characters');
            window.location.href='../login.html';
        </script>";
        exit();
    }

    // 初始化登录尝试追踪
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    // 清理超过30秒的旧记录
    $current_time = time();
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($attempt_time) use ($current_time) {
        return ($current_time - $attempt_time) < 30;
    });

    // 检查是否被锁定
    if (count($_SESSION['login_attempts']) >= 5) {
        $oldest_attempt = min($_SESSION['login_attempts']);
        $time_remaining = 30 - ($current_time - $oldest_attempt);
        
        echo "<script>
            alert('Too many failed login attempts!\\nPlease wait " . ceil($time_remaining) . " seconds before trying again.');
            window.location.href='../login.html?error=locked&time=" . ceil($time_remaining) . "';
        </script>";
        exit();
    }

    // 连接数据库
    $conn = getDBConnection();

    // 查询用户（通过用户名或邮箱）- 使用 tenant 表
    $sql = "SELECT * FROM tenant WHERE TenantName = ? OR Email = ?";
    $stmt = $conn->prepare($sql);

    // 检查 SQL 是否准备成功
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // 检查用户是否存在
    if ($result->num_rows > 0) {
        $tenant = $result->fetch_assoc();

        // 验证密码
        if (password_verify($password, $tenant['Password'])) {
            // 登录成功 - 清除失败记录
            unset($_SESSION['login_attempts']);

            $_SESSION['tenant_id'] = $tenant['TenantID'];
            $_SESSION['tenant_name'] = $tenant['TenantName'];
            $_SESSION['email'] = $tenant['Email'];
            $_SESSION['phone_no'] = $tenant['PhoneNo'];
            $_SESSION['gender'] = $tenant['Gender'];
            $_SESSION['country'] = $tenant['Country'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();

            // 如果勾选了 Remember Me，设置cookie（30天）
            if ($remember) {
                setcookie('remembered_tenant', $tenant['TenantName'], time() + (86400 * 30), "/");
            }

            $stmt->close();
            $conn->close();

            // 跳转到主页
            echo "<script>
                alert('Login successful!\\nWelcome back, " . $tenant['TenantName'] . "!');
                window.location.href='index.php';
            </script>";
            exit();
        } else {
            // 密码错误 - 记录失败尝试
            $_SESSION['login_attempts'][] = time();
            
            $remaining_attempts = 5 - count($_SESSION['login_attempts']);
            
            $stmt->close();
            $conn->close();

            if ($remaining_attempts > 0) {
                echo "<script>
                    alert('Invalid password!\\nRemaining attempts: " . $remaining_attempts . "');
                    window.location.href='../login.html?error=invalid&remaining=" . $remaining_attempts . "';
                </script>";
            } else {
                echo "<script>
                    alert('Too many failed login attempts!\\nYour account is locked for 30 seconds.');
                    window.location.href='../login.html?error=locked&time=30';
                </script>";
            }
            exit();
        }
    } else {
        // 用户不存在 - 也记录失败尝试
        $_SESSION['login_attempts'][] = time();
        
        $remaining_attempts = 5 - count($_SESSION['login_attempts']);
        
        $stmt->close();
        $conn->close();

        if ($remaining_attempts > 0) {
            echo "<script>
                alert('User not found!\\nRemaining attempts: " . $remaining_attempts . "');
                window.location.href='../login.html?error=notfound&remaining=" . $remaining_attempts . "';
            </script>";
        } else {
            echo "<script>
                alert('Too many failed login attempts!\\nYour account is locked for 30 seconds.');
                window.location.href='../login.html?error=locked&time=30';
            </script>";
        }
        exit();
    }
} else {
    // 如果不是POST请求，跳回登录页
    header("Location: ../login.html");
    exit();
}
?>
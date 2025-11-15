<?php
require_once 'config/database.php';

// Test query
try {
    $stmt = $conn->query("SELECT * FROM Hotel");
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Database Connection Successful!</h2>";
    echo "<h3>Hotels in Database:</h3>";
    echo "<pre>";
    print_r($hotels);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

访问：`http://localhost/Hotel%20Booking%20System/test_connection.php`

---

### **2. 建议的开发顺序**

#### **Phase 1: 基础页面（第一周）**
```
✅ index.php - 主页
✅ pages/rooms.php - 显示所有房间
✅ pages/room_details.php - 房间详情
✅ includes/header.php - 共用页首
✅ includes/footer.php - 共用页尾
```

#### **Phase 2: 用户功能（第二周）**
```
✅ pages/register.php - 用户注册
✅ pages/login.php - 用户登录
✅ pages/booking.php - 预订房间
✅ pages/my_bookings.php - 查看我的预订
```

#### **Phase 3: 管理后台（第三周）**
```
✅ pages/admin/login.php - 管理员登录
✅ pages/admin/dashboard.php - 管理面板
✅ pages/admin/manage_rooms.php - 管理房间
✅ pages/admin/manage_bookings.php - 管理预订
```

#### **Phase 4: 付款功能（第四周）**
```
✅ pages/payment.php - 付款页面
✅ pages/payment_confirmation.php - 付款确认
✅ pages/invoice.php - 发票/收据
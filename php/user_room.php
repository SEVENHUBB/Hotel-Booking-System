<?php
include "db_hotel.php";

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if ($hotel_id <= 0) {
    die("Invalid hotel ID");
}

// room type filter
$room_type = isset($_GET['room_type']) ? trim($_GET['room_type']) : '';

// 查询酒店信息
$stmt = $conn->prepare("SELECT * FROM hotel WHERE HotelID = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

// 查询房间信息
$sql = "
    SELECT RoomType, RoomPrice, RoomDesc, RoomImage, Capacity, RoomQuantity
    FROM room
    WHERE HotelID = ?
      AND RoomStatus = 'Available'
";

if (!empty($room_type)) {
    $sql .= " AND RoomType = ?";
}

$stmt2 = $conn->prepare($sql);

if (!empty($room_type)) {
    $stmt2->bind_param("is", $hotel_id, $room_type);
} else {
    $stmt2->bind_param("i", $hotel_id);
}

$stmt2->execute();
$rooms = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['HotelName']); ?> - Available Rooms</title>
    
    <!-- 引入你的CSS文件 -->
    <link rel="stylesheet" href="/Hotel_Booking_System/css/user_room_style.css">
    
    <!-- 可选：Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="main-container">
        
        <!-- 页面头部 -->
        <div class="page-header">
            <h1>🏨 <?php echo htmlspecialchars($hotel['HotelName']); ?></h1>
            <p class="hotel-description">
                <?php echo htmlspecialchars($hotel['Description']); ?>
            </p>
        </div>

        <!-- 筛选区域 -->
        <div class="filter-section">
            <form method="GET">
                <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">

                <label for="room_type">🔍 Filter by Room Type:</label>
                <select name="room_type" id="room_type">
                    <option value="">All Room Types</option>

                    <option value="Single Room" <?php if ($room_type == "Single Room") echo "selected"; ?>>
                        Single Room
                    </option>

                    <option value="Double Room" <?php if ($room_type == "Double Room") echo "selected"; ?>>
                        Double Room
                    </option>
                    
                    <option value="King Size" <?php if ($room_type == "King Size") echo "selected"; ?>>
                        King Size
                    </option>
                    
                    <option value="Queen Size" <?php if ($room_type == "Queen Size") echo "selected"; ?>>
                        Queen Size
                    </option>
                </select>

                <button type="submit">Apply Filter</button>
            </form>
        </div>

        <!-- 房间网格 -->
        <div class="room-grid">
            <?php if ($rooms && $rooms->num_rows > 0): ?>
                <?php while ($r = $rooms->fetch_assoc()): ?>
                    <div class="room-card">
                        <!-- 房间图片 -->
                        <img
                            src="<?php echo !empty($r['RoomImage'])
                                        ? '/Hotel_Booking_System/' . htmlspecialchars($r['RoomImage'])
                                        : '/Hotel_Booking_System/images/no-image.png'; ?>"
                            alt="<?php echo htmlspecialchars($r['RoomType']); ?>"
                            class="room-img">

                        <!-- 房间信息 -->
                        <h3><?php echo htmlspecialchars($r['RoomType']); ?></h3>
                        
                        <p><strong>💰 RM <?php echo number_format($r['RoomPrice'], 2); ?></strong> / night</p>
                        
                        <div class="capacity-badge">
                            👥 <?php echo htmlspecialchars($r['Capacity']); ?> persons
                        </div>
                        
                        <p style="margin-top: 10px;">
                            <?php echo htmlspecialchars($r['RoomDesc']); ?>
                        </p>
                        
                        <div class="available-rooms">
                            🛏️ <?php echo htmlspecialchars($r['RoomQuantity']); ?> rooms available
                        </div>

                        <!-- 预订表单 -->
                        <form action="add_to_cart.php" method="post">
                            <input type="hidden" name="HotelID" value="<?php echo $hotel_id; ?>">
                            <input type="hidden" name="RoomType" value="<?php echo htmlspecialchars($r['RoomType']); ?>">
                            <input type="hidden" name="RoomPrice" value="<?php echo $r['RoomPrice']; ?>">
                            <input type="hidden" name="RoomQuantity" value="<?php echo $r['RoomQuantity']; ?>">

                            <label for="checkin_<?php echo htmlspecialchars($r['RoomType']); ?>">
                                📅 Check-in Date:
                            </label>
                            <input
                                type="date"
                                id="checkin_<?php echo htmlspecialchars($r['RoomType']); ?>"
                                name="checkin_date"
                                required
                                min="<?php echo date('Y-m-d'); ?>">

                            <label for="checkout_<?php echo htmlspecialchars($r['RoomType']); ?>">
                                📅 Check-out Date:
                            </label>
                            <input
                                type="date"
                                id="checkout_<?php echo htmlspecialchars($r['RoomType']); ?>"
                                name="checkout_date"
                                required
                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

                            <label for="qty_<?php echo htmlspecialchars($r['RoomType']); ?>">
                                🔢 Number of Rooms:
                            </label>
                            <input
                                type="number"
                                id="qty_<?php echo htmlspecialchars($r['RoomType']); ?>"
                                name="qty"
                                min="1"
                                max="<?php echo $r['RoomQuantity']; ?>"
                                value="1"
                                required>

                            <button type="submit">🛒 Add to Cart</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-rooms-message">
                    <p>😔 No rooms available for this hotel at the moment.</p>
                    <p>Please try again later or check other hotels.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- 可选：添加一些JavaScript增强体验 -->
    <script>
        // 验证退房日期必须在入住日期之后
        document.querySelectorAll('form').forEach(form => {
            const checkin = form.querySelector('input[name="checkin_date"]');
            const checkout = form.querySelector('input[name="checkout_date"]');
            
            if (checkin && checkout) {
                checkin.addEventListener('change', function() {
                    const nextDay = new Date(this.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkout.min = nextDay.toISOString().split('T')[0];
                    
                    // 如果当前checkout日期小于新的最小值，重置它
                    if (checkout.value && checkout.value <= this.value) {
                        checkout.value = checkout.min;
                    }
                });
                
                checkout.addEventListener('change', function() {
                    if (checkin.value && this.value <= checkin.value) {
                        alert('Check-out date must be after check-in date!');
                        this.value = '';
                    }
                });
            }
        });

        // 平滑滚动效果
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // 添加加载动画
        window.addEventListener('load', function() {
            document.querySelectorAll('.room-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>

</body>

</html>
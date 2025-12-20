<?php
include "db_hotel.php";

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if ($hotel_id <= 0) {
    die("Invalid hotel ID");
}

<<<<<<< HEAD
// check hotel information
=======
// room type filter
$room_type = isset($_GET['room_type']) ? trim($_GET['room_type']) : '';

// Pagination setup
$rooms_per_page = 3;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $rooms_per_page;

// 查询酒店信息
>>>>>>> 581c7709e54bab271779afe0000e462299705bf2
$stmt = $conn->prepare("SELECT * FROM hotel WHERE HotelID = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

// Count total rooms for pagination
$count_sql = "
    SELECT COUNT(*) as total
    FROM room
    WHERE HotelID = ?
      AND RoomStatus = 'Available'
";

if (!empty($room_type)) {
    $count_sql .= " AND RoomType = ?";
}

<<<<<<< HEAD
// Query room information
$stmt2 = $conn->prepare("
=======
$count_stmt = $conn->prepare($count_sql);

if (!empty($room_type)) {
    $count_stmt->bind_param("is", $hotel_id, $room_type);
} else {
    $count_stmt->bind_param("i", $hotel_id);
}

$count_stmt->execute();
$total_rooms = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rooms / $rooms_per_page);

// 查询房间信息 with LIMIT
$sql = "
>>>>>>> 581c7709e54bab271779afe0000e462299705bf2
    SELECT RoomType, RoomPrice, RoomDesc, RoomImage, Capacity, RoomQuantity
    FROM room
    WHERE HotelID = ?
      AND RoomStatus = 'Available'
";

if (!empty($room_type)) {
    $sql .= " AND RoomType = ?";
}

$sql .= " LIMIT ? OFFSET ?";

$stmt2 = $conn->prepare($sql);

if (!empty($room_type)) {
    $stmt2->bind_param("isii", $hotel_id, $room_type, $rooms_per_page, $offset);
} else {
    $stmt2->bind_param("iii", $hotel_id, $rooms_per_page, $offset);
}

$stmt2->execute();
$rooms = $stmt2->get_result();
?>

<<<<<<< HEAD
// Prepare the data to be passed to HTML
$hotel_name = htmlspecialchars($hotel['HotelName']);
$hotel_description = htmlspecialchars($hotel['Description']);
$min_checkin_date = date('Y-m-d');
$min_checkout_date = date('Y-m-d', strtotime('+1 day'));

$stmt->close();
$stmt2->close();

include '../room.html';

$conn->close();
?>
=======
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
            <!-- 返回首页按钮 -->
            <a href="../php/index.php" class="back-home-btn">
                ← Back to Home
            </a>
            
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

        <!-- Page Info -->
        <?php if ($total_rooms > 0): ?>
            <div class="page-info">
                Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $rooms_per_page, $total_rooms); ?> 
                of <?php echo $total_rooms; ?> room(s)
            </div>
        <?php endif; ?>

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

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <!-- Previous Button -->
                <?php if ($current_page > 1): ?>
                    <a href="?hotel_id=<?php echo $hotel_id; ?>&room_type=<?php echo urlencode($room_type); ?>&page=<?php echo $current_page - 1; ?>">
                        « Previous
                    </a>
                <?php else: ?>
                    <span class="disabled">« Previous</span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $current_page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?hotel_id=<?php echo $hotel_id; ?>&room_type=<?php echo urlencode($room_type); ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Next Button -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?hotel_id=<?php echo $hotel_id; ?>&room_type=<?php echo urlencode($room_type); ?>&page=<?php echo $current_page + 1; ?>">
                        Next »
                    </a>
                <?php else: ?>
                    <span class="disabled">Next »</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <script src="../js/register.js"></script>
</body>

</html>
>>>>>>> 581c7709e54bab271779afe0000e462299705bf2

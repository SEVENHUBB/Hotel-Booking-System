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
    <title>Rooms for <?php echo htmlspecialchars($hotel['HotelName']); ?></title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
</head>

<body>

    <h1><?php echo htmlspecialchars($hotel['HotelName']); ?> - Available Rooms</h1>

    <form method="GET" style="margin-bottom:20px;">
        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">

        <label>Filter by Room Type:</label>
        <select name="room_type">
            <option value="">All</option>

            <option value="Single Room" <?php if ($room_type == "Single Room") echo "selected"; ?>>
                Single Room
            </option>

            <option value="Double Room" <?php if ($room_type == "Double Room") echo "selected"; ?>>
                Double Room
            </option>
            
            <option value="King Size" <?php if ($room_type == "King Siza") echo "selected"; ?>>
                King Siza
            </option>
            
            <option value="Queen Size" <?php if ($room_type == "Queen Size") echo "selected"; ?>>
                Queen Size
            </option>
        </select>


        <button type="submit">Filter</button>
    </form>

    <p><?php echo htmlspecialchars($hotel['Description']); ?></p>

    <div class="room-grid">
        <?php if ($rooms && $rooms->num_rows > 0): ?>
            <?php while ($r = $rooms->fetch_assoc()): ?>
                <div class="room-card">
                    <img
                        src="<?php echo !empty($r['RoomImage'])
                                    ? '/Hotel_Booking_System/' . htmlspecialchars($r['RoomImage'])
                                    : '/Hotel_Booking_System/images/no-image.png'; ?>"
                        alt="Room Image"
                        class="room-img">

                    <h3><?php echo htmlspecialchars($r['RoomType']); ?></h3>
                    <p>Price: RM <?php echo htmlspecialchars($r['RoomPrice']); ?></p>
                    <p>Capacity: <?php echo htmlspecialchars($r['Capacity']); ?> persons</p>
                    <p><?php echo htmlspecialchars($r['RoomDesc']); ?></p>
                    <p>Available Rooms: <?php echo htmlspecialchars($r['RoomQuantity']); ?></p>

                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="HotelID" value="<?php echo $hotel_id; ?>">
                        <input type="hidden" name="RoomType" value="<?php echo htmlspecialchars($r['RoomType']); ?>">
                        <input type="hidden" name="RoomPrice" value="<?php echo $r['RoomPrice']; ?>">
                        <input type="hidden" name="RoomQuantity" value="<?php echo $r['RoomQuantity']; ?>">

                        <label>Check-in Date:</label>
                        <input
                            type="date"
                            name="checkin_date"
                            required
                            min="<?php echo date('Y-m-d'); ?>">

                        <label>Check-out Date:</label>
                        <input
                            type="date"
                            name="checkout_date"
                            required
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

                        <label>Quantity:</label>
                        <input
                            type="number"
                            name="qty"
                            min="1"
                            max="<?php echo $r['RoomQuantity']; ?>"
                            value="1"
                            required>

                        <button type="submit">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No rooms available for this hotel.</p>
        <?php endif; ?>
    </div>

</body>

</html>
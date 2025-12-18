<?php
include "db_hotel.php";

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if ($hotel_id <= 0) {
    die("Invalid hotel ID");
}

// 查询酒店信息
$stmt = $conn->prepare("SELECT * FROM hotel WHERE HotelID = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

// 查询房间信息
// 查询房间信息
$stmt2 = $conn->prepare("
    SELECT RoomType, RoomPrice, RoomDesc, RoomImage, Capacity, RoomQuantity
    FROM room
    WHERE HotelID = ? AND RoomStatus = 'Available'
");
$stmt2->bind_param("i", $hotel_id);
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
    <p><?php echo htmlspecialchars($hotel['Description']); ?></p>

    <div class="room-grid">
<?php if($rooms && $rooms->num_rows > 0): ?>
    <?php while($r = $rooms->fetch_assoc()): ?>
        <div class="room-card">
            <img src="<?php echo !empty($r['RoomImage']) ? '/Hotel_Booking_System/' . htmlspecialchars($r['RoomImage']) : '/Hotel_Booking_System/images/no-image.png'; ?>" alt="Room Image" class="room-img">
            <h3><?php echo htmlspecialchars($r['RoomType']); ?></h3>
            <p>Price: RM <?php echo htmlspecialchars($r['RoomPrice']); ?></p>
            <p>Capacity: <?php echo htmlspecialchars($r['Capacity']); ?> persons</p>
            <p><?php echo htmlspecialchars($r['RoomDesc']); ?></p>
            <p>Available Rooms: <?php echo htmlspecialchars($r['RoomQuantity']); ?></p>
            <a href="booking.php?hotel_id=<?php echo $hotel_id; ?>&room_type=<?php echo urlencode($r['RoomType']); ?>">Book Now</a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No rooms available for this hotel.</p>
<?php endif; ?>


    </div>
</body>

</html>
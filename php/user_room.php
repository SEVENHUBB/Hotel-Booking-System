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
$stmt2 = $conn->prepare("SELECT * FROM room WHERE HotelID = ? AND RoomStatus='Available'");
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
        <?php if ($rooms && $rooms->num_rows > 0): ?>
            <?php while ($r = $rooms->fetch_assoc()): ?>
                <div class="room">
                    <img src="../<?php echo !empty($r['RoomImage']) ? $r['RoomImage'] : 'default.png'; ?>" 
                        alt="<?php echo htmlspecialchars($r['RoomType']); ?>">
                    <div class="room-card">
                        <h3><?php echo $r['RoomType']; ?></h3>
                        <p>Price: RM <?php echo $r['RoomPrice']; ?></p>
                        <p>Capacity: <?php echo $r['Capacity']; ?> persons</p>
                        <p><?php echo $r['RoomDesc']; ?></p>
                        <a href="booking.php?room_id=<?php echo $r['RoomID']; ?>">Book Now</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No rooms available for this hotel.</p>
        <?php endif; ?>
    </div>
</body>

</html>
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
            <form action="add_to_cart.php" method="post">
                <input type="hidden" name="HotelID" value="<?php echo $hotel_id; ?>">
                <input type="hidden" name="RoomType" value="<?php echo htmlspecialchars($r['RoomType']); ?>">
                <input type="hidden" name="RoomPrice" value="<?php echo $r['RoomPrice']; ?>">
                <input type="hidden" name="RoomQuantity" value="<?php echo $r['RoomQuantity']; ?>">

                <label>Check-in Date:</label>
                <input type="date" name="checkin_date" required min="<?php echo date('Y-m-d'); ?>">

                <label>Check-out Date:</label>
                <input type="date" name="checkout_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

                <label>Quantity:</label>
                <input 
                    type="number" 
                    name="qty" 
                    min="1" 
                    max="<?php echo $r['RoomQuantity']; ?>" 
                    value="1"
                    required
                >

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
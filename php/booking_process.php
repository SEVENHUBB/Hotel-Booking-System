<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['TenantID'])) {
    header("Location: login.php"); // Change to your actual login page
    exit();
}

include "db_hotel.php"; // Your existing DB connection (using mysqli)

$tenantID = $_SESSION['TenantID'];
$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$room_type = isset($_GET['room_type']) ? urldecode($_GET['room_type']) : '';

if ($hotel_id <= 0 || empty($room_type)) {
    die("Invalid hotel or room selection.");
}

// Fetch room details to validate and get RoomID & price
$stmt = $conn->prepare("
    SELECT RoomID, RoomPrice, Capacity, RoomQuantity 
    FROM room 
    WHERE HotelID = ? AND RoomType = ? AND RoomStatus = 'Available'
    LIMIT 1
");
$stmt->bind_param("is", $hotel_id, $room_type);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

if (!$room || $room['RoomQuantity'] <= 0) {
    die("This room type is no longer available.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $num_guests = (int)($_POST['num_guests'] ?? 0);

    // Basic validation
    if (empty($check_in) || empty($check_out) || $num_guests <= 0) {
        $error = "Please fill in all fields correctly.";
    } elseif (strtotime($check_in) >= strtotime($check_out)) {
        $error = "Check-out date must be after check-in date.";
    } elseif ($num_guests > $room['Capacity']) {
        $error = "Number of guests exceeds room capacity ({$room['Capacity']}).";
    } else {
        // Insert into booking table
        $stmt_b = $conn->prepare("
            INSERT INTO booking 
            (RoomID, TenantID, CheckInDate, CheckOutDate, NumberOfTenant)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_b->bind_param("iissi", $room['RoomID'], $tenantID, $check_in, $check_out, $num_guests);

        if ($stmt_b->execute()) {
            $bookingID = $conn->insert_id;

            // Optional: Decrease available quantity (if you track inventory)
            // $conn->query("UPDATE room SET RoomQuantity = RoomQuantity - 1 WHERE RoomID = {$room['RoomID']} AND RoomQuantity > 0");

            // Redirect to payment page
            header("Location: payment.php?bookingID=$bookingID");
            exit();
        } else {
            $error = "Booking failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Room - <?php echo htmlspecialchars($room_type); ?></title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
    <style>
        .booking-form { max-width: 500px; margin: 40px auto; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        label { display: block; margin: 15px 0 5px; font-weight: bold; }
        input[type="date"], input[type="number"] { width: 100%; padding: 10px; box-sizing: border-box; }
        button { padding: 12px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="booking-form">
        <h2>Complete Your Booking</h2>
        <p><strong>Hotel ID:</strong> <?php echo $hotel_id; ?></p>
        <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room_type); ?></p>
        <p><strong>Price per Night:</strong> RM <?php echo number_format($room['RoomPrice'], 2); ?></p>
        <p><strong>Capacity:</strong> <?php echo $room['Capacity']; ?> persons</p>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Check-In Date</label>
            <input type="date" name="check_in" required min="<?php echo date('Y-m-d'); ?>">

            <label>Check-Out Date</label>
            <input type="date" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

            <label>Number of Guests</label>
            <input type="number" name="num_guests" min="1" max="<?php echo $room['Capacity']; ?>" required value="1">

            <button type="submit">Confirm & Proceed to Payment</button>
        </form>
    </div>
</body>
</html>
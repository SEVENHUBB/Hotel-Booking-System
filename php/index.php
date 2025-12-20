<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db_hotel.php";

// this is for search bar function
$keyword = "";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['Quantity'];
}

if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {

    $keyword = trim($_GET['keyword']);

    $sql = "SELECT HotelID, HotelName, Description, Address, City, Country, 
                   NumRooms, Category, StarRating, ImagePath
            FROM hotel
            WHERE HotelName LIKE ?
               OR City LIKE ?
               OR Address LIKE ?";

    $stmt = $conn->prepare($sql);
    $search = "%" . $keyword . "%";
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {

    $sql = "SELECT HotelID, HotelName, Description, Address, City, Country, 
                   NumRooms, Category, StarRating, ImagePath
            FROM hotel";

    $result = $conn->query($sql);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Super Booking System</title>
    <link rel="stylesheet" href="/Hotel_Booking_System/css/home.css">
    <link rel="stylesheet" href="/Hotel_Booking_System/css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>


<body>
    <header>
        <h1>Super Booking System</h1>
        <nav>
            <ul class="nav-links">
                <li><a href="/Hotel_Booking_System/php/index.php">Home</a></li>
                <li><a href="/Hotel_Booking_System/about.html">About Us</a></li>
                <li><a href="/Hotel_Booking_System/contact.html">Contact Us</a></li>
            </ul>
        </nav>
        <div id="navRight">
            <?php if (isset($_SESSION['tenant_id'])): ?>

                <a href="cart.php" class="cart-btn" title="View Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>

                <span class="welcome-text">
                    Hi, <?php echo htmlspecialchars($_SESSION['tenant_name']); ?>
                </span>

                <a href="/Hotel_Booking_System/php/profile.php" class="profile-btn">
                    <i class="fas fa-user"></i> Profile
                </a>

                <!-- My Booking -->
                <a href="/Hotel_Booking_System/php/my_booking.php" class="nav-btn blue-btn">
                    <i class="fas fa-calendar-check"></i> My Booking
                </a>

                <!-- Delete Account -->
                <a href="/Hotel_Booking_System/php/delete_account.php"
                    class="nav-btn delete-btn"
                    onclick="return confirm('Are you sure you want to delete your account?');">
                    <i class="fas fa-trash"></i> Delete
                </a>

                <a href="/Hotel_Booking_System/php/logout.php" class="logout-btn">Logout</a>

            <?php else: ?>

                <button class="login-btn" onclick="goToLogin()">Log In</button>
                <button class="register-btn" onclick="goToRegister()">Register</button>

            <?php endif; ?>
        </div>
    </header>

    <section class="home-header">
        <h1>Welcome to Super Booking Hotels</h1>
        <p>Find your perfect stay with the best prices</p>

        <form method="GET" action="index.php" class="search-box">

            <input type="text" name="keyword" placeholder="Enter city, address or hotel name"
                value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>" required />

<<<<<<< HEAD
=======
            <!-- 房客选择区域 -->
>>>>>>> 581c7709e54bab271779afe0000e462299705bf2
            <div class="search-item">
                <div class="rect-box" id="guestsTrigger">
                    <i class="fas fa-user-friends"></i>
                    <span id="guestDisplay">2 adults</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </div>
            </div>

            <button type="submit" class="search-btn">Search</button>

        </form>

        <div class="guest-picker-popup" id="guestPicker">
            <div class="guest-row">
                <div class="guest-label">
                    <div>Rooms</div>
                    <small>Maximum 8 rooms</small>
                </div>
                <div class="guest-counter">
                    <button class="counter-btn minus" data-type="rooms">-</button>
                    <span class="counter-value" data-type="rooms">1</span>
                    <button class="counter-btn plus" data-type="rooms">+</button>
                </div>
            </div>

            <div class="guest-row">
                <div class="guest-label">
                    <div>Adults</div>
                    <small>Age 18 or above</small>
                </div>
                <div class="guest-counter">
                    <button class="counter-btn minus" data-type="adults">-</button>
                    <span class="counter-value" data-type="adults">2</span>
                    <button class="counter-btn plus" data-type="adults">+</button>
                </div>
            </div>

            <div class="guest-row">
                <div class="guest-label">
                    <div>Children</div>
                    <small>Ages 0–17</small>
                </div>
                <div class="guest-counter">
                    <button class="counter-btn minus" data-type="children">-</button>
                    <span class="counter-value" data-type="children">0</span>
                    <button class="counter-btn plus" data-type="children">+</button>
                </div>
            </div>

            <div id="childrenAges"></div>

            <div class="guest-actions">
                <button class="done-btn">Done</button>
    </section>

    <section class="hotels-section">
        <h2>
            <?php if (!empty($keyword)): ?>
                Search results for "<?php echo htmlspecialchars($keyword); ?>"
            <?php else: ?>
                Featured Hotels
            <?php endif; ?>
        </h2>

        <div class="hotel-grid">

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <a href="user_room.php?hotel_id=<?php echo $row['HotelID']; ?>">
                        <div class="hotel-card">
                            <img src="../<?php echo !empty($row['ImagePath']) ? $row['ImagePath'] : 'default.png'; ?>"
                             alt="<?php echo htmlspecialchars($row['HotelName']); ?>">
                            <div class="info">
                                <h3><?php echo $row['HotelName'] ?></h3>
                                <p><?php echo $row['Description']; ?></p>
                                <p><strong>Address:</strong> <?php echo $row['Address']; ?></p>
                                <p><strong>Rooms Available:</strong> <?php echo $row['NumRooms']; ?></p>
                                <p><strong>Category:</strong> <?php echo $row['Category']; ?></p>
                                <p><strong>Location:</strong> <?php echo $row['City'] . ", " . $row['Country']; ?></p>
                                <p>⭐ <?php echo $row['StarRating']; ?> Star Hotel</p>
                            </div>
                        </div>
                    </a>

                <?php endwhile; ?>

            <?php else: ?>

                <p>No hotels available</p>

            <?php endif; ?>

        </div>
    </section>

    <script src="/Hotel_Booking_System/js/main.js"></script>
    <script src="/Hotel_Booking_System/js/date-picker.js"></script>
    <script src="/Hotel_Booking_System/js/guest-picker.js"></script>



</body>

</html>
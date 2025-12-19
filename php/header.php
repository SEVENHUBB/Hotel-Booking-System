<?php
// header.php - Reusable header component
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection if not already included
if (!isset($conn)) {
    include_once "db_hotel.php";
}

// Cart count calculation
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['Quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Super Booking System'; ?></title>
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
                <li><a href="/Hotel_Booking_System/about.php">About Us</a></li>
                <li><a href="/Hotel_Booking_System/contact.php">Contact Us</a></li>
            </ul>
        </nav>
        <div id="navRight">
            <?php if (isset($_SESSION['tenant_id'])): ?>

                <a href="/Hotel_Booking_System/php/cart.php" class="cart-btn" title="View Cart">
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

                <a href="/Hotel_Booking_System/php/my_booking.php" class="nav-btn blue-btn">
                    <i class="fas fa-calendar-check"></i> My Booking
                </a>

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
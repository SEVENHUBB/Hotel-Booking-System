<!-- <?php
// include 'db.php';

// $HotelName = $_POST['HotelName'];
// $Address = $_POST['Address'];
// $City = $_POST['City'];
// $Country = $_POST['Country'];
// $NumRooms = $_POST['NumRooms'];
// $StarRating = $_POST['StarRating'];
// $PhoneNo = $_POST['PhoneNo'];
// $TenantID = $_POST['TenantID'];

// $sql = "INSERT INTO hotels (HotelName, Address, City, Country, NumRooms, StarRating, PhoneNo, TenantID)
//         VALUES ('$HotelName', '$Address', '$City', '$Country', '$NumRooms', '$StarRating', '$PhoneNo', '$TenantID')";

// if ($conn->query($sql) === TRUE) {
//     echo "Hotel added successfully!";
// } else {
//     echo "Error: " . $conn->error;
// }
?> -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Booking System</title>
  <link rel="stylesheet" href="../css/home.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body>

<?php include '_main_page.php'; ?>

<header class="home-header">
  <h1>Welcome to Super Booking Hotels</h1>
  <p>Find your perfect stay with the best prices</p>
  
  <div class="search-box">
    <input type="text" placeholder="Enter destination..." />
    <input type="date" />
    <input type="date" />
    <select>
      <option>1 Guest</option>
      <option>2 Guests</option>
      <option>3 Guests</option>
      <option>4 Guests</option>
    </select>
    <button>Search</button>
  </div>
</header>

<section class="hotels-section">
  <h2>Featured Hotels</h2>
  <div class="hotel-grid">
  <a href="">
    <div class="hotel-card">
      <img src="../images/hotel_1.png" alt="Hotel" />
      <div class="info">
        <h3>Sunrise Resort</h3>
        <p>Location: Penang</p>
        <p class="price">RM 250 / night</p>
      </div>
    </div>
</a>

    <div class="hotel-card">
      <img src="hotel2.jpg" alt="Hotel" />
      <div class="info">
        <h3>Luxury Palace Hotel</h3>
        <p>Location: Kuala Lumpur</p>
        <p class="price">RM 450 / night</p>
      </div>
    </div>

    <div class="hotel-card">
      <img src="hotel3.jpg" alt="Hotel" />
      <div class="info">
        <h3>Beachside Villa</h3>
        <p>Location: Langkawi</p>
        <p class="price">RM 300 / night</p>
      </div>
    </div>

  </div>
</section>

</body>
</html>

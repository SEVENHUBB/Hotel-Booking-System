<!DOCTYPE html>
<html>
<head>
    <title>Add Hotel</title>
</head>
<body>

<h2>Add New Hotel</h2>

<form action="add_hotel_process.php" method="POST">
    <label>Hotel Name:</label>
    <input type="text" name="HotelName" required><br>

    <label>Address:</label>
    <input type="text" name="Address" required><br>

    <label>City:</label>
    <input type="text" name="City" required><br>

    <label>Country:</label>
    <input type="text" name="Country" required><br>

    <label>Number of Rooms:</label>
    <input type="number" name="NumRooms" required><br>

    <label>Star Rating:</label>
    <input type="number" name="StarRating" min="1" max="5" required><br>

    <label>Phone Number:</label>
    <input type="text" name="PhoneNo" required><br>

    <label>Tenant ID:</label>
    <input type="number" name="TenantID" required><br>

    <button type="submit">Add Hotel</button>
</form>

</body>
</html>

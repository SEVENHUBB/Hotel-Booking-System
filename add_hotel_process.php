<?php
include 'db.php';

$HotelName = $_POST['HotelName'];
$Address = $_POST['Address'];
$City = $_POST['City'];
$Country = $_POST['Country'];
$NumRooms = $_POST['NumRooms'];
$StarRating = $_POST['StarRating'];
$PhoneNo = $_POST['PhoneNo'];
$TenantID = $_POST['TenantID'];

$sql = "INSERT INTO hotels (HotelName, Address, City, Country, NumRooms, StarRating, PhoneNo, TenantID)
        VALUES ('$HotelName', '$Address', '$City', '$Country', '$NumRooms', '$StarRating', '$PhoneNo', '$TenantID')";

if ($conn->query($sql) === TRUE) {
    echo "Hotel added successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>

<?php require '/receipt_logic.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <link rel="stylesheet" href="css/receipt.css">
    <style>@media print { button { display: none; } }</style>
</head>
<body>
    <div class="container">
        <h2>Receipt #<?php echo $billID; ?></h2>

        <div class="details">
            <p><strong>Tenant:</strong> <?php echo htmlspecialchars($receipt['FullName']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($receipt['Email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($receipt['PhoneNo']); ?></p>
            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($receipt['HotelName']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($receipt['RoomType']); ?></p>
            <p><strong>Check-In:</strong> <?php echo $receipt['CheckInDate']; ?></p>
            <p><strong>Check-Out:</strong> <?php echo $receipt['CheckOutDate']; ?></p>
            <p><strong>Guests:</strong> <?php echo $receipt['NumberOfTenant']; ?></p>
            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($receipt['TransactionID']); ?></p>
            <p><strong>Payment Date:</strong> <?php echo $receipt['PaymentDate']; ?></p>
        </div>

        <table>
            <tr><th>Description</th><th>Amount (MYR)</th></tr>
            <tr><td>Room Cost (<?php echo $receipt['Nights']; ?> nights)</td><td><?php echo number_format($subtotal, 2); ?></td></tr>
            <tr><td>Tax (10%)</td><td><?php echo number_format($tax, 2); ?></td></tr>
            <tr class="total"><td><strong>Total Paid</strong></td><td><strong><?php echo number_format($totalAmount, 2); ?></strong></td></tr>
        </table>

        <button onclick="window.print()">Print Receipt</button>
        <button id="emailBtn">Email Receipt</button>
    </div>

    <script src="js/receipt.js"></script>
</body>
</html>
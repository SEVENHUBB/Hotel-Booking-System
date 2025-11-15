-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 10:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_booking_system`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `availablerooms`
-- (See below for the actual view)
--
CREATE TABLE `availablerooms` (
`RoomID` int(11)
,`RoomType` varchar(50)
,`RoomPrice` decimal(10,2)
,`RoomDesc` text
,`Capacity` int(11)
,`HotelName` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `bill`
--

CREATE TABLE `bill` (
  `BillID` int(11) NOT NULL,
  `PaymentID` int(11) NOT NULL,
  `TenantName` varchar(100) NOT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `PhoneNo` varchar(20) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Country` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` int(11) NOT NULL,
  `RoomID` int(11) NOT NULL,
  `TenantID` int(11) NOT NULL,
  `CheckInDate` date NOT NULL,
  `CheckOutDate` date NOT NULL,
  `NumberOfTenant` int(11) NOT NULL,
  `BookingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `RoomID`, `TenantID`, `CheckInDate`, `CheckOutDate`, `NumberOfTenant`, `BookingDate`, `Status`) VALUES
(1, 1, 1, '2025-11-20', '2025-11-23', 2, '2025-11-15 09:39:10', 'Confirmed'),
(2, 2, 2, '2025-11-25', '2025-11-28', 3, '2025-11-15 09:39:10', 'Pending');

-- --------------------------------------------------------

--
-- Stand-in structure for view `bookingdetails`
-- (See below for the actual view)
--
CREATE TABLE `bookingdetails` (
`BookingID` int(11)
,`TenantName` varchar(100)
,`Email` varchar(100)
,`RoomType` varchar(50)
,`HotelName` varchar(100)
,`CheckInDate` date
,`CheckOutDate` date
,`NumberOfTenant` int(11)
,`Status` varchar(20)
,`Amount` decimal(10,2)
,`PaymentStatus` varchar(20)
);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EmployeeID` int(11) NOT NULL,
  `RoleID` int(11) NOT NULL,
  `EmployeeName` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `PhoneNo` varchar(20) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EmployeeID`, `RoleID`, `EmployeeName`, `Password`, `Gender`, `PhoneNo`, `Email`, `Salary`) VALUES
(1, 1, 'Ahmad bin Ali', '$2y$10$example_hash_password1', 'Male', '+60-12-3456789', 'ahmad@grandhotel.com', 5000.00),
(2, 2, 'Siti binti Hassan', '$2y$10$example_hash_password2', 'Female', '+60-13-9876543', 'siti@grandhotel.com', 3000.00),
(3, 3, 'Kumar a/l Rajan', '$2y$10$example_hash_password3', 'Male', '+60-14-5551234', 'kumar@grandhotel.com', 2500.00);

-- --------------------------------------------------------

--
-- Table structure for table `hotel`
--

CREATE TABLE `hotel` (
  `HotelID` int(11) NOT NULL,
  `HotelName` varchar(100) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `City` varchar(50) NOT NULL,
  `Country` varchar(50) NOT NULL,
  `NumRooms` int(11) NOT NULL,
  `StarRating` int(11) DEFAULT NULL CHECK (`StarRating` between 1 and 5),
  `PhoneNo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel`
--

INSERT INTO `hotel` (`HotelID`, `HotelName`, `Address`, `City`, `Country`, `NumRooms`, `StarRating`, `PhoneNo`) VALUES
(1, 'Grand Hotel Melaka', '123 Jonker Street', 'Melaka', 'Malaysia', 50, 5, '+60-6-1234567'),
(2, 'Riverside Inn', '456 Beach Road', 'Melaka', 'Malaysia', 30, 4, '+60-6-7654321');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `BookingID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL,
  `PaymentDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `PaymentStatus` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `BookingID`, `Amount`, `PaymentMethod`, `PaymentDate`, `PaymentStatus`) VALUES
(1, 1, 750.00, 'Credit Card', '2025-11-15 09:39:10', 'Completed'),
(2, 2, 1350.00, 'Online Banking', '2025-11-15 09:39:10', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `RoleID` int(11) NOT NULL,
  `RoleTitle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`RoleID`, `RoleTitle`) VALUES
(4, 'Admin'),
(3, 'Housekeeping'),
(1, 'Manager'),
(2, 'Receptionist');

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `RoomID` int(11) NOT NULL,
  `HotelID` int(11) NOT NULL,
  `RoomType` varchar(50) NOT NULL,
  `RoomPrice` decimal(10,2) NOT NULL,
  `RoomDesc` text DEFAULT NULL,
  `RoomStatus` varchar(20) DEFAULT 'Available',
  `Capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`RoomID`, `HotelID`, `RoomType`, `RoomPrice`, `RoomDesc`, `RoomStatus`, `Capacity`) VALUES
(1, 1, 'Deluxe Room', 250.00, 'Spacious room with city view', 'Available', 2),
(2, 1, 'Suite', 450.00, 'Luxury suite with ocean view', 'Available', 4),
(3, 1, 'Standard Room', 150.00, 'Comfortable standard room', 'Available', 2),
(4, 2, 'Family Room', 350.00, 'Large room for families', 'Available', 5),
(5, 2, 'Standard Room', 120.00, 'Basic accommodation', 'Available', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tenant`
--

CREATE TABLE `tenant` (
  `TenantID` int(11) NOT NULL,
  `TenantName` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `PhoneNo` varchar(20) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Country` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant`
--

INSERT INTO `tenant` (`TenantID`, `TenantName`, `Password`, `PhoneNo`, `Gender`, `Email`, `Country`) VALUES
(1, 'John Doe', '$2y$10$example_hash_password4', '+60-16-7778889', 'Male', 'john.doe@email.com', 'Singapore'),
(2, 'Mary Tan', '$2y$10$example_hash_password5', '+60-17-4445556', 'Female', 'mary.tan@email.com', 'Malaysia');

-- --------------------------------------------------------

--
-- Structure for view `availablerooms`
--
DROP TABLE IF EXISTS `availablerooms`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `availablerooms`  AS SELECT `r`.`RoomID` AS `RoomID`, `r`.`RoomType` AS `RoomType`, `r`.`RoomPrice` AS `RoomPrice`, `r`.`RoomDesc` AS `RoomDesc`, `r`.`Capacity` AS `Capacity`, `h`.`HotelName` AS `HotelName` FROM (`room` `r` join `hotel` `h` on(`r`.`HotelID` = `h`.`HotelID`)) WHERE `r`.`RoomStatus` = 'Available' ;

-- --------------------------------------------------------

--
-- Structure for view `bookingdetails`
--
DROP TABLE IF EXISTS `bookingdetails`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `bookingdetails`  AS SELECT `b`.`BookingID` AS `BookingID`, `t`.`TenantName` AS `TenantName`, `t`.`Email` AS `Email`, `r`.`RoomType` AS `RoomType`, `h`.`HotelName` AS `HotelName`, `b`.`CheckInDate` AS `CheckInDate`, `b`.`CheckOutDate` AS `CheckOutDate`, `b`.`NumberOfTenant` AS `NumberOfTenant`, `b`.`Status` AS `Status`, `p`.`Amount` AS `Amount`, `p`.`PaymentStatus` AS `PaymentStatus` FROM ((((`booking` `b` join `tenant` `t` on(`b`.`TenantID` = `t`.`TenantID`)) join `room` `r` on(`b`.`RoomID` = `r`.`RoomID`)) join `hotel` `h` on(`r`.`HotelID` = `h`.`HotelID`)) left join `payment` `p` on(`b`.`BookingID` = `p`.`BookingID`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bill`
--
ALTER TABLE `bill`
  ADD PRIMARY KEY (`BillID`),
  ADD KEY `PaymentID` (`PaymentID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `RoomID` (`RoomID`),
  ADD KEY `TenantID` (`TenantID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `RoleID` (`RoleID`);

--
-- Indexes for table `hotel`
--
ALTER TABLE `hotel`
  ADD PRIMARY KEY (`HotelID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `BookingID` (`BookingID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `RoleTitle` (`RoleTitle`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`RoomID`),
  ADD KEY `HotelID` (`HotelID`);

--
-- Indexes for table `tenant`
--
ALTER TABLE `tenant`
  ADD PRIMARY KEY (`TenantID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bill`
--
ALTER TABLE `bill`
  MODIFY `BillID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hotel`
--
ALTER TABLE `hotel`
  MODIFY `HotelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `RoomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tenant`
--
ALTER TABLE `tenant`
  MODIFY `TenantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bill`
--
ALTER TABLE `bill`
  ADD CONSTRAINT `bill_ibfk_1` FOREIGN KEY (`PaymentID`) REFERENCES `payment` (`PaymentID`) ON DELETE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`RoomID`) REFERENCES `room` (`RoomID`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`TenantID`) REFERENCES `tenant` (`TenantID`) ON DELETE CASCADE;

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `role` (`RoleID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`) ON DELETE CASCADE;

--
-- Constraints for table `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `room_ibfk_1` FOREIGN KEY (`HotelID`) REFERENCES `hotel` (`HotelID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

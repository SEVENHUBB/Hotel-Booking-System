-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 05:08 AM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `RoleID` int(11) DEFAULT NULL,
  `HotelID` int(11) DEFAULT NULL,
  `AdminName` varchar(100) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `PhoneNo` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `RoleID`, `HotelID`, `AdminName`, `Password`, `Gender`, `PhoneNo`, `Email`, `Salary`) VALUES
(7, NULL, NULL, NULL, '$2y$10$cMG5gV1Uiz.XurdzU02kYO/32joCd1oHJYsS5KD0iJfdv0oAC4aaK', NULL, NULL, 'admin@gmail.com', NULL);

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
  `PaymentID` int(11) DEFAULT NULL,
  `TenantName` varchar(100) DEFAULT NULL,
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
  `RoomID` int(11) DEFAULT NULL,
  `TenantID` int(11) DEFAULT NULL,
  `CheckInDate` date DEFAULT NULL,
  `CheckOutDate` date DEFAULT NULL,
  `NumberOfTenant` int(11) DEFAULT NULL,
  `BookingDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotel`
--

CREATE TABLE `hotel` (
  `HotelID` int(11) NOT NULL,
  `HotelName` varchar(100) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `City` varchar(50) DEFAULT NULL,
  `Country` varchar(50) DEFAULT NULL,
  `NumRooms` int(11) DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `StarRating` int(11) DEFAULT NULL,
  `ImagePath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel`
--

INSERT INTO `hotel` (`HotelID`, `HotelName`, `Description`, `Address`, `City`, `Country`, `NumRooms`, `Category`, `StarRating`, `ImagePath`) VALUES
(15, 'Sex Hotel', 'located at setapak', 'Sungai Petani', 'Selangor', 'Malaysia', 20, '15', 4, 'images/hotel_photo/hotel_69422b40da830.png'),
(67, 'Gay Hotel', 'Gay Hotel', 'KL', 'Kedah', 'Malaysia', 15, '4', 5, 'images/hotel_photo/hotel_69418b1bbceda.png'),
(152, 'Purest Hotel', 'Located at Sungai Petani', 'Sungai Petani', 'Kedah', 'Malaysia', 45, '5', 5, 'images/hotel_photo/hotel_6941173bc72ee.png'),
(123456, 'Purest Hotel', 'Purest Hotel', 'Sungai Petani', 'Kedah', 'Malaysia', 15, '4', 5, 'images/hotel_photo/hotel_69417f528db2b.png');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otp`
--

CREATE TABLE `password_reset_otp` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_otp`
--

INSERT INTO `password_reset_otp` (`id`, `email`, `otp`, `created_at`, `expires_at`, `is_used`) VALUES
(4, 'leon@gmail.com', '696944', '2025-11-16 07:20:40', '2025-11-16 00:30:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `BookingID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `PaymentStatus` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `RoleID` int(11) NOT NULL,
  `RoleTitle` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `RoomID` int(11) NOT NULL,
  `HotelID` int(11) DEFAULT NULL,
  `TenantID` int(11) DEFAULT NULL,
  `RoomType` varchar(50) DEFAULT NULL,
  `RoomPrice` decimal(10,2) DEFAULT NULL,
  `RoomDesc` text DEFAULT NULL,
  `RoomImage` varchar(255) DEFAULT NULL,
  `RoomStatus` varchar(20) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`RoomID`, `HotelID`, `TenantID`, `RoomType`, `RoomPrice`, `RoomDesc`, `RoomImage`, `RoomStatus`, `Capacity`) VALUES
(4, 67, NULL, 'Single Room', 150.00, 'very good', NULL, 'Available', 1),
(5, 152, NULL, 'Queen Size', 400.00, 'good', NULL, 'Available', 2),
(6, 152, NULL, 'Double Room', 300.00, 'good', NULL, 'Available', 2),
(7, 15, NULL, 'Queen Size', 400.00, 'noob', NULL, 'Available', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tenant`
--

CREATE TABLE `tenant` (
  `TenantID` int(11) NOT NULL,
  `RoleID` int(11) DEFAULT NULL,
  `TenantName` varchar(100) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `PhoneNo` varchar(20) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Country` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant`
--

INSERT INTO `tenant` (`TenantID`, `RoleID`, `TenantName`, `Password`, `PhoneNo`, `Gender`, `Email`, `FullName`, `Country`) VALUES
(2, NULL, 'david', '$2y$10$0j.dc/AFNendhIQlH6pFnOQayJWxTyNX.hqdsxxV9g3OZ8z.qhOZa', '0185878187', 'Male', 'davidtao@gmail.com', 'DavidTao', 'Malaysia'),
(3, NULL, 'yeesiang', '$2y$10$J2wkex39eN0VrwwnGZxHB.6H1xF6n9s26bojaLTu/Gd1GgD7Bp7.S', '0185878187', 'Male', 'tangyeesiang2006@gmail.com', 'Tang Yee Siang', 'Singapore'),
(4, NULL, '123', '$2y$10$VbSt9BQen5janFr06Mj8ee0gm4bYiwW9KE3RDeixLLm7hPjC4yS.K', '123456789', 'Male', '123@gmail.com', '123', 'Malaysia');

-- --------------------------------------------------------

--
-- Structure for view `availablerooms`
--
DROP TABLE IF EXISTS `availablerooms`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `availablerooms`  AS SELECT `r`.`RoomID` AS `RoomID`, `r`.`RoomType` AS `RoomType`, `r`.`RoomPrice` AS `RoomPrice`, `r`.`RoomDesc` AS `RoomDesc`, `r`.`Capacity` AS `Capacity`, `h`.`HotelName` AS `HotelName` FROM (`room` `r` join `hotel` `h` on(`r`.`HotelID` = `h`.`HotelID`)) WHERE `r`.`RoomStatus` = 'Available' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD KEY `RoleID` (`RoleID`);

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
-- Indexes for table `hotel`
--
ALTER TABLE `hotel`
  ADD PRIMARY KEY (`HotelID`);

--
-- Indexes for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires` (`expires_at`);

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
  ADD PRIMARY KEY (`RoleID`);

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
  ADD KEY `RoleID` (`RoleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bill`
--
ALTER TABLE `bill`
  MODIFY `BillID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hotel`
--
ALTER TABLE `hotel`
  MODIFY `HotelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123457;

--
-- AUTO_INCREMENT for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `RoomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tenant`
--
ALTER TABLE `tenant`
  MODIFY `TenantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `role` (`RoleID`);

--
-- Constraints for table `bill`
--
ALTER TABLE `bill`
  ADD CONSTRAINT `bill_ibfk_1` FOREIGN KEY (`PaymentID`) REFERENCES `payment` (`PaymentID`);

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`RoomID`) REFERENCES `room` (`RoomID`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`TenantID`) REFERENCES `tenant` (`TenantID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`);

--
-- Constraints for table `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `room_ibfk_1` FOREIGN KEY (`HotelID`) REFERENCES `hotel` (`HotelID`);

--
-- Constraints for table `tenant`
--
ALTER TABLE `tenant`
  ADD CONSTRAINT `tenant_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `role` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

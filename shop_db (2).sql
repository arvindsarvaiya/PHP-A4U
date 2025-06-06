-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2025 at 08:39 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `caretaker_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caretakers`
--

CREATE TABLE `caretakers` (
  `caretaker_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `profile_img` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `resume_type` varchar(50) DEFAULT NULL,
  `applied_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caretakers`
--

INSERT INTO `caretakers` (`caretaker_id`, `user_id`, `name`, `age`, `hourly_rate`, `specialization`, `description`, `profile_img`, `resume_path`, `gender`, `is_approved`, `is_available`, `resume_type`, `applied_at`, `created_at`) VALUES
(3, 19, NULL, 45, '444.00', 'medical', NULL, 'default.jpg', NULL, 'female', 1, 1, 'application/pdf', '2025-04-10 03:47:20', '2025-04-09 22:17:20'),
(5, 22, NULL, 25, '55.00', 'child_care', 'i want this job so bad...................', 'CT-67f7739e3cbd05.71718829.jpg', NULL, 'female', 1, 1, 'application/pdf', '2025-04-10 13:00:38', '2025-04-10 07:30:38');

-- --------------------------------------------------------

--
-- Table structure for table `caretaker_availability`
--

CREATE TABLE `caretaker_availability` (
  `availability_id` int(11) NOT NULL,
  `caretaker_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caretaker_bookings`
--

CREATE TABLE `caretaker_bookings` (
  `booking_id` int(11) NOT NULL,
  `caretaker_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `hours` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `report_status` enum('pending','submitted','late','missed') DEFAULT 'pending',
  `report_deadline` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caretaker_bookings`
--

INSERT INTO `caretaker_bookings` (`booking_id`, `caretaker_id`, `user_id`, `start_datetime`, `end_datetime`, `hours`, `total_price`, `status`, `created_at`, `report_status`, `report_deadline`) VALUES
(10, 5, 24, '2025-04-10 15:01:00', '2025-04-10 17:01:00', 2, '110.00', 'completed', '2025-04-10 09:30:55', 'pending', '2025-04-11 17:01:00'),
(13, 3, 24, '2025-04-10 14:34:00', '2025-04-10 16:34:00', 2, '888.00', 'completed', '2025-04-10 09:43:30', 'submitted', '2025-04-11 16:34:00'),
(14, 5, 20, '2025-04-10 17:22:00', '2025-04-10 19:22:00', 2, '110.00', 'completed', '2025-04-10 11:51:51', 'pending', '2025-04-11 19:22:00'),
(16, 3, 24, '2025-04-10 17:41:00', '2025-04-10 18:41:00', 1, '444.00', 'completed', '2025-04-10 12:10:56', 'pending', '2025-04-11 18:41:00'),
(17, 5, 25, '2025-04-11 08:46:00', '2025-04-11 09:46:00', 1, '55.00', 'completed', '2025-04-11 00:16:14', 'pending', '2025-04-12 09:46:00'),
(18, 5, 16, '2025-04-11 10:56:00', '2025-04-11 11:56:00', 1, '55.00', 'pending', '2025-04-11 05:26:11', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `caretaker_reports`
--

CREATE TABLE `caretaker_reports` (
  `report_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `caretaker_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_address` text NOT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `situation_before` text NOT NULL,
  `situation_after` text NOT NULL,
  `report_pdf` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_on_time` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caretaker_reports`
--

INSERT INTO `caretaker_reports` (`report_id`, `booking_id`, `caretaker_id`, `client_name`, `client_address`, `client_email`, `situation_before`, `situation_after`, `report_pdf`, `submitted_at`, `is_on_time`) VALUES
(1, 13, 3, 'shiv', 'yiweyr8wie jeutr73w4 uiew6r384', 'shiv@gmail.com', 'bad', 'good', 'uploads/reports/REPORT_13_1744337332.pdf', '2025-04-11 02:08:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `caretaker_services`
--

CREATE TABLE `caretaker_services` (
  `service_id` int(11) NOT NULL,
  `caretaker_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `item_type` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `pid`, `booking_id`, `item_type`, `name`, `price`, `quantity`, `image`) VALUES
(136, 23, 5, NULL, NULL, '', 55, 1, 'CT-67f7739e3cbd05.71718829.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user_id`, `name`, `email`, `number`, `message`) VALUES
(14, 16, 'Arvind B Sarvaiya', 'absarsh@gmail.com', '07600012045', 'i am sleepyyyyy');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status`) VALUES
(19, 16, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ', cottage rose (1) ', 15, '09-Apr-2025', 'completed'),
(20, 24, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ,  (1) ,  (1) ', 888, '10-Apr-2025', 'completed'),
(21, 20, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ', 110, '10-Apr-2025', 'completed'),
(22, 24, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ,  (1) ', 444, '10-Apr-2025', 'pending'),
(23, 25, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ', 55, '11-Apr-2025', 'completed'),
(24, 16, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ', lavendor rose (1) ', 13, '11-Apr-2025', 'completed'),
(25, 16, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'credit card', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ', 55, '11-Apr-2025', 'completed'),
(26, 25, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'paypal', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ', veg salad (1) ', 15, '11-Apr-2025', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `details` varchar(500) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `details`, `price`, `image`) VALUES
(21, 'khichdi', 'healthy food ', 10, 'pic7.jpg'),
(22, 'veg salad', 'healthy food', 15, 'pic4.jpg'),
(23, 'fruits', 'to improve health and immunity', 25, 'pic14.jpg'),
(24, 'lunch combo', 'healthy lunch ', 80, 'pic10.png'),
(25, 'oats', 'healthy food', 55, 'pic3.jpg'),
(26, 'soup', 'healthy food', 35, 'pic15.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user',
  `application_status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `application_status`) VALUES
(10, 'admin A', 'admin01@gmail.com', '698d51a19d8a121ce581499d7b701668', 'admin', 'pending'),
(16, 'saumya', 'absarsh@gmail.com', '698d51a19d8a121ce581499d7b701668', 'caretaker', 'pending'),
(17, 'abc', 'test@gmail.com', '698d51a19d8a121ce581499d7b701668', 'admin', 'pending'),
(18, 'abc', 'smadhukanta@gmail.com', '698d51a19d8a121ce581499d7b701668', 'caretaker', 'pending'),
(19, 'happy', 'behappy@gmail.com', '698d51a19d8a121ce581499d7b701668', 'caretaker', 'pending'),
(20, 'karina', 'karina@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'rejected'),
(21, 'Admin User', 'admin@example.com', '0192023a7bbd73250516f069df18b500', 'admin', 'pending'),
(22, 'aarti', 'aarti@gmail.com', '698d51a19d8a121ce581499d7b701668', 'caretaker', 'pending'),
(23, 'jeel', 'jeel@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'pending'),
(24, 'shiv', 'shiv@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'rejected'),
(25, 'user', 'user@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'pending'),
(28, 'zoya hudda', 'zoyahudda563@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'rejected');

-- --------------------------------------------------------

--
-- Table structure for table `visit_reports`
--

CREATE TABLE `visit_reports` (
  `report_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `caretaker_notes` text DEFAULT NULL,
  `health_condition` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `caretaker_id` (`caretaker_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `caretakers`
--
ALTER TABLE `caretakers`
  ADD PRIMARY KEY (`caretaker_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `caretaker_availability`
--
ALTER TABLE `caretaker_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `caretaker_id` (`caretaker_id`);

--
-- Indexes for table `caretaker_bookings`
--
ALTER TABLE `caretaker_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `caretaker_id` (`caretaker_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `caretaker_reports`
--
ALTER TABLE `caretaker_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `caretaker_id` (`caretaker_id`);

--
-- Indexes for table `caretaker_services`
--
ALTER TABLE `caretaker_services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `caretaker_id` (`caretaker_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visit_reports`
--
ALTER TABLE `visit_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `caretakers`
--
ALTER TABLE `caretakers`
  MODIFY `caretaker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `caretaker_availability`
--
ALTER TABLE `caretaker_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `caretaker_bookings`
--
ALTER TABLE `caretaker_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `caretaker_reports`
--
ALTER TABLE `caretaker_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `caretaker_services`
--
ALTER TABLE `caretaker_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `visit_reports`
--
ALTER TABLE `visit_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`caretaker_id`) REFERENCES `caretakers` (`caretaker_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `caretakers`
--
ALTER TABLE `caretakers`
  ADD CONSTRAINT `caretakers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `caretaker_availability`
--
ALTER TABLE `caretaker_availability`
  ADD CONSTRAINT `caretaker_availability_ibfk_1` FOREIGN KEY (`caretaker_id`) REFERENCES `caretakers` (`caretaker_id`) ON DELETE CASCADE;

--
-- Constraints for table `caretaker_bookings`
--
ALTER TABLE `caretaker_bookings`
  ADD CONSTRAINT `caretaker_bookings_ibfk_1` FOREIGN KEY (`caretaker_id`) REFERENCES `caretakers` (`caretaker_id`),
  ADD CONSTRAINT `caretaker_bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `caretaker_reports`
--
ALTER TABLE `caretaker_reports`
  ADD CONSTRAINT `caretaker_reports_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `caretaker_bookings` (`booking_id`),
  ADD CONSTRAINT `caretaker_reports_ibfk_2` FOREIGN KEY (`caretaker_id`) REFERENCES `caretakers` (`caretaker_id`);

--
-- Constraints for table `caretaker_services`
--
ALTER TABLE `caretaker_services`
  ADD CONSTRAINT `caretaker_services_ibfk_1` FOREIGN KEY (`caretaker_id`) REFERENCES `caretakers` (`caretaker_id`);

--
-- Constraints for table `visit_reports`
--
ALTER TABLE `visit_reports`
  ADD CONSTRAINT `visit_reports_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 07:13 PM
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
(1, 16, NULL, 25, '0.00', '', NULL, 'default.jpg', NULL, 'female', 1, 1, 'application/pdf', '2025-04-10 02:19:40', '2025-04-09 20:49:40'),
(2, 18, NULL, 23, '0.00', 'general', NULL, 'default.jpg', NULL, 'male', 1, 1, 'application/pdf', '2025-04-10 02:55:48', '2025-04-09 21:25:48'),
(3, 19, NULL, 45, '444.00', 'medical', NULL, 'default.jpg', NULL, 'female', 1, 1, 'application/pdf', '2025-04-10 03:47:20', '2025-04-09 22:17:20'),
(4, 20, NULL, 23, '56.00', 'elderly_care', NULL, 'default.jpg', NULL, 'female', 0, 1, 'application/pdf', '2025-04-10 03:53:56', '2025-04-09 22:23:56'),
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caretaker_bookings`
--

INSERT INTO `caretaker_bookings` (`booking_id`, `caretaker_id`, `user_id`, `start_datetime`, `end_datetime`, `hours`, `total_price`, `status`, `created_at`) VALUES
(1, 1, 20, '2025-04-10 11:26:00', '2025-04-10 12:26:00', 1, '0.00', 'pending', '2025-04-10 05:55:59'),
(2, 2, 20, '2025-04-10 11:34:00', '2025-04-10 13:34:00', 2, '0.00', 'pending', '2025-04-10 06:03:36'),
(10, 5, 24, '2025-04-10 15:01:00', '2025-04-10 17:01:00', 2, '110.00', 'pending', '2025-04-10 09:30:55'),
(12, 2, 24, '2025-04-10 15:11:00', '2025-04-10 17:11:00', 2, '0.00', 'pending', '2025-04-10 09:40:44'),
(13, 3, 24, '2025-04-10 14:34:00', '2025-04-10 16:34:00', 2, '888.00', 'pending', '2025-04-10 09:43:30'),
(14, 5, 20, '2025-04-10 17:22:00', '2025-04-10 19:22:00', 2, '110.00', 'pending', '2025-04-10 11:51:51'),
(15, 2, 24, '2025-04-10 17:28:00', '2025-04-10 18:28:00', 1, '0.00', 'pending', '2025-04-10 11:57:21'),
(16, 3, 24, '2025-04-10 17:41:00', '2025-04-10 18:41:00', 1, '444.00', 'pending', '2025-04-10 12:10:56');

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
(129, 14, 16, NULL, NULL, 'lavendor rose', 13, 1, 'lavendor rose.jpg'),
(131, 14, 15, NULL, NULL, 'cottage rose', 15, 1, 'cottage rose.jpg'),
(133, 15, 15, NULL, NULL, 'cottage rose', 15, 1, 'cottage rose.jpg'),
(134, 15, 16, NULL, NULL, 'lavendor rose', 13, 3, 'lavendor rose.jpg'),
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
(13, 14, 'shaikh anas', 'shaikh@gmail.com', '0987654321', 'hi, how are you?'),
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
(17, 14, 'shaikh anas', '0987654321', 'shaikh@gmail.com', 'credit card', 'flat no. 321, jogeshwari, mumbai, india - 654321', ', cottage rose (3) , pink bouquet (1) , yellow queen rose (1) ', 80, '11-Mar-2022', 'completed'),
(18, 14, 'shaikh anas', '1234567899', 'shaikh@gmail.com', 'paypal', 'flat no. 321, jogeshwari, mumbai, india - 654321', ', yellow queen rose (1) , pink rose (2) ', 40, '11-Mar-2022', 'pending'),
(19, 16, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ', cottage rose (1) ', 15, '09-Apr-2025', 'pending'),
(20, 24, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ,  (1) ,  (1) ', 888, '10-Apr-2025', 'completed'),
(21, 20, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ', 110, '10-Apr-2025', 'completed'),
(22, 24, 'Arvind B Sarvaiya', '07600012045', 'absarsh@gmail.com', 'cash on delivery', 'flat no. NEW TYPE- II/13,GSECL COLONY, DHUVARAN,T, DHUVARAN, DHUVARAN, India - 388610', ',  (1) ,  (1) ', 444, '10-Apr-2025', 'pending');

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
(15, 'cottage rose', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Eaque error earum quasi facere optio tenetur.', 15, 'cottage rose.jpg'),
(16, 'lavendor rose', 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Rem, nobis tenetur voluptatibus officiis odit minus fugit dolore accusantium fuga ipsa!', 13, 'lavendor rose.jpg');

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
(16, 'saumya', 'absarsh@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'caretaker', 'pending'),
(17, 'abc', 'test@gmail.com', '698d51a19d8a121ce581499d7b701668', 'admin', 'pending'),
(18, 'abc', 'smadhukanta@gmail.com', '698d51a19d8a121ce581499d7b701668', 'caretaker', 'pending'),
(19, 'happy', 'behappy@gmail.com', '698d51a19d8a121ce581499d7b701668', 'caretaker', 'pending'),
(20, 'karina', 'karina@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'pending'),
(21, 'Admin User', 'admin@example.com', '0192023a7bbd73250516f069df18b500', 'admin', 'pending'),
(22, 'aarti', 'aarti@gmail.com', '698d51a19d8a121ce581499d7b701668', 'caretaker', 'pending'),
(23, 'jeel', 'jeel@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'pending'),
(24, 'shiv', 'shiv@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'rejected'),
(25, 'user', 'user@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'pending'),
(26, 'saumya', 'zoyahudda563@gmail.com', '698d51a19d8a121ce581499d7b701668', 'user', 'rejected');

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
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `pid`, `name`, `price`, `image`) VALUES
(63, 0, 15, 'cottage rose', 15, 'cottage rose.jpg'),
(65, 20, 15, 'cottage rose', 15, 'cottage rose.jpg');

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
  MODIFY `caretaker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `caretaker_availability`
--
ALTER TABLE `caretaker_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `caretaker_bookings`
--
ALTER TABLE `caretaker_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `caretaker_services`
--
ALTER TABLE `caretaker_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `visit_reports`
--
ALTER TABLE `visit_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

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

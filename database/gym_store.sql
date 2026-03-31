-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 31, 2026 at 04:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`, `subject`) VALUES
(1, 'Navindi Thisara', 'navindithisara214@gmail.com', 'what are the special offers?', '2026-03-26 02:38:11', 'General Enquiry'),
(2, 'Navindi Thisara', 'navindithisara214@gmail.com', 'what are the special offers?', '2026-03-26 03:14:54', 'General Enquiry');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'COD',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `quantity`, `status`, `created_at`, `full_name`, `email`, `address`, `phone`, `city`, `postal`, `payment_method`, `total_amount`) VALUES
(14, 2, 0, 'Pending', '2026-03-27 11:44:51', 'Navindi Thisara', '', 'No. 02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'card', 26200.00),
(15, 2, 0, 'Pending', '2026-03-27 11:51:00', 'Navindi Thisara', '', 'No. 02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'card', 26200.00),
(16, 2, 2, 'Pending', '2026-03-27 11:56:12', 'Navindi Thisara', 'navindithisara214@gmail.com', 'No. 02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'card', 26200.00),
(17, 2, 2, 'Pending', '2026-03-27 12:05:56', 'Navindi Thisara', 'navindithisara214@gmail.com', 'No. 02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'card', 26200.00),
(18, 2, 2, 'Pending', '2026-03-27 12:07:51', 'Navindi Thisara', 'navindithisara214@gmail.com', 'No.02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'card', 26200.00),
(19, 2, 2, 'Pending', '2026-03-27 12:23:58', 'Navindi Thisara', 'navindithisara214@gmail.com', 'No.02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'card', 26200.00),
(20, 2, 1, 'Pending', '2026-03-27 12:26:53', 'Navindi Thisara', 'navindithisara214@gmail.com', 'No. 02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'cod', 3200.00),
(21, 2, 1, 'Pending', '2026-03-27 12:28:58', 'Navindi Thisara', 'navindithisara214@gmail.com', 'No. 02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'bank', 2900.00),
(22, 2, 1, 'Completed', '2026-03-27 12:34:55', 'Navindi Thisara', 'navindithisara214@gmail.com', 'No.02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'cod', 16500.00),
(23, 2, 0, 'Pending', '2026-03-30 02:36:16', '', '', '', '', '', '', '', 19600.00),
(24, 2, 0, 'Pending', '2026-03-30 02:36:17', '', '', '', '', '', '', '', 19600.00),
(25, 2, 0, 'Pending', '2026-03-30 02:47:32', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara.', '+94 76 745 5755', 'Ambalangoda', '80300', 'cod', 3200.00),
(26, 2, 0, 'Pending', '2026-03-30 02:51:30', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara.', '+94 76 745 5755', 'Ambalangoda', '80300', 'cod', 3200.00),
(27, 2, 0, 'Pending', '2026-03-30 02:55:07', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'bank', 18000.00),
(28, 2, 0, 'Pending', '2026-03-30 03:03:10', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'bank', 8200.00),
(29, 2, 0, 'Paid', '2026-03-30 03:10:50', 'Navindi Thisara', '', 'No. 02, Wadumulla, Wathugedara', '+94 76 734 5755', 'Ambalangoda', '80300', 'card', 8800.00),
(30, 2, 0, 'Pending', '2026-03-30 03:17:40', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'cod', 8200.00),
(31, 2, 0, 'Pending', '2026-03-30 03:17:56', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara.', '+94 76 734 5755', 'Ambalangoda', '80300', 'bank', 11400.00),
(32, 5, 0, 'Paid', '2026-03-31 04:52:49', 'Samir Shakya', '', 'No.03, Colombo', '+94 75 145 5715', 'Colombo', '76600', 'card', 8200.00),
(33, 2, 0, 'Pending', '2026-03-31 14:03:32', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara', '+76 734  5755', 'Ambalangoda', '80300', 'cod', 8200.00),
(34, 2, 0, 'Pending', '2026-03-31 14:07:54', 'Navindi Thisara', '', 'No.02, Wadumulla, Wathugedara', '+94 76 734 5755', 'Ambalangoda', '80300', 'cod', 13000.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(6, 19, 5, 1, 8200.00),
(7, 19, 8, 1, 18000.00),
(8, 20, 16, 1, 3200.00),
(9, 21, 9, 1, 2900.00),
(10, 22, 4, 1, 16500.00),
(11, 26, 16, 1, 3200.00),
(12, 27, 8, 1, 18000.00),
(13, 28, 5, 1, 8200.00),
(14, 29, 17, 1, 8800.00),
(15, 30, 5, 1, 8200.00),
(16, 31, 6, 1, 11400.00),
(17, 32, 5, 1, 8200.00),
(18, 33, 5, 1, 8200.00),
(19, 34, 11, 1, 13000.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
(11, 'admin@gymstore.com', '83c393ac60f941babfdc6370a7fa81b3ff99879ffe0d62dd1a3e8f4acf34da2f', '2026-03-27 22:22:12');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('Supplements','Equipment','Accessories') NOT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `badge` varchar(50) DEFAULT '',
  `icon` varchar(50) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `brand`, `price`, `quantity`, `description`, `image`, `badge`, `icon`) VALUES
(4, 'Whey Protein 2kg', 'Supplements', NULL, 16500.00, 50, NULL, 'whey.jpg', 'Best Seller', 'fa-jar'),
(5, 'Creatine Monohydrate', 'Supplements', NULL, 8200.00, 49, NULL, 'creatine.png', 'Popular', 'fa-flask'),
(6, 'Pre-Workout Powder', 'Supplements', NULL, 11400.00, 50, NULL, 'preworkout.png', 'New', 'fa-bolt'),
(7, 'BCAA Capsules 60s', 'Supplements', NULL, 6500.00, 50, NULL, 'bcaa.jpg', '', 'fa-capsules'),
(8, 'Mass Gainer 3kg', 'Supplements', NULL, 18000.00, 50, NULL, 'massgainer.png', 'New', 'fa-weight-scale'),
(9, 'Vitamin C 1000mg', 'Supplements', NULL, 2900.00, 50, NULL, 'vitaminc.png', '', 'fa-tablets'),
(10, 'Adjustable Dumbbells', 'Equipment', NULL, 42500.00, 50, NULL, 'dumbbells.png', 'Top Pick', 'fa-dumbbell'),
(11, 'Pull-Up Bar', 'Equipment', NULL, 13000.00, -1, NULL, 'pullupbar.png', 'Popular', 'fa-arrow-up'),
(12, 'Resistance Bands Set', 'Equipment', NULL, 6200.00, 50, NULL, 'bands.png', '', 'fa-circle-nodes'),
(13, 'Foam Roller', 'Equipment', NULL, 7500.00, 50, NULL, 'foamroller.png', '', 'fa-circle'),
(14, 'Yoga Mat Pro', 'Accessories', NULL, 9200.00, 50, NULL, 'yogamat.png', '', 'fa-person'),
(15, 'Gym Gloves', 'Accessories', NULL, 4900.00, 50, NULL, 'gloves.png', '', 'fa-hand'),
(16, 'Shaker Bottle 700ml', 'Accessories', NULL, 3200.00, 50, NULL, 'shaker.png', '', 'fa-bottle-water'),
(17, 'Gym Bag 35L', 'Accessories', NULL, 8800.00, 50, NULL, 'gymbag.png', 'New', 'fa-bag-shopping'),
(18, 'Knee Sleeves (Pair)', 'Accessories', NULL, 5600.00, 50, NULL, 'kneesleeves.png', '', 'fa-person-running'),
(19, 'Lifting Belt', 'Accessories', NULL, 7200.00, 50, NULL, 'liftingbelt.png', 'Popular', 'fa-circle-dot');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin', 'admin@gymstore.com', '$2y$10$HsFVaZgHXlg8sglEhqTfvu2okt4At.WD80PjyCzunUisqP7cThS1S', 'admin'),
(2, 'Navindi Thisara', 'navindithisara214@gmail.com', '$2y$10$1140axZ.Xgm2ojgN5svqZ.dZajFJwUHt0MBFLXtJzNRitbacp10LC', 'user'),
(3, 'Dinesh Kumar', 'dineshkumar63@gmail.com', '$2y$10$HLHuPCiVuNv9w/uL/rgHuOIgCPnTlHzTJUklCpAXil28lZxN4X15m', 'user'),
(4, 'Anusha Lakmali', 'anushalakmali721@gmail.com', '$2y$10$DgyNQpw/Lkg681BZTLyS9eKGi3wDN5v7ibw2ZBVMNB4aVUJhrHLCm', 'user'),
(5, 'Samir Shakya', 'samirshakya1312@gmail.com', '$2y$10$LePskBE79aTMQ.3AMnxNUedKPvK0nUHfi3fUfy23gQEwxF/.MMfJ6', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

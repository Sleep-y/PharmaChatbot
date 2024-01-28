-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2023 at 09:16 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharma`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `brand_name` text NOT NULL,
  `brand_description` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `brand_name`, `brand_description`, `status`) VALUES
(1, 'Biogesic', 'Test', 1),
(2, 'Formet', 'Formet ', 1),
(3, 'Poten Cee', 'Test', 1),
(4, 'Neurobion', 'Neurobion', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `inventory_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('pending','done','canceled') NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp(),
  `checkout_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `inventory_id`, `user_id`, `order_id`, `quantity`, `status`, `date_created`, `checkout_date`) VALUES
(1, 1, 2, 1, 1, 'pending', '2023-09-15', '2023-09-15'),
(2, 5, 2, 1, 2, 'pending', '2023-09-15', '2023-09-15'),
(3, 2, 2, 1, 2, 'pending', '2023-09-15', '2023-09-15');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `category_name`, `description`, `status`) VALUES
(1, 'Capsule', 'Description', 0),
(4, 'Nebule', 'Nebule description', 1),
(5, 'Tablet', 'Test', 1),
(6, 'Syrup', 'Test', 1),
(7, 'Sample', 'Description', 0);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_general`
--

CREATE TABLE `inventory_general` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `price_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `expiration_date` date NOT NULL,
  `serial_number` text NOT NULL,
  `product_number` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_general`
--

INSERT INTO `inventory_general` (`id`, `medicine_id`, `price_id`, `supplier_id`, `quantity`, `date_received`, `expiration_date`, `serial_number`, `product_number`) VALUES
(1, 1, 1, 3, 99, '2023-09-15', '2023-09-30', 'SRL0001', 'PROD23A0001'),
(2, 2, 2, 4, 102, '2023-09-15', '2023-09-26', 'SRL0002', 'PROD23A0002'),
(3, 3, 3, 5, 198, '2023-09-15', '2023-10-06', 'SRL0003', 'PROD23A0003'),
(4, 4, 4, 5, 100, '2023-09-15', '2023-10-03', 'SRL0004', 'PROD23A0004'),
(5, 1, 5, 3, 250, '2023-09-15', '2023-09-27', 'SRL0005', 'PROD23A0005');

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'cashier id',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`id`, `payment_id`, `order_id`, `user_id`, `date_created`) VALUES
(1, 1, 1, 1, '2023-09-15 07:13:22');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_profile`
--

CREATE TABLE `medicine_profile` (
  `id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `generic_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `dosage` text NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_profile`
--

INSERT INTO `medicine_profile` (`id`, `medicine_name`, `category_id`, `image`, `brand_id`, `generic_name`, `description`, `dosage`, `deleted`) VALUES
(1, 'Biogesic', 7, '09152023-024453_Biogesic.png', 1, 'Paracetamol', 'Test', '250', 0),
(2, 'Formet', 5, '09152023-025516_Formet.jpg', 2, 'Metformin', 'Formet', '21', 0),
(3, 'Ascorbic Acid', 5, '09152023-025606_Poten-Cee.jpg', 3, 'Ascorbic Acid', 'Ascorbic Acid', '1000', 0),
(4, 'Neurobion', 5, '09152023-025706_Neurobion.png', 4, 'Neurobion', 'Neurobion', '100', 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_subtotal` decimal(11,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `inventory_general_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `order_subtotal`, `quantity`, `inventory_general_id`) VALUES
(1, 1, '45.00', 1, 1),
(2, 1, '66.00', 2, 2),
(3, 1, '200.00', 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `order_tbl`
--

CREATE TABLE `order_tbl` (
  `id` int(11) NOT NULL,
  `order_number` varchar(32) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'set null if walk in',
  `subtotal` decimal(11,2) DEFAULT NULL,
  `discount` decimal(11,2) DEFAULT NULL,
  `overall_total` decimal(11,2) DEFAULT NULL,
  `type` enum('walk_in','online') NOT NULL,
  `date_ordered` date NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','preparing','to claim','claimed','declined','canceled') DEFAULT NULL COMMENT 'pending, preparing, to claim, claimed, declined, canceled',
  `note` text DEFAULT NULL,
  `prescription` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tbl`
--

INSERT INTO `order_tbl` (`id`, `order_number`, `user_id`, `subtotal`, `discount`, `overall_total`, `type`, `date_ordered`, `status`, `note`, `prescription`) VALUES
(1, 'ORD23A0001', 2, '311.00', '62.20', '248.80', 'online', '2023-09-15', 'claimed', NULL, '09152023-030847_received_1366990700832575.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `paid_amount` decimal(11,2) NOT NULL,
  `customer_change` decimal(11,2) NOT NULL,
  `date_paid` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `order_id`, `paid_amount`, `customer_change`, `date_paid`) VALUES
(1, 1, '500.00', '251.20', '2023-09-15');

-- --------------------------------------------------------

--
-- Table structure for table `price`
--

CREATE TABLE `price` (
  `id` int(11) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `status` enum('active','inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price`
--

INSERT INTO `price` (`id`, `price`, `status`) VALUES
(1, '45.00', 'active'),
(2, '33.00', 'active'),
(3, '100.00', 'active'),
(4, '90.00', 'active'),
(5, '100.00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `creation_date` date NOT NULL,
  `payment_amount` decimal(11,2) NOT NULL,
  `payment_date` date NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`id`, `supplier_id`, `created_by`, `medicine_id`, `creation_date`, `payment_amount`, `payment_date`, `quantity`) VALUES
(3, NULL, 1, 1, '2023-08-09', '5012.00', '2023-08-09', 5);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `total_quantity_sold` int(11) NOT NULL,
  `sales_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_id`, `total_quantity_sold`, `sales_date`) VALUES
(1, 1, 3, '2023-09-15');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `supplier_name` text NOT NULL,
  `address` text NOT NULL,
  `contact` varchar(32) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `supplier_name`, `address`, `contact`, `status`) VALUES
(3, 'Supplier1', 'Test', '098765', 1),
(4, 'Supplier2', 'Test', 'Test', 1),
(5, 'Supplier3', 'Test', 'Test', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uname` text NOT NULL,
  `fname` text NOT NULL,
  `mname` text DEFAULT NULL,
  `lname` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(250) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `avatar` text DEFAULT NULL,
  `isNew` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uname`, `fname`, `mname`, `lname`, `email`, `password`, `role`, `avatar`, `isNew`) VALUES
(1, 'Admin', 'Super', 'A', 'Admin', 'admin@email.com', '$argon2i$v=19$m=65536,t=4,p=1$cEdNeDRRRWUwR1VQMGtoRQ$FlSDL4rCgkTy/L2ceA2fmIgPWoeN73f5CgKmj8Fykdw', 'admin', '08232023-082651_person_5.jpg', NULL),
(2, 'uname1', 'John', 'Awdd', 'Montemar', 'montemar@gmail.com', '$argon2i$v=19$m=65536,t=4,p=1$aEVtY3pyRmZtQTNPM2FXdA$DuH66gPeocjaRTJxtwzzRT+tLb529XQiD0PsLjBGX5c', 'user', NULL, NULL),
(3, 'uname2', 'Test', 'Test', 'Test', 'awd@awd', '$argon2i$v=19$m=65536,t=4,p=1$elhZdzlSQVBTaFguQ3Qvag$IXVjsB6M0sxE9jYH/HnOmSalRZYFHZL49UiFoJy4RBA', 'admin', NULL, NULL),
(4, 'uname3', 'Test', 'Test', 'Test', 'test@test', '$argon2i$v=19$m=65536,t=4,p=1$czZVOXBrbFRFemtqd3NJeQ$2X5i31DVAt9YMdv6/CQcp2MF1EGQH1CT7rJDDxSRnEc', 'admin', NULL, NULL),
(5, 'Test2', 'Test', 'Test', 'Test', 'test4@email.com', '$argon2i$v=19$m=65536,t=4,p=1$QlJ2QVdmWnlPc3NZbzFBRQ$dlwlblh78LPeXFY2xZhAv2Kn64HDkDg1BYV7dFHlTlE', 'admin', NULL, NULL),
(6, 'Awd', 'Awd', 'Awd', 'Awd', 'awd@awd.com', '$argon2i$v=19$m=65536,t=4,p=1$Li5Zd2pWQXR4eExkdmRLaA$7FW0dekryGVwIiqJcwwhM5YPvct/tdqG1LRYqdfju/w', 'admin', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_id` (`inventory_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_general`
--
ALTER TABLE `inventory_general`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `price_id` (`price_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medicine_profile`
--
ALTER TABLE `medicine_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_general_id` (`inventory_general_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_tbl`
--
ALTER TABLE `order_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_ibfk_1` (`order_id`);

--
-- Indexes for table `price`
--
ALTER TABLE `price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory_general`
--
ALTER TABLE `inventory_general`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medicine_profile`
--
ALTER TABLE `medicine_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_tbl`
--
ALTER TABLE `order_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `price`
--
ALTER TABLE `price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_general` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `order_tbl` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `inventory_general`
--
ALTER TABLE `inventory_general`
  ADD CONSTRAINT `inventory_general_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicine_profile` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_general_ibfk_2` FOREIGN KEY (`price_id`) REFERENCES `price` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_general_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `order_tbl` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `medicine_profile`
--
ALTER TABLE `medicine_profile`
  ADD CONSTRAINT `medicine_profile_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `medicine_profile_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`inventory_general_id`) REFERENCES `inventory_general` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `order_tbl` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_tbl`
--
ALTER TABLE `order_tbl`
  ADD CONSTRAINT `order_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_tbl` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicine_profile` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

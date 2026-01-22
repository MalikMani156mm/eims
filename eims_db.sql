-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2026 at 08:21 AM
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
-- Database: `eims_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoriesID` int(11) NOT NULL,
  `categoryName` varchar(255) NOT NULL,
  `createdAT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoriesID`, `categoryName`, `createdAT`) VALUES
(1, 'Air Conditioner', '2026-01-16 18:20:25'),
(2, 'Refrigerator ', '2026-01-16 18:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `colorID` int(11) NOT NULL,
  `colorName` varchar(255) NOT NULL,
  `createdAT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`colorID`, `colorName`, `createdAT`) VALUES
(1, 'White', '2026-01-16 18:41:10'),
(2, 'Blue', '2026-01-16 18:41:10'),
(3, 'Black', '2026-01-16 18:41:10'),
(4, 'Brown', '2026-01-16 18:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

CREATE TABLE `models` (
  `modelID` int(11) NOT NULL,
  `modelName` varchar(255) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`modelID`, `modelName`, `categoryID`, `createdAt`) VALUES
(1, 'RS1817', 2, '2026-01-16 18:28:56'),
(2, 'Rd1817', 2, '2026-01-16 18:28:56'),
(5, 'w25', 1, '2026-01-21 14:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productID` int(11) NOT NULL,
  `productName` varchar(255) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `colorID` int(11) NOT NULL,
  `modelID` int(11) NOT NULL,
  `sizeID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `regionID` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `productName`, `categoryID`, `colorID`, `modelID`, `sizeID`, `quantity`, `regionID`, `description`, `createdAt`, `updatedAt`) VALUES
(2, 'Standing AC', 1, 3, 2, 1, 120, 1, 'Very Large Size AC for rooms', '2026-01-21 16:49:06', '2026-01-21 16:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `product_barcodes`
--

CREATE TABLE `product_barcodes` (
  `barcodeID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `barcode_value` varchar(255) NOT NULL,
  `status` enum('available','issued','damaged','returned') NOT NULL DEFAULT 'available',
  `issuedID` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `issuedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `regionID` int(11) NOT NULL,
  `regionName` varchar(255) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`regionID`, `regionName`, `createdAt`) VALUES
(1, 'Gulberg Office', '2026-01-21 07:15:34'),
(2, 'Lahore', '2026-01-21 07:16:49'),
(3, 'Islamabad', '2026-01-21 07:16:49'),
(4, 'Karachi', '2026-01-21 07:16:49'),
(5, 'Peshawar', '2026-01-21 07:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `sizeID` int(11) NOT NULL,
  `sizeName` varchar(255) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`sizeID`, `sizeName`, `categoryID`, `createdAt`) VALUES
(1, '1  Ton', 1, '2026-01-21 15:13:51'),
(2, '40 Kg', 2, '2026-01-21 15:14:04'),
(3, '1.5 Ton', 1, '2026-01-21 15:14:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','do','ledger') NOT NULL,
  `dashboard_access` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `dashboard_access`, `full_name`, `email`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'admin', 'admin', 'System Administrator', 'admin@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-01-21 11:30:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoriesID`),
  ADD UNIQUE KEY `categoriesID` (`categoriesID`,`categoryName`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`colorID`),
  ADD UNIQUE KEY `colorName` (`colorName`);

--
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`modelID`),
  ADD UNIQUE KEY `modelName` (`modelName`),
  ADD KEY `category_fk` (`categoryID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productID`),
  ADD KEY `fk_product_category` (`categoryID`),
  ADD KEY `fk_product_color` (`colorID`),
  ADD KEY `fk_product_model` (`modelID`),
  ADD KEY `fk_product_size` (`sizeID`),
  ADD KEY `fk_product_region` (`regionID`);

--
-- Indexes for table `product_barcodes`
--
ALTER TABLE `product_barcodes`
  ADD PRIMARY KEY (`barcodeID`),
  ADD UNIQUE KEY `unique_barcode` (`barcode_value`),
  ADD KEY `fk_barcode_product` (`productID`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`regionID`),
  ADD UNIQUE KEY `regionName` (`regionName`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`sizeID`),
  ADD UNIQUE KEY `sizeName` (`sizeName`),
  ADD KEY `category_fk` (`categoryID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoriesID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `colorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `modelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_barcodes`
--
ALTER TABLE `product_barcodes`
  MODIFY `barcodeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `regionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `sizeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `models`
--
ALTER TABLE `models`
  ADD CONSTRAINT `category_fk` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`categoriesID`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`categoriesID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_color` FOREIGN KEY (`colorID`) REFERENCES `colors` (`colorID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_model` FOREIGN KEY (`modelID`) REFERENCES `models` (`modelID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_region` FOREIGN KEY (`regionID`) REFERENCES `regions` (`regionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_size` FOREIGN KEY (`sizeID`) REFERENCES `sizes` (`sizeID`) ON DELETE CASCADE;

--
-- Constraints for table `product_barcodes`
--
ALTER TABLE `product_barcodes`
  ADD CONSTRAINT `fk_barcode_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

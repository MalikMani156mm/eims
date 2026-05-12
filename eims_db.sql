-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2026 at 05:39 PM
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
(1, 'Air Conditioner', '2026-01-16 18:20:25');

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
(4, 'Brown', '2026-01-16 18:41:10'),
(5, 'Silver', '2026-04-15 17:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `distributing_officer`
--

CREATE TABLE `distributing_officer` (
  `DO_ID` int(11) NOT NULL,
  `DO_Name` varchar(100) NOT NULL,
  `CNIC` varchar(20) NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `regionID` int(11) NOT NULL DEFAULT 1,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `approvedBy` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `distributing_officer`
--

INSERT INTO `distributing_officer` (`DO_ID`, `DO_Name`, `CNIC`, `contactNumber`, `Address`, `regionID`, `status`, `approvedBy`, `createdAt`, `updatedAt`) VALUES
(2, 'Malik Mani', '37405-0484579-9', 2147483647, 'KRL Rd', 5, 1, NULL, '2026-03-11 21:59:26', '2026-05-10 17:00:42'),
(3, 'Rafay', '74389-7329579-3', 388787878, 'KRL Rd', 2, 0, 1, '2026-04-15 17:34:44', '2026-05-10 17:00:55'),
(4, 'Abdul Rafay', '37405-3526770-7', 2147483647, 'KRL Rd', 1, 1, NULL, '2026-05-10 17:21:56', '2026-05-10 17:21:56'),
(5, 'Tygon', '37405-3526770-3', 2147483647, 'rwp', 5, 0, 1, '2026-05-10 17:26:44', '2026-05-10 20:13:41');

-- --------------------------------------------------------

--
-- Table structure for table `do_sales`
--

CREATE TABLE `do_sales` (
  `saleID` int(11) NOT NULL,
  `DO_ID` int(11) NOT NULL,
  `saleDate` datetime NOT NULL DEFAULT current_timestamp(),
  `totalAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amountPaid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pendingAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paymentDays` int(11) NOT NULL DEFAULT 0 COMMENT 'Days to return pending amount (max 45)',
  `dueDate` date DEFAULT NULL,
  `paymentStatus` enum('paid','partial','pending') NOT NULL DEFAULT 'pending',
  `regionID` int(11) NOT NULL,
  `approvedByAdmin` int(11) DEFAULT NULL,
  `approvedBySuperAdmin` int(11) DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `do_sale_items`
--

CREATE TABLE `do_sale_items` (
  `itemID` int(11) NOT NULL,
  `saleID` int(11) NOT NULL,
  `serialID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `batchNumber` varchar(100) NOT NULL,
  `sellingPrice` decimal(10,2) NOT NULL,
  `discountPercent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `finalPrice` decimal(10,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `do_sale_payments`
--

CREATE TABLE `do_sale_payments` (
  `paymentID` int(11) NOT NULL,
  `saleID` int(11) NOT NULL,
  `paymentAmount` decimal(10,2) NOT NULL,
  `paymentDate` datetime NOT NULL DEFAULT current_timestamp(),
  `paymentMethod` varchar(50) DEFAULT 'cash',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issue_parts_log`
--

CREATE TABLE `issue_parts_log` (
  `id` int(11) NOT NULL,
  `partName` varchar(255) NOT NULL,
  `regionID` int(11) NOT NULL,
  `batchName` varchar(100) DEFAULT NULL,
  `serialNumber` varchar(100) DEFAULT NULL,
  `quantityIssued` int(11) NOT NULL DEFAULT 0,
  `issuedTo` varchar(255) DEFAULT NULL,
  `issuedBy` int(11) DEFAULT NULL,
  `issuedByName` varchar(100) DEFAULT NULL,
  `issuedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_sales`
--

CREATE TABLE `ledger_sales` (
  `saleID` int(11) NOT NULL,
  `ledgerName` varchar(255) NOT NULL COMMENT 'Ledger name (entered at runtime)',
  `ledgerCNIC` varchar(20) DEFAULT NULL,
  `ledgerContact` varchar(20) DEFAULT NULL,
  `ledgerAddress` text DEFAULT NULL,
  `saleDate` datetime NOT NULL DEFAULT current_timestamp(),
  `totalAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amountPaid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pendingAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paymentDays` int(11) NOT NULL DEFAULT 0 COMMENT 'Days to return pending amount (max 45)',
  `dueDate` date DEFAULT NULL,
  `paymentStatus` enum('paid','partial','pending') NOT NULL DEFAULT 'pending',
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_sale_items`
--

CREATE TABLE `ledger_sale_items` (
  `itemID` int(11) NOT NULL,
  `saleID` int(11) NOT NULL,
  `serialID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `batchNumber` varchar(100) NOT NULL,
  `sellingPrice` decimal(10,2) NOT NULL,
  `discountPercent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `finalPrice` decimal(10,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_sale_payments`
--

CREATE TABLE `ledger_sale_payments` (
  `paymentID` int(11) NOT NULL,
  `saleID` int(11) NOT NULL,
  `paymentAmount` decimal(10,2) NOT NULL,
  `paymentDate` datetime NOT NULL DEFAULT current_timestamp(),
  `paymentMethod` varchar(50) DEFAULT 'cash',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'RS1817', 1, '2026-01-16 18:28:56'),
(2, 'Rd19967', 1, '2026-01-16 18:28:56'),
(5, 'WI65', 1, '2026-01-21 14:14:07'),
(6, 'OW25', 1, '2026-02-07 12:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `partID` int(11) NOT NULL,
  `partName` varchar(255) NOT NULL,
  `serialNumber` varchar(100) DEFAULT NULL,
  `regionID` int(11) DEFAULT 4,
  `batchName` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `used` int(11) NOT NULL DEFAULT 0,
  `status` enum('available','partial','used') NOT NULL DEFAULT 'available',
  `issuedToSerialNumber` varchar(100) DEFAULT NULL,
  `issuedDate` datetime DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parts`
--

INSERT INTO `parts` (`partID`, `partName`, `serialNumber`, `regionID`, `batchName`, `quantity`, `used`, `status`, `issuedToSerialNumber`, `issuedDate`, `createdAt`, `updatedAt`) VALUES
(19, 'Chips', '123', 4, 'May 26', 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:31:15', '2026-05-10 04:54:05'),
(20, 'Chips', '456', 4, 'May 26', 1, 0, 'available', NULL, NULL, '2026-05-10 04:31:15', '2026-05-10 04:31:15'),
(22, 'Machine', '789', 4, 'Apr 26', 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:36:07', '2026-05-10 04:54:05'),
(23, 'Machine', '100', 4, 'Apr 26', 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:36:07', '2026-05-10 04:54:05'),
(24, 'Compressor', NULL, 4, 'May 26', 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:41:22', '2026-05-10 04:54:05'),
(25, 'Compressor', NULL, 4, 'May 26', 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:41:22', '2026-05-10 04:54:05'),
(26, 'Compressor', NULL, 4, 'May 26', 1, 0, 'available', NULL, NULL, '2026-05-10 04:41:22', '2026-05-10 04:41:22'),
(27, 'Compressor', NULL, 4, 'May 26', 1, 0, 'available', NULL, NULL, '2026-05-10 04:42:11', '2026-05-10 04:42:11');

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
  `available` int(11) NOT NULL,
  `cost` int(11) NOT NULL,
  `regionID` int(11) NOT NULL DEFAULT 1,
  `batchNumber` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `productName`, `categoryID`, `colorID`, `modelID`, `sizeID`, `quantity`, `available`, `cost`, `regionID`, `batchNumber`, `description`, `createdAt`, `updatedAt`) VALUES
(5, 'AC Standing', 1, 1, 2, 1, 1, 1, 30000, 1, '0', '', '2026-05-09 09:29:24', '2026-05-10 10:53:34'),
(6, 'AC Tower', 1, 3, 1, 3, 1, 1, 45000, 1, '0', '', '2026-05-10 09:54:05', '2026-05-10 09:54:05');

-- --------------------------------------------------------

--
-- Table structure for table `product_batch`
--

CREATE TABLE `product_batch` (
  `batchID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `batchNumber` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_batch`
--

INSERT INTO `product_batch` (`batchID`, `productID`, `batchNumber`, `quantity`, `cost`, `createdAt`) VALUES
(10, 5, 'May 2026', 1, 30000.00, '2026-05-10 09:29:24'),
(11, 6, 'May 2026', 1, 44999.96, '2026-05-10 09:54:05');

-- --------------------------------------------------------

--
-- Table structure for table `product_serials`
--

CREATE TABLE `product_serials` (
  `serialID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `serialNumber` varchar(255) NOT NULL,
  `batchNumber` varchar(100) NOT NULL,
  `status` enum('available','issued','damaged') NOT NULL DEFAULT 'available',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_serials`
--

INSERT INTO `product_serials` (`serialID`, `productID`, `serialNumber`, `batchNumber`, `status`, `createdAt`) VALUES
(20, 5, '123', 'May 2026', 'available', '2026-05-10 09:29:24'),
(21, 6, '8907', 'May 2026', 'available', '2026-05-10 09:54:05');

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
(4, 'Karachi', '2026-04-27 16:43:43'),
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
(3, '1.5 Ton', 1, '2026-01-21 15:14:16'),
(5, '2 Ton', 1, '2026-04-20 20:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','user') NOT NULL,
  `dashboard_access` varchar(100) NOT NULL,
  `regionID` int(11) NOT NULL,
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

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `dashboard_access`, `regionID`, `full_name`, `email`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'superadmin', 'admin', 1, 'System Administrator', 'admin@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-05-10 11:40:20'),
(2, 'Regional_Admin', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'admin', 'admin', 1, 'Mahfaz', 'Mahfaz@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-03-15 07:23:11'),
(3, 'Regional_User', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'user', 'admin', 5, 'Worker', 'user@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-05-10 11:55:49');

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
-- Indexes for table `distributing_officer`
--
ALTER TABLE `distributing_officer`
  ADD PRIMARY KEY (`DO_ID`),
  ADD UNIQUE KEY `DO_Name` (`DO_Name`);

--
-- Indexes for table `do_sales`
--
ALTER TABLE `do_sales`
  ADD PRIMARY KEY (`saleID`),
  ADD KEY `DO_ID` (`DO_ID`),
  ADD KEY `saleDate` (`saleDate`),
  ADD KEY `paymentStatus` (`paymentStatus`),
  ADD KEY `region_Fkk` (`regionID`);

--
-- Indexes for table `do_sale_items`
--
ALTER TABLE `do_sale_items`
  ADD PRIMARY KEY (`itemID`),
  ADD KEY `saleID` (`saleID`),
  ADD KEY `serialID` (`serialID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `do_sale_payments`
--
ALTER TABLE `do_sale_payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `saleID` (`saleID`);

--
-- Indexes for table `issue_parts_log`
--
ALTER TABLE `issue_parts_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `part_idx` (`partName`),
  ADD KEY `region_idx` (`regionID`);

--
-- Indexes for table `ledger_sales`
--
ALTER TABLE `ledger_sales`
  ADD PRIMARY KEY (`saleID`),
  ADD KEY `ledgerName` (`ledgerName`),
  ADD KEY `saleDate` (`saleDate`),
  ADD KEY `paymentStatus` (`paymentStatus`);

--
-- Indexes for table `ledger_sale_items`
--
ALTER TABLE `ledger_sale_items`
  ADD PRIMARY KEY (`itemID`),
  ADD KEY `saleID` (`saleID`),
  ADD KEY `serialID` (`serialID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `ledger_sale_payments`
--
ALTER TABLE `ledger_sale_payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `saleID` (`saleID`);

--
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`modelID`),
  ADD UNIQUE KEY `modelName` (`modelName`),
  ADD KEY `category_fk` (`categoryID`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`partID`),
  ADD KEY `regionID` (`regionID`),
  ADD KEY `status` (`status`),
  ADD KEY `batchName` (`batchName`);

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
-- Indexes for table `product_batch`
--
ALTER TABLE `product_batch`
  ADD PRIMARY KEY (`batchID`),
  ADD KEY `fk_batch_product` (`productID`);

--
-- Indexes for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD PRIMARY KEY (`serialID`),
  ADD UNIQUE KEY `unique_serial` (`serialNumber`),
  ADD KEY `fk_serial_product` (`productID`),
  ADD KEY `idx_batch_number` (`batchNumber`);

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
  ADD KEY `idx_role` (`role`),
  ADD KEY `regionID_fk` (`regionID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoriesID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `colorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `distributing_officer`
--
ALTER TABLE `distributing_officer`
  MODIFY `DO_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `do_sales`
--
ALTER TABLE `do_sales`
  MODIFY `saleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `do_sale_items`
--
ALTER TABLE `do_sale_items`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `do_sale_payments`
--
ALTER TABLE `do_sale_payments`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_parts_log`
--
ALTER TABLE `issue_parts_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_sales`
--
ALTER TABLE `ledger_sales`
  MODIFY `saleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_sale_items`
--
ALTER TABLE `ledger_sale_items`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_sale_payments`
--
ALTER TABLE `ledger_sale_payments`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `modelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `partID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_batch`
--
ALTER TABLE `product_batch`
  MODIFY `batchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_serials`
--
ALTER TABLE `product_serials`
  MODIFY `serialID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `regionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `sizeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `do_sales`
--
ALTER TABLE `do_sales`
  ADD CONSTRAINT `fk_dosale_do` FOREIGN KEY (`DO_ID`) REFERENCES `distributing_officer` (`DO_ID`),
  ADD CONSTRAINT `region_Fkk` FOREIGN KEY (`regionID`) REFERENCES `regions` (`regionID`);

--
-- Constraints for table `do_sale_items`
--
ALTER TABLE `do_sale_items`
  ADD CONSTRAINT `fk_dosaleitem_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`),
  ADD CONSTRAINT `fk_dosaleitem_sale` FOREIGN KEY (`saleID`) REFERENCES `do_sales` (`saleID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dosaleitem_serial` FOREIGN KEY (`serialID`) REFERENCES `product_serials` (`serialID`);

--
-- Constraints for table `do_sale_payments`
--
ALTER TABLE `do_sale_payments`
  ADD CONSTRAINT `fk_dosale_payment` FOREIGN KEY (`saleID`) REFERENCES `do_sales` (`saleID`) ON DELETE CASCADE;

--
-- Constraints for table `ledger_sale_items`
--
ALTER TABLE `ledger_sale_items`
  ADD CONSTRAINT `fk_ledgersaleitem_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`),
  ADD CONSTRAINT `fk_ledgersaleitem_sale` FOREIGN KEY (`saleID`) REFERENCES `ledger_sales` (`saleID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ledgersaleitem_serial` FOREIGN KEY (`serialID`) REFERENCES `product_serials` (`serialID`);

--
-- Constraints for table `ledger_sale_payments`
--
ALTER TABLE `ledger_sale_payments`
  ADD CONSTRAINT `fk_ledgersale_payment` FOREIGN KEY (`saleID`) REFERENCES `ledger_sales` (`saleID`) ON DELETE CASCADE;

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
-- Constraints for table `product_batch`
--
ALTER TABLE `product_batch`
  ADD CONSTRAINT `fk_batch_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE;

--
-- Constraints for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD CONSTRAINT `fk_serial_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `regionID_fk` FOREIGN KEY (`regionID`) REFERENCES `regions` (`regionID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

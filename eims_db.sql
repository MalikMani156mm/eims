-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 12:37 PM
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
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brandID` int(11) NOT NULL,
  `brandName` varchar(100) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brandID`, `brandName`, `createdAt`) VALUES
(1, 'Haier', '2026-06-04 13:21:19'),
(2, 'Dawlance', '2026-06-04 13:21:28');

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
(5, 'Silver', '2026-04-15 17:29:25'),
(6, 'Golden', '2026-06-04 13:35:58');

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
(2, 'Malik Mani', '37405-0484579-9', '2147483647', 'KRL Rd', 5, 1, NULL, '2026-03-11 21:59:26', '2026-05-10 17:00:42'),
(3, 'Rafay', '74389-7329579-3', '388787878', 'KRL Rd', 2, 0, 1, '2026-04-15 17:34:44', '2026-05-10 17:00:55'),
(4, 'Abdul Rafay', '37405-3526770-7', '2147483647', 'KRL Rd', 1, 1, NULL, '2026-05-10 17:21:56', '2026-05-10 17:21:56'),
(5, 'Tygon', '37405-3526770-3', '2147483647', 'rwp', 5, 0, 1, '2026-05-10 17:26:44', '2026-05-10 20:13:41');

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

--
-- Dumping data for table `do_sales`
--

INSERT INTO `do_sales` (`saleID`, `DO_ID`, `saleDate`, `totalAmount`, `discountAmount`, `grandTotal`, `amountPaid`, `pendingAmount`, `paymentDays`, `dueDate`, `paymentStatus`, `regionID`, `approvedByAdmin`, `approvedBySuperAdmin`, `createdBy`, `createdAt`, `updatedAt`) VALUES
(1, 3, '2026-05-12 00:00:00', 30000.00, 0.00, 30000.00, 29500.00, 500.00, 0, NULL, 'partial', 4, 5, 1, 1, '2026-05-12 04:08:16', '2026-05-17 06:42:39'),
(3, 5, '2026-05-17 00:00:00', 120000.00, 0.00, 120000.00, 120000.00, 0.00, 10, '2026-05-29', 'paid', 4, 5, 1, 1, '2026-05-17 05:27:58', '2026-05-17 05:45:09');

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

--
-- Dumping data for table `do_sale_items`
--

INSERT INTO `do_sale_items` (`itemID`, `saleID`, `serialID`, `productID`, `batchNumber`, `sellingPrice`, `discountPercent`, `discountAmount`, `finalPrice`, `createdAt`) VALUES
(1, 1, 20, 5, 'May 2026', 30000.00, 0.00, 0.00, 30000.00, '2026-05-12 04:08:16'),
(3, 3, 23, 8, 'Jun 26', 120000.00, 0.00, 0.00, 120000.00, '2026-05-17 05:27:58');

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

--
-- Dumping data for table `do_sale_payments`
--

INSERT INTO `do_sale_payments` (`paymentID`, `saleID`, `paymentAmount`, `paymentDate`, `paymentMethod`, `notes`, `createdAt`) VALUES
(1, 1, 29000.00, '2026-05-12 09:08:16', 'cash', 'Initial payment', '2026-05-12 04:08:16'),
(2, 3, 120000.00, '2026-05-17 10:27:58', 'cash', 'Initial payment', '2026-05-17 05:27:58'),
(3, 1, 500.00, '2026-05-17 11:42:39', 'Cash', 'second time', '2026-05-17 06:42:39');

-- --------------------------------------------------------

--
-- Table structure for table `gas_batch_details`
--

CREATE TABLE `gas_batch_details` (
  `batch_id` int(11) NOT NULL,
  `gas_id` int(11) DEFAULT NULL,
  `batchName` varchar(255) NOT NULL,
  `regionID` int(11) DEFAULT 4,
  `quantity` decimal(10,2) DEFAULT 0.00,
  `available` decimal(10,2) DEFAULT 0.00,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gas_batch_details`
--

INSERT INTO `gas_batch_details` (`batch_id`, `gas_id`, `batchName`, `regionID`, `quantity`, `available`, `unit_price`, `total_price`, `createdAt`, `updatedAt`) VALUES
(2, 2, 'June 27', 4, 21.00, 0.00, 210.00, 4410.00, '2026-05-22 04:51:17', '2026-05-23 08:33:24'),
(3, 2, 'June 28', 4, 10.00, 0.00, 260.00, 2600.00, '2026-05-22 05:01:59', '2026-05-23 05:43:17'),
(4, 3, 'May 26', 4, 50.00, 24.50, 1000.00, 50000.00, '2026-05-23 05:13:21', '2026-05-23 08:33:24'),
(5, 2, 'Apr 26', 4, 33.00, 29.75, 330.00, 10890.00, '2026-05-23 08:23:41', '2026-05-23 08:33:24');

-- --------------------------------------------------------

--
-- Table structure for table `gas_logs`
--

CREATE TABLE `gas_logs` (
  `gas_log_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `gas_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gas_logs`
--

INSERT INTO `gas_logs` (`gas_log_id`, `product_id`, `serial_number`, `gas_id`, `batch_id`, `quantity_used`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 11, '9988', 2, 3, 10.00, 260.00, 2600.00, '2026-05-23 05:43:17'),
(3, 12, '786', 3, 4, 25.50, 1000.00, 25500.00, '2026-05-23 08:33:24'),
(4, 12, '786', 2, 5, 3.25, 330.00, 1072.50, '2026-05-23 08:33:24'),
(5, 12, '786', 2, 2, 20.00, 210.00, 4200.00, '2026-05-23 08:33:24');

-- --------------------------------------------------------

--
-- Table structure for table `gas_master`
--

CREATE TABLE `gas_master` (
  `gas_id` int(11) NOT NULL,
  `gas_name` varchar(255) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gas_master`
--

INSERT INTO `gas_master` (`gas_id`, `gas_name`, `batch_id`, `quantity`, `unit_price`, `createdAt`, `updatedAt`) VALUES
(2, 'Hydrogen', 2, 64.00, 279.69, '2026-05-22 04:51:17', '2026-05-23 08:23:41'),
(3, 'Helium', 4, 50.00, 1000.00, '2026-05-23 05:13:21', '2026-05-23 05:13:21');

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
  `brandID` int(11) NOT NULL,
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

INSERT INTO `parts` (`partID`, `partName`, `serialNumber`, `regionID`, `batchName`, `brandID`, `quantity`, `used`, `status`, `issuedToSerialNumber`, `issuedDate`, `createdAt`, `updatedAt`) VALUES
(19, 'Chips', '123', 4, 'May 26', 1, 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:31:15', '2026-05-10 04:54:05'),
(20, 'Chips', '456', 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-10 04:31:15', '2026-05-12 10:41:11'),
(22, 'Machine', '789', 4, 'Apr 26', 1, 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:36:07', '2026-05-10 04:54:05'),
(23, 'Machine', '100', 4, 'Apr 26', 1, 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:36:07', '2026-05-10 04:54:05'),
(24, 'Compressor', NULL, 4, 'May 26', 1, 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:41:22', '2026-05-10 04:54:05'),
(25, 'Compressor', NULL, 4, 'May 26', 1, 0, 1, 'used', '8907', '2026-05-10 09:54:05', '2026-05-10 04:41:22', '2026-05-10 04:54:05'),
(26, 'Compressor', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-10 04:41:22', '2026-05-12 10:41:11'),
(27, 'Compressor', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-10 04:42:11', '2026-05-23 08:33:24'),
(28, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(29, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(30, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(31, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(32, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(33, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(34, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(35, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(36, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(37, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:27:21', '2026-05-12 10:41:11'),
(38, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(39, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(40, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(41, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(42, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(43, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(44, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(45, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(46, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(47, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '12344321', '2026-05-18 13:11:38', '2026-05-12 10:27:21', '2026-05-18 08:11:38'),
(48, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(49, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(50, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(51, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(52, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(53, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(54, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(55, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(56, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(57, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(58, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(59, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(60, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(61, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(62, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(63, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(64, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(65, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:27:21', '2026-05-23 05:43:17'),
(66, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(67, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(68, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(69, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(70, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(71, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(72, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(73, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(74, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(75, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(76, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(77, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(78, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(79, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(80, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(81, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(82, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(83, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(84, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(85, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(86, 'Fan', NULL, 4, 'May 26', 1, 1, 1, 'used', '786', '2026-05-23 13:33:24', '2026-05-12 10:27:21', '2026-05-23 08:33:24'),
(87, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(88, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(89, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(90, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(91, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(92, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(93, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(94, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(95, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(96, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(97, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(98, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(99, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(100, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(101, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(102, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(103, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(104, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(105, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(106, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(107, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(108, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(109, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(110, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(111, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(112, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(113, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(114, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(115, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(116, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(117, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(118, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(119, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(120, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(121, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(122, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(123, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(124, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(125, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(126, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(127, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(128, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(129, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(130, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(131, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(132, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(133, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(134, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(135, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(136, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(137, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(138, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(139, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(140, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(141, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(142, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(143, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(144, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(145, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(146, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(147, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(148, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(149, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(150, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(151, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(152, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(153, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(154, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(155, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(156, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(157, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(158, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(159, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(160, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(161, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(162, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(163, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(164, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(165, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(166, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(167, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(168, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(169, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(170, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(171, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(172, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(173, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(174, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(175, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(176, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(177, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(178, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(179, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(180, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(181, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(182, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(183, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(184, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(185, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(186, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(187, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(188, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(189, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(190, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(191, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(192, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(193, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(194, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(195, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(196, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(197, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(198, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(199, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(200, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(201, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(202, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(203, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(204, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(205, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(206, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(207, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(208, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(209, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(210, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(211, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(212, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(213, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(214, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(215, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(216, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(217, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(218, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(219, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(220, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(221, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(222, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(223, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(224, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(225, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(226, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(227, 'Fan', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-12 10:27:21', '2026-05-12 10:27:21'),
(228, 'Fan Machine', '1', 4, 'June 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:28:10', '2026-05-12 10:41:11'),
(229, 'Fan Machine', '2', 4, 'June 26', 1, 1, 1, 'used', '45678', '2026-05-12 15:41:11', '2026-05-12 10:28:10', '2026-05-12 10:41:11'),
(230, 'Fan Machine', '3', 4, 'June 26', 1, 1, 1, 'used', '9988', '2026-05-23 10:43:17', '2026-05-12 10:28:10', '2026-05-23 05:43:17'),
(231, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(232, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(233, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(234, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(235, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(236, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(237, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(238, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(239, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(240, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(241, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(242, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(243, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(244, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(245, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(246, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(247, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(248, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(249, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(250, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(251, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(252, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(253, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(254, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(255, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(256, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(257, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(258, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(259, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(260, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(261, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(262, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(263, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(264, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(265, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(266, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(267, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(268, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(269, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(270, 'Machine', NULL, 4, 'May 26', 1, 1, 0, 'available', NULL, NULL, '2026-05-14 12:42:25', '2026-05-14 12:42:25'),
(271, 'Compressor', NULL, 4, 'May 26', 2, 1, 0, '', NULL, NULL, '2026-06-04 10:28:33', '2026-06-04 10:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productID` int(11) NOT NULL,
  `productName` varchar(255) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `brandID` int(11) NOT NULL,
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

INSERT INTO `products` (`productID`, `productName`, `categoryID`, `brandID`, `colorID`, `modelID`, `sizeID`, `quantity`, `available`, `cost`, `regionID`, `batchNumber`, `description`, `createdAt`, `updatedAt`) VALUES
(5, 'AC Standing', 1, 1, 1, 2, 1, 1, 0, 30000, 1, '0', '', '2026-05-09 09:29:24', '2026-06-04 13:59:39'),
(6, 'AC Tower', 1, 2, 3, 1, 3, 1, 1, 45000, 5, '0', '', '2026-05-10 09:54:05', '2026-06-04 13:59:45'),
(8, 'AC Duct', 1, 1, 1, 2, 3, 1, 0, 120000, 4, '0', '', '2026-05-12 15:41:11', '2026-06-04 13:59:49'),
(11, 'test Ac', 1, 2, 4, 2, 3, 1, 1, 24000, 1, '0', '', '2026-05-23 10:43:17', '2026-06-04 13:59:54'),
(12, 'tests last', 1, 1, 4, 5, 3, 1, 1, 230000, 1, '0', '', '2026-05-23 13:33:24', '2026-06-04 13:59:58');

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
(11, 6, 'May 2026', 1, 44999.96, '2026-05-10 09:54:05'),
(13, 8, 'Jun 26', 1, 120000.00, '2026-05-12 15:41:11'),
(16, 11, 'May 2026', 1, 24000.00, '2026-05-23 10:43:17'),
(17, 12, 'May 2026', 1, 230000.00, '2026-05-23 13:33:24');

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
(20, 5, '123', 'May 2026', 'issued', '2026-05-10 09:29:24'),
(21, 6, '8907', 'May 2026', 'available', '2026-05-10 09:54:05'),
(23, 8, '45678', 'Jun 26', 'issued', '2026-05-12 15:41:11'),
(26, 11, '9988', 'May 2026', 'available', '2026-05-23 10:43:17'),
(27, 12, '786', 'May 2026', 'available', '2026-05-23 13:33:24');

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
  `dashboard_access` varchar(100) NOT NULL DEFAULT 'admin',
  `regionID` int(11) NOT NULL,
  `haveMultipleRegions` tinyint(4) NOT NULL DEFAULT 0,
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

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `dashboard_access`, `regionID`, `haveMultipleRegions`, `full_name`, `email`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'superadmin', 'admin', 1, 0, 'System Administrator', 'admin@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-05-10 11:40:20'),
(2, 'Regional_Admin', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'admin', 'admin', 4, 0, 'Mahfaz', 'Mahfaz@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-05-12 04:38:46'),
(3, 'Regional_User', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'user', 'admin', 5, 0, 'Worker', 'user@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-05-10 11:55:49'),
(4, 'Regional_User_K', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'user', 'admin', 4, 0, 'Worker', 'user@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-05-10 11:55:49'),
(5, 'Regional_Admin_K', '$2y$10$9/ecV55bJPVpNTsgsfRR7O7nTuyJdGNC3PN9FevR8vPBy.AfzV3TC', 'admin', 'admin', 4, 0, 'Mahfaz', 'Mahfaz@gmail.com', '03008765432', 1, '2025-12-20 10:50:28', '2026-05-12 04:38:46'),
(6, 'tester', '$2y$10$vRaihbGZ5wwkMLDopIUrnuMeHLNBeqvUJFZxsMc8zV8tig15adr8C', 'user', '', 3, 0, 'test', '', '', 1, '2026-05-17 05:53:01', '2026-05-17 05:53:01'),
(7, 'Admin_test', '$2y$10$1Z4DUt3rSKhRJjWcV5hlmeh/V1nfZlQRp.AiSwsFNdPq6Gz8WF8qi', 'admin', '', 1, 1, 'Malik Mani awn', 'malikmani156.mm@gmail.com', '', 1, '2026-05-17 06:08:14', '2026-05-18 14:40:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_regions`
--

CREATE TABLE `user_regions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `regionID` int(11) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_regions`
--

INSERT INTO `user_regions` (`id`, `user_id`, `regionID`, `createdAt`) VALUES
(1, 7, 1, '2026-05-17 11:08:14'),
(2, 7, 3, '2026-05-17 11:08:14'),
(3, 7, 4, '2026-05-17 11:08:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brandID`),
  ADD UNIQUE KEY `brandName` (`brandName`);

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
-- Indexes for table `gas_batch_details`
--
ALTER TABLE `gas_batch_details`
  ADD PRIMARY KEY (`batch_id`),
  ADD KEY `regionID` (`regionID`),
  ADD KEY `gas_id` (`gas_id`);

--
-- Indexes for table `gas_logs`
--
ALTER TABLE `gas_logs`
  ADD PRIMARY KEY (`gas_log_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `gas_id` (`gas_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Indexes for table `gas_master`
--
ALTER TABLE `gas_master`
  ADD PRIMARY KEY (`gas_id`),
  ADD UNIQUE KEY `gas_name` (`gas_name`),
  ADD KEY `batch_id` (`batch_id`);

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
  ADD KEY `fk_product_region` (`regionID`),
  ADD KEY `fk_brand` (`brandID`);

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
-- Indexes for table `user_regions`
--
ALTER TABLE `user_regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_user_region` (`user_id`,`regionID`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_regionID` (`regionID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brandID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoriesID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `colorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `distributing_officer`
--
ALTER TABLE `distributing_officer`
  MODIFY `DO_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `do_sales`
--
ALTER TABLE `do_sales`
  MODIFY `saleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `do_sale_items`
--
ALTER TABLE `do_sale_items`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `do_sale_payments`
--
ALTER TABLE `do_sale_payments`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gas_batch_details`
--
ALTER TABLE `gas_batch_details`
  MODIFY `batch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gas_logs`
--
ALTER TABLE `gas_logs`
  MODIFY `gas_log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gas_master`
--
ALTER TABLE `gas_master`
  MODIFY `gas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `partID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=272;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_batch`
--
ALTER TABLE `product_batch`
  MODIFY `batchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `product_serials`
--
ALTER TABLE `product_serials`
  MODIFY `serialID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_regions`
--
ALTER TABLE `user_regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `gas_batch_details`
--
ALTER TABLE `gas_batch_details`
  ADD CONSTRAINT `gas_batch_details_ibfk_1` FOREIGN KEY (`regionID`) REFERENCES `regions` (`regionID`),
  ADD CONSTRAINT `gas_batch_details_ibfk_2` FOREIGN KEY (`gas_id`) REFERENCES `gas_master` (`gas_id`) ON DELETE CASCADE;

--
-- Constraints for table `gas_logs`
--
ALTER TABLE `gas_logs`
  ADD CONSTRAINT `gas_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`productID`),
  ADD CONSTRAINT `gas_logs_ibfk_2` FOREIGN KEY (`gas_id`) REFERENCES `gas_master` (`gas_id`),
  ADD CONSTRAINT `gas_logs_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `gas_batch_details` (`batch_id`);

--
-- Constraints for table `gas_master`
--
ALTER TABLE `gas_master`
  ADD CONSTRAINT `gas_master_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `gas_batch_details` (`batch_id`) ON DELETE CASCADE;

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
-- Constraints for table `parts`
--
ALTER TABLE `parts`
  ADD CONSTRAINT `fk_brand_Id` FOREIGN KEY (`brandID`) REFERENCES `brands` (`brandID`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_brand` FOREIGN KEY (`brandID`) REFERENCES `brands` (`brandID`),
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

--
-- Constraints for table `user_regions`
--
ALTER TABLE `user_regions`
  ADD CONSTRAINT `fk_userregions_region` FOREIGN KEY (`regionID`) REFERENCES `regions` (`regionID`),
  ADD CONSTRAINT `fk_userregions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

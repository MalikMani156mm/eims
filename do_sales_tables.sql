-- Create DO Sales tables

-- Main sales table for DO distributions
CREATE TABLE IF NOT EXISTS `do_sales` (
  `saleID` int(11) NOT NULL AUTO_INCREMENT,
  `DO_ID` int(11) NOT NULL,
  `saleDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `totalAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amountPaid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pendingAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paymentDays` int(11) NOT NULL DEFAULT 0 COMMENT 'Days to return pending amount (max 45)',
  `dueDate` date DEFAULT NULL,
  `paymentStatus` enum('paid','partial','pending') NOT NULL DEFAULT 'pending',
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`saleID`),
  KEY `DO_ID` (`DO_ID`),
  KEY `saleDate` (`saleDate`),
  KEY `paymentStatus` (`paymentStatus`),
  CONSTRAINT `fk_dosale_do` FOREIGN KEY (`DO_ID`) REFERENCES `distributing_officer` (`DO_ID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sale items table - individual products sold
CREATE TABLE IF NOT EXISTS `do_sale_items` (
  `itemID` int(11) NOT NULL AUTO_INCREMENT,
  `saleID` int(11) NOT NULL,
  `serialID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `batchNumber` varchar(100) NOT NULL,
  `sellingPrice` decimal(10,2) NOT NULL,
  `discountPercent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `finalPrice` decimal(10,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`itemID`),
  KEY `saleID` (`saleID`),
  KEY `serialID` (`serialID`),
  KEY `productID` (`productID`),
  CONSTRAINT `fk_dosaleitem_sale` FOREIGN KEY (`saleID`) REFERENCES `do_sales` (`saleID`) ON DELETE CASCADE,
  CONSTRAINT `fk_dosaleitem_serial` FOREIGN KEY (`serialID`) REFERENCES `product_serials` (`serialID`) ON DELETE RESTRICT,
  CONSTRAINT `fk_dosaleitem_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Payment history table (optional - for tracking payments over time)
CREATE TABLE IF NOT EXISTS `do_sale_payments` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `saleID` int(11) NOT NULL,
  `paymentAmount` decimal(10,2) NOT NULL,
  `paymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paymentMethod` varchar(50) DEFAULT 'cash',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`paymentID`),
  KEY `saleID` (`saleID`),
  CONSTRAINT `fk_dosale_payment` FOREIGN KEY (`saleID`) REFERENCES `do_sales` (`saleID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

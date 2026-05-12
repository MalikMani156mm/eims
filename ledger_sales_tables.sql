-- Create Ledger Sales tables

-- Main ledger sales table
CREATE TABLE IF NOT EXISTS `ledger_sales` (
  `saleID` int(11) NOT NULL AUTO_INCREMENT,
  `ledgerName` varchar(255) NOT NULL COMMENT 'Ledger name (entered at runtime)',
  `ledgerCNIC` varchar(20) DEFAULT NULL,
  `ledgerContact` varchar(20) DEFAULT NULL,
  `ledgerAddress` text DEFAULT NULL,
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
  KEY `ledgerName` (`ledgerName`),
  KEY `saleDate` (`saleDate`),
  KEY `paymentStatus` (`paymentStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ledger sale items table - individual products sold
CREATE TABLE IF NOT EXISTS `ledger_sale_items` (
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
  CONSTRAINT `fk_ledgersaleitem_sale` FOREIGN KEY (`saleID`) REFERENCES `ledger_sales` (`saleID`) ON DELETE CASCADE,
  CONSTRAINT `fk_ledgersaleitem_serial` FOREIGN KEY (`serialID`) REFERENCES `product_serials` (`serialID`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ledgersaleitem_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ledger payment history table
CREATE TABLE IF NOT EXISTS `ledger_sale_payments` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `saleID` int(11) NOT NULL,
  `paymentAmount` decimal(10,2) NOT NULL,
  `paymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paymentMethod` varchar(50) DEFAULT 'cash',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`paymentID`),
  KEY `saleID` (`saleID`),
  CONSTRAINT `fk_ledgersale_payment` FOREIGN KEY (`saleID`) REFERENCES `ledger_sales` (`saleID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

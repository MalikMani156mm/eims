-- Create products table
CREATE TABLE IF NOT EXISTS `products` (
  `productID` int(11) NOT NULL AUTO_INCREMENT,
  `productName` varchar(255) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `colorID` int(11) NOT NULL,
  `modelID` int(11) NOT NULL,
  `sizeID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `regionID` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`productID`),
  KEY `fk_product_category` (`categoryID`),
  KEY `fk_product_color` (`colorID`),
  KEY `fk_product_model` (`modelID`),
  KEY `fk_product_size` (`sizeID`),
  KEY `fk_product_region` (`regionID`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`categoriesID`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_color` FOREIGN KEY (`colorID`) REFERENCES `colors` (`colorID`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_model` FOREIGN KEY (`modelID`) REFERENCES `models` (`modelID`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_size` FOREIGN KEY (`sizeID`) REFERENCES `sizes` (`sizeID`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_region` FOREIGN KEY (`regionID`) REFERENCES `regions` (`regionID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create product_barcodes table
CREATE TABLE IF NOT EXISTS `product_barcodes` (
  `barcodeID` int(11) NOT NULL AUTO_INCREMENT,
  `productID` int(11) NOT NULL,
  `barcode_value` varchar(255) NOT NULL,
  `status` enum('available','issued','damaged','returned') NOT NULL DEFAULT 'available',
  `issuedID` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `issuedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`barcodeID`),
  UNIQUE KEY `unique_barcode` (`barcode_value`),
  KEY `fk_barcode_product` (`productID`),
  CONSTRAINT `fk_barcode_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

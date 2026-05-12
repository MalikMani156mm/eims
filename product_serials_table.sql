-- Table structure for table `product_serials`

CREATE TABLE IF NOT EXISTS `product_serials` (
  `serialID` int(11) NOT NULL AUTO_INCREMENT,
  `productID` int(11) NOT NULL,
  `serialNumber` varchar(255) NOT NULL,
  `status` enum('available','issued','damaged') NOT NULL DEFAULT 'available',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`serialID`),
  UNIQUE KEY `unique_serial` (`serialNumber`),
  KEY `fk_serial_product` (`productID`),
  CONSTRAINT `fk_serial_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

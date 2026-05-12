-- Table structure for table `product_batch`

CREATE TABLE IF NOT EXISTS `product_batch` (
  `batchID` int(11) NOT NULL AUTO_INCREMENT,
  `productID` int(11) NOT NULL,
  `batchNumber` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`batchID`),
  KEY `fk_batch_product` (`productID`),
  CONSTRAINT `fk_batch_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

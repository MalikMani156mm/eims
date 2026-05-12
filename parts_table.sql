-- Create Parts table
CREATE TABLE IF NOT EXISTS `parts` (
  `partID` int(11) NOT NULL AUTO_INCREMENT,
  `partName` varchar(255) NOT NULL,
  `serialNumber` varchar(100) DEFAULT NULL,
  `regionID` int(11) DEFAULT 4,
  `batchName` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('available','used') NOT NULL DEFAULT 'available',
  `issuedToSerialNumber` varchar(100) DEFAULT NULL,
  `issuedDate` datetime DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`partID`),
  KEY `partName_idx` (`partName`),
  KEY `regionID` (`regionID`),
  KEY `status` (`status`),
  KEY `batchName` (`batchName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

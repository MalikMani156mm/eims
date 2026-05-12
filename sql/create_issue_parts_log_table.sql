-- Create audit table for issued parts
CREATE TABLE IF NOT EXISTS `issue_parts_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `partName` VARCHAR(255) NOT NULL,
  `regionID` INT(11) NOT NULL,
  `batchName` VARCHAR(100) DEFAULT NULL,
  `serialNumber` VARCHAR(100) DEFAULT NULL,
  `quantityIssued` INT(11) NOT NULL DEFAULT 0,
  `issuedTo` VARCHAR(255) DEFAULT NULL,
  `issuedBy` INT(11) DEFAULT NULL,
  `issuedByName` VARCHAR(100) DEFAULT NULL,
  `issuedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `part_idx` (`partName`),
  KEY `region_idx` (`regionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

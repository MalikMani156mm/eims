-- Create types lookup table
CREATE TABLE IF NOT EXISTS types (
  typeID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  typeName VARCHAR(255) NOT NULL UNIQUE,
  status TINYINT(1) NOT NULL DEFAULT 1,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO types (typeID, typeName, status) VALUES
  (1, 'Indoor', 1),
  (2, 'Outdoor', 1);

-- Add typeID to parts (after sizeID)
ALTER TABLE parts ADD COLUMN typeID INT NOT NULL DEFAULT 1 AFTER sizeID;
ALTER TABLE parts ADD INDEX idx_parts_typeID (typeID);

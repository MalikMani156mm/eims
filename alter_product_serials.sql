-- Add batchNumber column to product_serials table

ALTER TABLE `product_serials` 
ADD COLUMN `batchNumber` varchar(100) NOT NULL AFTER `serialNumber`;

-- Add index for batch number for faster queries
ALTER TABLE `product_serials`
ADD INDEX `idx_batch_number` (`batchNumber`);

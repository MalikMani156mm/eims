<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = intval($_POST['productID'] ?? 0);
    $batchNumber = trim($_POST['batchNumber'] ?? '');
    $serialNumbersJSON = $_POST['serialNumbers'] ?? '';
    $partsDataJSON = $_POST['partsData'] ?? '{}';
    $gasDataJSON = $_POST['gasData'] ?? '[]';
    
    // Validation
    if ($productID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    if (empty($batchNumber)) {
        echo json_encode(['success' => false, 'message' => 'Batch number is required']);
        exit;
    }
    
    if (empty($serialNumbersJSON)) {
        echo json_encode(['success' => false, 'message' => 'No serial numbers provided']);
        exit;
    }
    
    $serialNumbers = json_decode($serialNumbersJSON, true);
    $partsData = json_decode($partsDataJSON, true);
    $gasData = json_decode($gasDataJSON, true);
    
    if (!is_array($serialNumbers) || empty($serialNumbers)) {
        echo json_encode(['success' => false, 'message' => 'Invalid serial numbers format']);
        exit;
    }

    $productBrandID = 0;
    $productStmt = $conn->prepare("SELECT brandID FROM products WHERE productID = ? LIMIT 1");
    $productStmt->bind_param("i", $productID);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    if ($productRow = $productResult->fetch_assoc()) {
        $productBrandID = intval($productRow['brandID']);
    }
    $productStmt->close();

    if ($productBrandID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Product brand could not be found']);
        exit;
    }
    
    // Normalize and validate serial numbers before any database changes
    $normalizedSerials = [];
    foreach ($serialNumbers as $serialNumber) {
        $serialNumber = trim((string) $serialNumber);
        if ($serialNumber === '') {
            continue;
        }
        $normalizedSerials[] = $serialNumber;
    }

    if (empty($normalizedSerials)) {
        echo json_encode(['success' => false, 'message' => 'No valid serial numbers provided']);
        exit;
    }

    if (count(array_unique($normalizedSerials)) !== count($normalizedSerials)) {
        echo json_encode(['success' => false, 'message' => 'Duplicate serial numbers in your list. Each must be unique.']);
        exit;
    }

    $dupCheckStmt = $conn->prepare("SELECT serialNumber FROM product_serials WHERE serialNumber = ? LIMIT 1");
    if (!$dupCheckStmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    foreach ($normalizedSerials as $serialNumber) {
        $dupCheckStmt->bind_param('s', $serialNumber);
        $dupCheckStmt->execute();
        $dupCheckStmt->store_result();
        if ($dupCheckStmt->num_rows > 0) {
            $dupCheckStmt->close();
            echo json_encode([
                'success' => false,
                'message' => 'Serial number already exists in the database: ' . $serialNumber
            ]);
            exit;
        }
    }
    $dupCheckStmt->close();

    // Begin transaction — nothing is written until duplicate checks pass
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO product_serials (productID, serialNumber, batchNumber, status) VALUES (?, ?, ?, 'available')");
        $productSerial = null;
        
        foreach ($normalizedSerials as $serialNumber) {
            if ($productSerial === null) {
                $productSerial = $serialNumber;
            }
            
            $stmt->bind_param("iss", $productID, $serialNumber, $batchNumber);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to save serial number: ' . $serialNumber);
            }
        }
        
        $stmt->close();
        
        // Handle parts with serial numbers
        if (!empty($partsData['withSerial'])) {
            $updateStmt = $conn->prepare("UPDATE parts SET status = 'used', used = 1, issuedToSerialNumber = ?, issuedDate = NOW() WHERE partID = ?");
            
            foreach ($partsData['withSerial'] as $part) {
                $partID = intval($part['partID']);
                $updateStmt->bind_param("si", $productSerial, $partID);
                $updateStmt->execute();
            }
            
            $updateStmt->close();
        }
        
        // Handle parts without serial numbers. Production may still contain
        // aggregate rows where one row represents multiple no-serial items, so
        // consume only the requested quantity instead of marking the whole row.
        if (!empty($partsData['withoutSerial'])) {
            foreach ($partsData['withoutSerial'] as $part) {
                $partName = $part['partName'];
                $sizeID = intval($part['sizeID'] ?? 0);
                $typeID = intval($part['typeID'] ?? 0);
                $quantity = intval($part['quantity']);

                if ($quantity <= 0) {
                    continue;
                }

                if ($sizeID > 0 && $typeID > 0) {
                    $getStmt = $conn->prepare("
                        SELECT partID, regionID, batchName, brandID, sizeID, typeID, quantity, used
                        FROM parts
                        WHERE partName = ?
                          AND brandID = ?
                          AND sizeID = ?
                          AND typeID = ?
                          AND serialNumber IS NULL
                          AND status = 'available'
                        ORDER BY createdAt ASC, partID ASC
                        FOR UPDATE
                    ");
                    $getStmt->bind_param("siii", $partName, $productBrandID, $sizeID, $typeID);
                } elseif ($sizeID > 0) {
                    $getStmt = $conn->prepare("
                        SELECT partID, regionID, batchName, brandID, sizeID, typeID, quantity, used
                        FROM parts
                        WHERE partName = ?
                          AND brandID = ?
                          AND sizeID = ?
                          AND serialNumber IS NULL
                          AND status = 'available'
                        ORDER BY createdAt ASC, partID ASC
                        FOR UPDATE
                    ");
                    $getStmt->bind_param("sii", $partName, $productBrandID, $sizeID);
                } elseif ($typeID > 0) {
                    $getStmt = $conn->prepare("
                        SELECT partID, regionID, batchName, brandID, sizeID, typeID, quantity, used
                        FROM parts
                        WHERE partName = ?
                          AND brandID = ?
                          AND typeID = ?
                          AND serialNumber IS NULL
                          AND status = 'available'
                        ORDER BY createdAt ASC, partID ASC
                        FOR UPDATE
                    ");
                    $getStmt->bind_param("sii", $partName, $productBrandID, $typeID);
                } else {
                    $getStmt = $conn->prepare("
                        SELECT partID, regionID, batchName, brandID, sizeID, typeID, quantity, used
                        FROM parts
                        WHERE partName = ?
                          AND brandID = ?
                          AND serialNumber IS NULL
                          AND status = 'available'
                        ORDER BY createdAt ASC, partID ASC
                        FOR UPDATE
                    ");
                    $getStmt->bind_param("si", $partName, $productBrandID);
                }
                $getStmt->execute();
                $result = $getStmt->get_result();

                $rows = [];
                $availableQuantity = 0;
                while ($row = $result->fetch_assoc()) {
                    $rowQuantity = intval($row['quantity']);
                    if ($rowQuantity <= 0) {
                        $rowQuantity = 1;
                    }

                    $row['availableQuantity'] = $rowQuantity;
                    $availableQuantity += $rowQuantity;
                    $rows[] = $row;
                }
                $getStmt->close();

                if ($availableQuantity < $quantity) {
                    throw new Exception("Insufficient available quantity for part: $partName");
                }

                $remaining = $quantity;
                $updateAvailableStmt = $conn->prepare("UPDATE parts SET quantity = ?, updatedAt = NOW() WHERE partID = ?");
                $updateUsedStmt = $conn->prepare("UPDATE parts SET quantity = ?, used = ?, status = 'used', issuedToSerialNumber = ?, issuedDate = NOW(), updatedAt = NOW() WHERE partID = ?");
                $insertUsedStmt = $conn->prepare("INSERT INTO parts (partName, serialNumber, regionID, batchName, brandID, sizeID, typeID, quantity, used, status, issuedToSerialNumber, issuedDate, createdAt, updatedAt) VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, 'used', ?, NOW(), NOW(), NOW())");

                foreach ($rows as $row) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $partID = intval($row['partID']);
                    $rowQuantity = intval($row['availableQuantity']);
                    $useNow = min($rowQuantity, $remaining);
                    $newAvailableQuantity = $rowQuantity - $useNow;
                    $newUsedQuantity = intval($row['used']) + $useNow;

                    if ($newAvailableQuantity > 0) {
                        $updateAvailableStmt->bind_param("ii", $newAvailableQuantity, $partID);
                        if (!$updateAvailableStmt->execute()) {
                            throw new Exception("Failed to update available quantity for part: $partName");
                        }

                        $rowRegionID = intval($row['regionID']);
                        $rowBatchName = $row['batchName'];
                        $rowBrandID = intval($row['brandID']);
                        $rowSizeID = intval($row['sizeID']);
                        $rowTypeID = intval($row['typeID']);
                        $insertUsedStmt->bind_param("sisiiiiis", $partName, $rowRegionID, $rowBatchName, $rowBrandID, $rowSizeID, $rowTypeID, $useNow, $useNow, $productSerial);
                        if (!$insertUsedStmt->execute()) {
                            throw new Exception("Failed to log used quantity for part: $partName");
                        }
                    } else {
                        $updateUsedStmt->bind_param("iisi", $useNow, $newUsedQuantity, $productSerial, $partID);
                        if (!$updateUsedStmt->execute()) {
                            throw new Exception("Failed to mark part as used: $partName");
                        }
                    }

                    $remaining -= $useNow;
                }

                $updateAvailableStmt->close();
                $updateUsedStmt->close();
                $insertUsedStmt->close();
            }
        }

        // Handle gas allocation
        if (!empty($gasData) && is_array($gasData)) {
            $gasStmt = $conn->prepare("UPDATE gas_batch_details SET available = available - ? WHERE batch_id = ? AND available >= ?");
            $gasLogStmt = $conn->prepare("INSERT INTO gas_logs (product_id, serial_number, gas_id, batch_id, quantity_used, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($gasData as $gas) {
                $batchId = intval($gas['batch_id']);
                $gasId = intval($gas['gas_id']);
                $quantity = floatval($gas['quantity']);
                $unitPrice = floatval($gas['unit_price']);
                $totalPrice = floatval($gas['total_price']);
                
                // Update available quantity in gas_batch_details
                $gasStmt->bind_param("dii", $quantity, $batchId, $quantity);
                $gasStmt->execute();
                
                // Insert into gas_logs - bind all 7 parameters correctly
                $gasLogStmt->bind_param("isiiddd", $productID, $productSerial, $gasId, $batchId, $quantity, $unitPrice, $totalPrice);
                $gasLogStmt->execute();
            }
            
            $gasStmt->close();
            $gasLogStmt->close();
        }
        
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => count($normalizedSerials) . " serial number(s) saved for batch $batchNumber and parts/gases issued"
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

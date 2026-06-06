<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data format']);
        exit;
    }

    // Validation
    $partName = trim($data['partName'] ?? '');
    $batchName = trim($data['batchName'] ?? '');
    $brandID = intval($data['brandID'] ?? 0);
    $quantity = intval($data['quantity'] ?? 0);
    $serialNumbers = $data['serialNumbers'] ?? [];

    if (empty($partName)) {
        echo json_encode(['success' => false, 'message' => 'Part name is required']);
        exit;
    }

    if (empty($batchName)) {
        echo json_encode(['success' => false, 'message' => 'Batch name is required']);
        exit;
    }

    if ($brandID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Brand is required']);
        exit;
    }

    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
        exit;
    }

    // If not using noSerial flow, ensure serial count matches quantity
    if (empty($data['noSerial'])) {
        if (count($serialNumbers) !== $quantity) {
            echo json_encode(['success' => false, 'message' => 'Serial number count does not match quantity']);
            exit;
        }
    }

    try {
        // Default region and status
        $regionID = 4; // Default region
        $status = 'available';

        // Handle no-serials flow: create separate records for each item (each with quantity = 1, serialNumber = NULL)
        if (!empty($data['noSerial'])) {
            // Begin transaction
            $conn->begin_transaction();

            // Prepare insert statement for no-serial records
            $ins = $conn->prepare("INSERT INTO parts (partName, serialNumber, regionID, batchName, brandID, quantity, status, createdAt, updatedAt) VALUES (?, NULL, ?, ?, ?, 1, ?, NOW(), NOW())");
            if (!$ins) throw new Exception('Prepare failed: ' . $conn->error);

            $insertCount = 0;
            $status = 'available';

            // Create a separate record for each item with quantity = 1
            for ($i = 0; $i < $quantity; $i++) {
                $ins->bind_param('sisis', $partName, $regionID, $batchName, $brandID, $status);
                if (!$ins->execute()) throw new Exception('Insert failed: ' . $ins->error);
                $insertCount++;
            }

            $ins->close();
            $conn->commit();
            echo json_encode(['success' => true, 'message' => "Successfully added $insertCount separate record(s) without serial numbers"]);
            $conn->close();
            exit;
        }

        // Note: allow multiple rows for same partName (each serial stored separately)
        // We'll check per-serial duplicates below before inserting

        // Begin transaction
        $conn->begin_transaction();

        $insertCount = 0;

        // Prepare two insert statements: one for serial provided, one for NULL serial
        $stmtWithSerial = $conn->prepare("INSERT INTO parts (partName, serialNumber, regionID, batchName, brandID, quantity, status, createdAt, updatedAt) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmtWithNull = $conn->prepare("INSERT INTO parts (partName, serialNumber, regionID, batchName, brandID, quantity, status, createdAt, updatedAt) 
                                VALUES (?, NULL, ?, ?, ?, ?, ?, NOW(), NOW())");

        if (!$stmtWithSerial || !$stmtWithNull) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        foreach ($serialNumbers as $serialNumber) {
            $serialNumber = trim($serialNumber);

            // Determine duplicate check depending on whether serial is provided
            if ($serialNumber === '') {
                // Check for existing NULL serial for this part
                // Check for existing NULL serial for this part and brand
                $dupCheck = $conn->prepare("SELECT partID FROM parts WHERE partName = ? AND brandID = ? AND serialNumber IS NULL LIMIT 1");
                if (!$dupCheck) {
                    throw new Exception('Prepare failed (dup check): ' . $conn->error);
                }
                $dupCheck->bind_param('si', $partName, $brandID);
            } else {
                // Check if this exact serial for this part already exists
                $dupCheck = $conn->prepare("SELECT partID FROM parts WHERE partName = ? AND brandID = ? AND serialNumber = ? LIMIT 1");
                if (!$dupCheck) {
                    throw new Exception('Prepare failed (dup check): ' . $conn->error);
                }
                $dupCheck->bind_param('sis', $partName, $brandID, $serialNumber);
            }

            $dupCheck->execute();
            $dupCheck->store_result();
            if ($dupCheck->num_rows > 0) {
                $dupCheck->close();
                throw new Exception('Serial number "' . ($serialNumber === '' ? 'NULL' : $serialNumber) . '" already exists for this part');
            }
            $dupCheck->close();

            // quantity stored as 1 for each serial record
            $oneQuantity = 1;

            if ($serialNumber === '') {
                // Insert with NULL serial
                $stmtWithNull->bind_param(
                    "sisiis",
                    $partName,
                    $regionID,
                    $batchName,
                    $brandID,
                    $oneQuantity,
                    $status
                );

                if (!$stmtWithNull->execute()) {
                    throw new Exception('Failed to insert part (null serial): ' . $stmtWithNull->error);
                }
            } else {
                // Insert with provided serial
                $stmtWithSerial->bind_param(
                    "ssisiis",
                    $partName,
                    $serialNumber,
                    $regionID,
                    $batchName,
                    $brandID,
                    $oneQuantity,
                    $status
                );

                if (!$stmtWithSerial->execute()) {
                    throw new Exception('Failed to insert part: ' . $stmtWithSerial->error);
                }
            }

            $insertCount++;
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => "Successfully added $insertCount part(s) with serial numbers"
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

$conn->close();
?>

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

    $partName = trim($data['partName'] ?? '');
    $batchName = trim($data['batchName'] ?? '');
    $brandID = intval($data['brandID'] ?? 0);
    $sizeID = intval($data['sizeID'] ?? 1);
    $typeID = intval($data['typeID'] ?? 1);
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

    if ($sizeID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Size is required']);
        exit;
    }

    if ($typeID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Type is required']);
        exit;
    }

    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
        exit;
    }

    if (empty($data['noSerial'])) {
        if (count($serialNumbers) !== $quantity) {
            echo json_encode(['success' => false, 'message' => 'Serial number count does not match quantity']);
            exit;
        }
    }

    try {
        $regionID = 4;
        $status = 'available';

        if (!empty($data['noSerial'])) {
            $conn->begin_transaction();

            $ins = $conn->prepare("INSERT INTO parts (partName, serialNumber, regionID, batchName, brandID, sizeID, typeID, quantity, status, createdAt, updatedAt) VALUES (?, NULL, ?, ?, ?, ?, ?, 1, ?, NOW(), NOW())");
            if (!$ins) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }

            $insertCount = 0;
            for ($i = 0; $i < $quantity; $i++) {
                $ins->bind_param('sisiiis', $partName, $regionID, $batchName, $brandID, $sizeID, $typeID, $status);
                if (!$ins->execute()) {
                    throw new Exception('Insert failed: ' . $ins->error);
                }
                $insertCount++;
            }

            $ins->close();
            $conn->commit();
            echo json_encode(['success' => true, 'message' => "Successfully added $insertCount separate record(s) without serial numbers"]);
            $conn->close();
            exit;
        }

        $conn->begin_transaction();
        $insertCount = 0;

        $stmtWithSerial = $conn->prepare("INSERT INTO parts (partName, serialNumber, regionID, batchName, brandID, sizeID, typeID, quantity, status, createdAt, updatedAt)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmtWithNull = $conn->prepare("INSERT INTO parts (partName, serialNumber, regionID, batchName, brandID, sizeID, typeID, quantity, status, createdAt, updatedAt)
                                VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

        if (!$stmtWithSerial || !$stmtWithNull) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        foreach ($serialNumbers as $serialNumber) {
            $serialNumber = trim($serialNumber);

            if ($serialNumber === '') {
                $dupCheck = $conn->prepare("SELECT partID FROM parts WHERE partName = ? AND brandID = ? AND sizeID = ? AND typeID = ? AND serialNumber IS NULL LIMIT 1");
                if (!$dupCheck) {
                    throw new Exception('Prepare failed (dup check): ' . $conn->error);
                }
                $dupCheck->bind_param('siii', $partName, $brandID, $sizeID, $typeID);
            } else {
                $dupCheck = $conn->prepare("SELECT partID FROM parts WHERE partName = ? AND brandID = ? AND sizeID = ? AND typeID = ? AND serialNumber = ? LIMIT 1");
                if (!$dupCheck) {
                    throw new Exception('Prepare failed (dup check): ' . $conn->error);
                }
                $dupCheck->bind_param('siiis', $partName, $brandID, $sizeID, $typeID, $serialNumber);
            }

            $dupCheck->execute();
            $dupCheck->store_result();
            if ($dupCheck->num_rows > 0) {
                $dupCheck->close();
                throw new Exception('Serial number "' . ($serialNumber === '' ? 'NULL' : $serialNumber) . '" already exists for this part');
            }
            $dupCheck->close();

            $oneQuantity = 1;

            if ($serialNumber === '') {
                $stmtWithNull->bind_param(
                    'sisiiiis',
                    $partName,
                    $regionID,
                    $batchName,
                    $brandID,
                    $sizeID,
                    $typeID,
                    $oneQuantity,
                    $status
                );

                if (!$stmtWithNull->execute()) {
                    throw new Exception('Failed to insert part (null serial): ' . $stmtWithNull->error);
                }
            } else {
                $stmtWithSerial->bind_param(
                    'ssisiiiis',
                    $partName,
                    $serialNumber,
                    $regionID,
                    $batchName,
                    $brandID,
                    $sizeID,
                    $typeID,
                    $oneQuantity,
                    $status
                );

                if (!$stmtWithSerial->execute()) {
                    throw new Exception('Failed to insert part: ' . $stmtWithSerial->error);
                }
            }

            $insertCount++;
        }

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

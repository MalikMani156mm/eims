<?php

/**
 * Restore a no-serial used part row back to available stock.
 */
function restoreNoSerialPart($conn, array $usedPart): void
{
    $partID = intval($usedPart['partID']);
    $partName = $usedPart['partName'];
    $brandID = intval($usedPart['brandID']);
    $regionID = intval($usedPart['regionID']);
    $batchName = $usedPart['batchName'];
    $sizeID = intval($usedPart['sizeID'] ?? 0);
    $typeID = intval($usedPart['typeID'] ?? 0);
    $qty = intval($usedPart['quantity']);
    if ($qty <= 0) {
        $qty = 1;
    }

    if ($sizeID > 0 && $typeID > 0) {
        $findStmt = $conn->prepare("
            SELECT partID, quantity
            FROM parts
            WHERE partName = ?
              AND brandID = ?
              AND regionID = ?
              AND batchName = ?
              AND sizeID = ?
              AND typeID = ?
              AND serialNumber IS NULL
              AND status = 'available'
              AND partID != ?
            ORDER BY partID ASC
            LIMIT 1
            FOR UPDATE
        ");
        $findStmt->bind_param('siisiii', $partName, $brandID, $regionID, $batchName, $sizeID, $typeID, $partID);
    } elseif ($sizeID > 0) {
        $findStmt = $conn->prepare("
            SELECT partID, quantity
            FROM parts
            WHERE partName = ?
              AND brandID = ?
              AND regionID = ?
              AND batchName = ?
              AND sizeID = ?
              AND serialNumber IS NULL
              AND status = 'available'
              AND partID != ?
            ORDER BY partID ASC
            LIMIT 1
            FOR UPDATE
        ");
        $findStmt->bind_param('siisii', $partName, $brandID, $regionID, $batchName, $sizeID, $partID);
    } elseif ($typeID > 0) {
        $findStmt = $conn->prepare("
            SELECT partID, quantity
            FROM parts
            WHERE partName = ?
              AND brandID = ?
              AND regionID = ?
              AND batchName = ?
              AND typeID = ?
              AND serialNumber IS NULL
              AND status = 'available'
              AND partID != ?
            ORDER BY partID ASC
            LIMIT 1
            FOR UPDATE
        ");
        $findStmt->bind_param('siisii', $partName, $brandID, $regionID, $batchName, $typeID, $partID);
    } else {
        $findStmt = $conn->prepare("
            SELECT partID, quantity
            FROM parts
            WHERE partName = ?
              AND brandID = ?
              AND regionID = ?
              AND batchName = ?
              AND serialNumber IS NULL
              AND status = 'available'
              AND partID != ?
            ORDER BY partID ASC
            LIMIT 1
            FOR UPDATE
        ");
        $findStmt->bind_param('siisi', $partName, $brandID, $regionID, $batchName, $partID);
    }

    $findStmt->execute();
    $result = $findStmt->get_result();
    $availableRow = $result->fetch_assoc();
    $findStmt->close();

    if ($availableRow) {
        $newQty = intval($availableRow['quantity']) + $qty;
        $updateStmt = $conn->prepare("UPDATE parts SET quantity = ?, updatedAt = NOW() WHERE partID = ?");
        $availablePartID = intval($availableRow['partID']);
        $updateStmt->bind_param('ii', $newQty, $availablePartID);
        if (!$updateStmt->execute()) {
            $updateStmt->close();
            throw new Exception('Failed to restore part quantity for: ' . $partName);
        }
        $updateStmt->close();

        $deleteStmt = $conn->prepare("DELETE FROM parts WHERE partID = ?");
        $deleteStmt->bind_param('i', $partID);
        if (!$deleteStmt->execute()) {
            $deleteStmt->close();
            throw new Exception('Failed to remove used part row for: ' . $partName);
        }
        $deleteStmt->close();
        return;
    }

    $restoreStmt = $conn->prepare("
        UPDATE parts
        SET status = 'available',
            used = 0,
            issuedToSerialNumber = NULL,
            issuedDate = NULL,
            updatedAt = NOW()
        WHERE partID = ?
    ");
    $restoreStmt->bind_param('i', $partID);
    if (!$restoreStmt->execute()) {
        $restoreStmt->close();
        throw new Exception('Failed to restore part: ' . $partName);
    }
    $restoreStmt->close();
}

/**
 * Restore parts and gas consumed for a product, and remove related serial/gas log rows.
 */
function restoreProductInventory($conn, int $productID): void
{
    $serialStmt = $conn->prepare("SELECT serialNumber FROM product_serials WHERE productID = ?");
    $serialStmt->bind_param('i', $productID);
    $serialStmt->execute();
    $serialResult = $serialStmt->get_result();
    $serials = [];
    while ($row = $serialResult->fetch_assoc()) {
        $serials[] = $row['serialNumber'];
    }
    $serialStmt->close();

    $gasStmt = $conn->prepare("SELECT gas_log_id, batch_id, quantity_used FROM gas_logs WHERE product_id = ? FOR UPDATE");
    $gasStmt->bind_param('i', $productID);
    $gasStmt->execute();
    $gasResult = $gasStmt->get_result();
    $gasLogs = [];
    $seenGasLogIds = [];
    while ($row = $gasResult->fetch_assoc()) {
        $logId = intval($row['gas_log_id']);
        if (!isset($seenGasLogIds[$logId])) {
            $seenGasLogIds[$logId] = true;
            $gasLogs[] = $row;
        }
    }
    $gasStmt->close();

    if (!empty($serials)) {
        $placeholders = implode(',', array_fill(0, count($serials), '?'));
        $types = str_repeat('s', count($serials));

        $extraGasSql = "SELECT gas_log_id, batch_id, quantity_used FROM gas_logs WHERE serial_number IN ($placeholders) AND product_id != ?";
        $extraGasStmt = $conn->prepare($extraGasSql);
        $bindTypes = $types . 'i';
        $bindValues = array_merge($serials, [$productID]);
        $extraGasStmt->bind_param($bindTypes, ...$bindValues);
        $extraGasStmt->execute();
        $extraGasResult = $extraGasStmt->get_result();
        while ($row = $extraGasResult->fetch_assoc()) {
            $logId = intval($row['gas_log_id']);
            if (!isset($seenGasLogIds[$logId])) {
                $seenGasLogIds[$logId] = true;
                $gasLogs[] = $row;
            }
        }
        $extraGasStmt->close();
    }

    $restoreGasStmt = $conn->prepare("UPDATE gas_batch_details SET available = available + ? WHERE batch_id = ?");
    foreach ($gasLogs as $log) {
        $quantityUsed = floatval($log['quantity_used']);
        $batchId = intval($log['batch_id']);
        if ($quantityUsed <= 0 || $batchId <= 0) {
            continue;
        }
        $restoreGasStmt->bind_param('di', $quantityUsed, $batchId);
        if (!$restoreGasStmt->execute()) {
            $restoreGasStmt->close();
            throw new Exception('Failed to restore gas stock');
        }
    }
    $restoreGasStmt->close();

    $deleteGasStmt = $conn->prepare("DELETE FROM gas_logs WHERE product_id = ?");
    $deleteGasStmt->bind_param('i', $productID);
    if (!$deleteGasStmt->execute()) {
        $deleteGasStmt->close();
        throw new Exception('Failed to remove gas logs');
    }
    $deleteGasStmt->close();

    if (!empty($serials)) {
        $placeholders = implode(',', array_fill(0, count($serials), '?'));
        $types = str_repeat('s', count($serials));

        $deleteExtraGasStmt = $conn->prepare("DELETE FROM gas_logs WHERE serial_number IN ($placeholders)");
        $deleteExtraGasStmt->bind_param($types, ...$serials);
        $deleteExtraGasStmt->execute();
        $deleteExtraGasStmt->close();

        $partsSql = "
            SELECT partID, partName, serialNumber, batchName, quantity, brandID, regionID, sizeID, typeID, status
            FROM parts
            WHERE issuedToSerialNumber IN ($placeholders)
            FOR UPDATE
        ";
        $partsStmt = $conn->prepare($partsSql);
        $partsStmt->bind_param($types, ...$serials);
        $partsStmt->execute();
        $partsResult = $partsStmt->get_result();
        $issuedParts = [];
        while ($row = $partsResult->fetch_assoc()) {
            $issuedParts[] = $row;
        }
        $partsStmt->close();

        $restoreSerialPartStmt = $conn->prepare("
            UPDATE parts
            SET status = 'available',
                used = 0,
                issuedToSerialNumber = NULL,
                issuedDate = NULL,
                updatedAt = NOW()
            WHERE partID = ?
        ");

        foreach ($issuedParts as $part) {
            if (!empty($part['serialNumber'])) {
                $partID = intval($part['partID']);
                $restoreSerialPartStmt->bind_param('i', $partID);
                if (!$restoreSerialPartStmt->execute()) {
                    $restoreSerialPartStmt->close();
                    throw new Exception('Failed to restore part: ' . $part['partName']);
                }
            } else {
                restoreNoSerialPart($conn, $part);
            }
        }
        $restoreSerialPartStmt->close();
    }

    $deleteSerialsStmt = $conn->prepare("DELETE FROM product_serials WHERE productID = ?");
    $deleteSerialsStmt->bind_param('i', $productID);
    if (!$deleteSerialsStmt->execute()) {
        $deleteSerialsStmt->close();
        throw new Exception('Failed to remove product serial numbers');
    }
    $deleteSerialsStmt->close();
}

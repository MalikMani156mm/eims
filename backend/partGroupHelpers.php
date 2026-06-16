<?php

function buildPartDisplayLabel(string $partName, string $sizeName = '', string $typeName = ''): string
{
    $meta = [];
    $sizeName = trim($sizeName);
    $typeName = trim($typeName);

    if ($sizeName !== '' && $sizeName !== 'N/A') {
        $meta[] = $sizeName;
    }
    if ($typeName !== '' && $typeName !== 'N/A') {
        $meta[] = $typeName;
    }

    return empty($meta) ? $partName : $partName . ' (' . implode(', ', $meta) . ')';
}

function loadPartsGroupsForBrand($conn, int $brandID): array
{
    $partsWithSerialGroups = [];
    $partsWithoutSerialGroups = [];

    if ($brandID > 0) {
        $groupsStmt = $conn->prepare("
            SELECT DISTINCT p.partName, p.sizeID, p.typeID,
                   COALESCE(s.sizeName, 'N/A') AS sizeName,
                   COALESCE(t.typeName, 'N/A') AS typeName
            FROM parts p
            LEFT JOIN sizes s ON p.sizeID = s.sizeID
            LEFT JOIN types t ON p.typeID = t.typeID
            WHERE p.brandID = ?
            ORDER BY p.partName ASC, s.sizeName ASC, t.typeName ASC
        ");
        $groupsStmt->bind_param('i', $brandID);
    } else {
        $groupsStmt = $conn->prepare("
            SELECT DISTINCT p.partName, p.sizeID, p.typeID,
                   COALESCE(s.sizeName, 'N/A') AS sizeName,
                   COALESCE(t.typeName, 'N/A') AS typeName
            FROM parts p
            LEFT JOIN sizes s ON p.sizeID = s.sizeID
            LEFT JOIN types t ON p.typeID = t.typeID
            ORDER BY p.partName ASC, s.sizeName ASC, t.typeName ASC
        ");
    }

    $groupsStmt->execute();
    $groupsRes = $groupsStmt->get_result();

    while ($row = $groupsRes->fetch_assoc()) {
        $partName = $row['partName'];
        $sizeID = (int)$row['sizeID'];
        $typeID = (int)$row['typeID'];
        $sizeName = trim($row['sizeName']);
        $typeName = trim($row['typeName']);
        $label = buildPartDisplayLabel($partName, $sizeName, $typeName);

        if ($brandID > 0) {
            $serialCheck = $conn->prepare("SELECT COUNT(*) as count FROM parts WHERE partName = ? AND brandID = ? AND sizeID = ? AND typeID = ? AND serialNumber IS NOT NULL LIMIT 1");
            $serialCheck->bind_param('siii', $partName, $brandID, $sizeID, $typeID);
        } else {
            $serialCheck = $conn->prepare("SELECT COUNT(*) as count FROM parts WHERE partName = ? AND sizeID = ? AND typeID = ? AND serialNumber IS NOT NULL LIMIT 1");
            $serialCheck->bind_param('sii', $partName, $sizeID, $typeID);
        }

        $serialCheck->execute();
        $serialCount = (int)($serialCheck->get_result()->fetch_assoc()['count'] ?? 0);
        $serialCheck->close();

        if ($serialCount > 0) {
            $serialParts = [];
            if ($brandID > 0) {
                $pstmt = $conn->prepare("
                    SELECT partID, partName, serialNumber, batchName, sizeID, typeID
                    FROM parts
                    WHERE partName = ? AND brandID = ? AND sizeID = ? AND typeID = ? AND serialNumber IS NOT NULL AND status = 'available'
                    ORDER BY serialNumber ASC
                ");
                $pstmt->bind_param('siii', $partName, $brandID, $sizeID, $typeID);
            } else {
                $pstmt = $conn->prepare("
                    SELECT partID, partName, serialNumber, batchName, sizeID, typeID
                    FROM parts
                    WHERE partName = ? AND sizeID = ? AND typeID = ? AND serialNumber IS NOT NULL AND status = 'available'
                    ORDER BY serialNumber ASC
                ");
                $pstmt->bind_param('sii', $partName, $sizeID, $typeID);
            }

            $pstmt->execute();
            $pRes = $pstmt->get_result();
            while ($p = $pRes->fetch_assoc()) {
                $serialParts[] = $p;
            }
            $pstmt->close();

            if (!empty($serialParts)) {
                $partsWithSerialGroups[] = [
                    'label' => $label,
                    'partName' => $partName,
                    'sizeID' => $sizeID,
                    'typeID' => $typeID,
                    'sizeName' => $sizeName,
                    'typeName' => $typeName,
                    'items' => $serialParts
                ];
            }
        } else {
            if ($brandID > 0) {
                $noSerialCheck = $conn->prepare("
                    SELECT SUM(CASE WHEN quantity > 0 THEN quantity ELSE 1 END) as count
                    FROM parts
                    WHERE partName = ? AND brandID = ? AND sizeID = ? AND typeID = ? AND serialNumber IS NULL AND status = 'available'
                ");
                $noSerialCheck->bind_param('siii', $partName, $brandID, $sizeID, $typeID);
            } else {
                $noSerialCheck = $conn->prepare("
                    SELECT SUM(CASE WHEN quantity > 0 THEN quantity ELSE 1 END) as count
                    FROM parts
                    WHERE partName = ? AND sizeID = ? AND typeID = ? AND serialNumber IS NULL AND status = 'available'
                ");
                $noSerialCheck->bind_param('sii', $partName, $sizeID, $typeID);
            }

            $noSerialCheck->execute();
            $availableCount = (int)($noSerialCheck->get_result()->fetch_assoc()['count'] ?? 0);
            $noSerialCheck->close();

            if ($availableCount > 0) {
                $partsWithoutSerialGroups[] = [
                    'label' => $label,
                    'partName' => $partName,
                    'sizeID' => $sizeID,
                    'typeID' => $typeID,
                    'sizeName' => $sizeName,
                    'typeName' => $typeName,
                    'availableCount' => $availableCount
                ];
            }
        }
    }

    $groupsStmt->close();

    return [
        'partsWithSerialGroups' => $partsWithSerialGroups,
        'partsWithoutSerialGroups' => $partsWithoutSerialGroups
    ];
}

?>

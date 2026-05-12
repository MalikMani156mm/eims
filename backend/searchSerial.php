<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $serialNumber = trim($_GET['serialNumber'] ?? '');
    
    // Validation
    if (empty($serialNumber)) {
        echo json_encode(['success' => false, 'message' => 'Serial number is required']);
        exit;
    }
    
    try {
        // Search for the serial number with all related product information
        $stmt = $conn->prepare("
            SELECT 
                ps.*,
                p.productName,
                p.cost,
                c.categoryName,
                col.colorName,
                m.modelName,
                s.sizeName,
                r.regionName
            FROM product_serials ps
            INNER JOIN products p ON ps.productID = p.productID
            LEFT JOIN categories c ON p.categoryID = c.categoriesID
            LEFT JOIN colors col ON p.colorID = col.colorID
            LEFT JOIN models m ON p.modelID = m.modelID
            LEFT JOIN sizes s ON p.sizeID = s.sizeID
            LEFT JOIN regions r ON p.regionID = r.regionID
            WHERE ps.serialNumber = ?
            LIMIT 1
        ");
        
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $row['soldToName'] = null;
            $row['soldToType'] = null;
            $row['soldAt'] = null;
            $row['soldPrice'] = null;
            $row['listPrice'] = null;
            $row['discountAmount'] = null;

            if (($row['status'] ?? '') === 'issued') {
                $saleInfoStmt = $conn->prepare(" 
                    SELECT soldToName, soldToType, soldAt, soldPrice, listPrice, discountAmount
                    FROM (
                        SELECT 
                            ls.ledgerName AS soldToName,
                            'Ledger' AS soldToType,
                            lsi.createdAt AS soldAt,
                            lsi.finalPrice AS soldPrice,
                            lsi.sellingPrice AS listPrice,
                            lsi.discountAmount AS discountAmount
                        FROM ledger_sale_items lsi
                        INNER JOIN ledger_sales ls ON lsi.saleID = ls.saleID
                        WHERE lsi.serialID = ?

                        UNION ALL

                        SELECT 
                            do.DO_Name AS soldToName,
                            'Distributing Officer' AS soldToType,
                            dsi.createdAt AS soldAt,
                            dsi.finalPrice AS soldPrice,
                            dsi.sellingPrice AS listPrice,
                            dsi.discountAmount AS discountAmount
                        FROM do_sale_items dsi
                        INNER JOIN do_sales ds ON dsi.saleID = ds.saleID
                        INNER JOIN distributing_officer do ON ds.DO_ID = do.DO_ID
                        WHERE dsi.serialID = ?
                    ) AS sale_history
                    ORDER BY soldAt DESC
                    LIMIT 1
                ");

                if ($saleInfoStmt) {
                    $serialID = intval($row['serialID']);
                    $saleInfoStmt->bind_param("ii", $serialID, $serialID);
                    $saleInfoStmt->execute();
                    $saleInfoResult = $saleInfoStmt->get_result();

                    if ($saleInfo = $saleInfoResult->fetch_assoc()) {
                        $row['soldToName'] = $saleInfo['soldToName'] ?? null;
                        $row['soldToType'] = $saleInfo['soldToType'] ?? null;
                        $row['soldAt'] = $saleInfo['soldAt'] ?? null;
                        $row['soldPrice'] = $saleInfo['soldPrice'] ?? null;
                        $row['listPrice'] = $saleInfo['listPrice'] ?? null;
                        $row['discountAmount'] = $saleInfo['discountAmount'] ?? null;
                    }

                    $saleInfoStmt->close();
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $row
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Serial number not found'
            ]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

$conn->close();
?>

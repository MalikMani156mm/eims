<?php
header('Content-Type: application/json');
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $productID = intval($_GET['productID'] ?? 0);
    
    // Validation
    if ($productID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    try {
        // Fetch all batch logs for this product
        $stmt = $conn->prepare("
            SELECT 
                batchID,
                batchNumber,
                quantity,
                cost,
                createdAt
            FROM product_batch
            WHERE productID = ?
            ORDER BY createdAt DESC
        ");
        $stmt->bind_param("i", $productID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $batches = [];
        $totalQuantity = 0;
        $totalCost = 0;
        
        while ($row = $result->fetch_assoc()) {
            $batches[] = $row;
            $totalQuantity += $row['quantity'];
            $totalCost += ($row['quantity'] * $row['cost']);
        }
        
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'data' => $batches,
            'totals' => [
                'totalQuantity' => $totalQuantity,
                'totalCost' => $totalCost
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

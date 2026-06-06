<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

header('Content-Type: application/json');

try {
    // Get unique ledger names with their latest details and pending amounts
    $query = "
        SELECT 
            ledgerName as name,
            ledgerCNIC as cnic,
            ledgerContact as contact,
            ledgerAddress as address,
            SUM(pendingAmount) as pending
        FROM ledger_sales
        GROUP BY ledgerName, ledgerCNIC, ledgerContact, ledgerAddress
        ORDER BY MAX(saleID) DESC
    ";
    
    $result = $conn->query($query);
    $ledgers = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ledgers[] = [
                'name' => $row['name'],
                'cnic' => $row['cnic'],
                'contact' => $row['contact'],
                'address' => $row['address'],
                'pending' => floatval($row['pending'])
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'ledgers' => $ledgers
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

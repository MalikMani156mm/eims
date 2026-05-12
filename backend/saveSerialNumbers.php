<?php
header('Content-Type: application/json');
require '../adminAuth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = intval($_POST['productID'] ?? 0);
    $batchNumber = trim($_POST['batchNumber'] ?? '');
    $serialNumbersJSON = $_POST['serialNumbers'] ?? '';
    $partsDataJSON = $_POST['partsData'] ?? '{}';
    
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
    
    if (!is_array($serialNumbers) || empty($serialNumbers)) {
        echo json_encode(['success' => false, 'message' => 'Invalid serial numbers format']);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO product_serials (productID, serialNumber, batchNumber, status) VALUES (?, ?, ?, 'available')");
        
        $successCount = 0;
        $failedSerials = [];
        $productSerial = null;
        
        foreach ($serialNumbers as $serialNumber) {
            $serialNumber = trim($serialNumber);
            
            if (empty($serialNumber)) {
                continue;
            }
            
            // Store the first serial as the product serial for issuing parts
            if ($productSerial === null) {
                $productSerial = $serialNumber;
            }
            
            $stmt->bind_param("iss", $productID, $serialNumber, $batchNumber);
            
            if ($stmt->execute()) {
                $successCount++;
            } else {
                $failedSerials[] = $serialNumber;
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
        
        // Handle parts without serial numbers
        if (!empty($partsData['withoutSerial'])) {
            foreach ($partsData['withoutSerial'] as $part) {
                $partName = $part['partName'];
                $quantity = intval($part['quantity']);
                
                // Get the first $quantity available records without serial
                $getStmt = $conn->prepare("SELECT partID FROM parts WHERE partName = ? AND serialNumber IS NULL AND status = 'available' ORDER BY partID ASC LIMIT ?");
                $getStmt->bind_param("si", $partName, $quantity);
                $getStmt->execute();
                $result = $getStmt->get_result();
                
                $partIDs = [];
                while ($row = $result->fetch_assoc()) {
                    $partIDs[] = intval($row['partID']);
                }
                $getStmt->close();
                
                // Update each part record
                foreach ($partIDs as $partID) {
                    $updateStmt = $conn->prepare("UPDATE parts SET status = 'used', used = 1, issuedToSerialNumber = ?, issuedDate = NOW() WHERE partID = ?");
                    $updateStmt->bind_param("si", $productSerial, $partID);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
            }
        }
        
        // Commit if at least some serials succeeded
        if ($successCount > 0) {
            $conn->commit();
            
            if (empty($failedSerials)) {
                echo json_encode([
                    'success' => true, 
                    'message' => "All $successCount serial numbers saved successfully for batch $batchNumber and parts issued"
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => "$successCount serial numbers saved, " . count($failedSerials) . " failed (possibly duplicates), parts issued"
                ]);
            }
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to save any serial numbers']);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
$conn->close();
?>

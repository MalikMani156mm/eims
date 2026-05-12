<?php
require '../db.php';

// Fix all used parts that have quantity=0 to have quantity=1
$sql = "UPDATE parts SET quantity = 1 WHERE status = 'used' AND quantity = 0";

if ($conn->query($sql)) {
    $affectedRows = $conn->affected_rows;
    echo json_encode([
        'success' => true,
        'message' => "Fixed $affectedRows used part records with quantity updated to 1"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $conn->error
    ]);
}

$conn->close();
?>

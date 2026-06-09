<?php

function syncGasMasterFromBatches(mysqli $conn, int $gasId): void
{
    $summaryStmt = $conn->prepare("
        SELECT
            COALESCE(SUM(quantity), 0) AS total_qty,
            COALESCE(SUM(total_price), 0) AS total_amount,
            COALESCE(SUM(paid_price), 0) AS total_paid,
            COALESCE(SUM(pending_price), 0) AS total_pending
        FROM gas_batch_details
        WHERE gas_id = ?
    ");
    $summaryStmt->bind_param('i', $gasId);
    $summaryStmt->execute();
    $summary = $summaryStmt->get_result()->fetch_assoc();
    $summaryStmt->close();

    $totalQty = floatval($summary['total_qty']);
    $totalAmount = floatval($summary['total_amount']);
    $totalPaid = floatval($summary['total_paid']);
    $totalPending = floatval($summary['total_pending']);
    $avgPrice = $totalQty > 0 ? ($totalAmount / $totalQty) : 0;

    $vendorID = 0;
    $vendorStmt = $conn->prepare("
        SELECT vendorID
        FROM gas_batch_details
        WHERE gas_id = ? AND vendorID > 0
        ORDER BY createdAt DESC, batch_id DESC
        LIMIT 1
    ");
    $vendorStmt->bind_param('i', $gasId);
    $vendorStmt->execute();
    $vendorRow = $vendorStmt->get_result()->fetch_assoc();
    if ($vendorRow) {
        $vendorID = intval($vendorRow['vendorID']);
    }
    $vendorStmt->close();

    $updateStmt = $conn->prepare("
        UPDATE gas_master
        SET quantity = ?, unit_price = ?, paid_price = ?, pending_price = ?, vendorID = ?, updatedAt = NOW()
        WHERE gas_id = ?
    ");
    $updateStmt->bind_param('ddddii', $totalQty, $avgPrice, $totalPaid, $totalPending, $vendorID, $gasId);
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to sync gas master totals');
    }
    $updateStmt->close();
}

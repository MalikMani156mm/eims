<?php
require __DIR__ . '/../db.php';

// Fetch all gases from gas_master with aggregated batch data
$gases = [];
$result = $conn->query("
    SELECT 
        gm.gas_id,
        gm.gas_name,
        gm.vendorID,
        gm.quantity as total_quantity,
        gm.unit_price as avg_unit_price,
        gm.paid_price,
        gm.pending_price,
        (gm.quantity * gm.unit_price) as total_amount,
        v.vendorName,
        COALESCE(SUM(gbd.available), 0) as total_available,
        COUNT(gbd.batch_id) as batch_count,
        (SELECT regionID FROM gas_batch_details WHERE gas_id = gm.gas_id LIMIT 1) as regionID,
        (SELECT r.regionName FROM gas_batch_details gbd2 
         LEFT JOIN regions r ON gbd2.regionID = r.regionID 
         WHERE gbd2.gas_id = gm.gas_id LIMIT 1) as regionName
    FROM gas_master gm
    LEFT JOIN vendors v ON gm.vendorID = v.vendorID
    LEFT JOIN gas_batch_details gbd ON gm.gas_id = gbd.gas_id
    GROUP BY gm.gas_id
    ORDER BY gm.gas_id DESC
");

if ($result) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        $row['serial'] = $counter++;
        $gases[] = $row;
    }
}

// Vendor pendencies summary
$vendorPendencies = [];
$vendorResult = $conn->query("
    SELECT
        v.vendorID,
        v.vendorName,
        COUNT(gbd.batch_id) as gas_count,
        COALESCE(SUM(gbd.total_price), 0) as total_amount,
        COALESCE(SUM(gbd.paid_price), 0) as total_paid,
        COALESCE(SUM(gbd.pending_price), 0) as total_pending
    FROM vendors v
    INNER JOIN gas_batch_details gbd ON v.vendorID = gbd.vendorID
    GROUP BY v.vendorID, v.vendorName
    ORDER BY total_pending DESC, v.vendorName ASC
");

if ($vendorResult) {
    $vendorSerial = 1;
    while ($row = $vendorResult->fetch_assoc()) {
        $row['serial'] = $vendorSerial++;
        $vendorPendencies[] = $row;
    }
}

$totalGasPending = 0;
$totalGasPaid = 0;
$totalGasAmount = 0;
foreach ($gases as $gas) {
    $totalGasPending += floatval($gas['pending_price']);
    $totalGasPaid += floatval($gas['paid_price']);
    $totalGasAmount += floatval($gas['total_amount']);
}

// Fetch gas_logs allocation history
$gasLogs = [];
$logsResult = $conn->query("
    SELECT 
        gl.gas_log_id,
        gl.product_id,
        gl.serial_number,
        p.productName as product_name,
        gm.gas_name,
        gbd.batchName,
        r.regionName,
        gl.quantity_used,
        gl.unit_price,
        gl.total_price,
        gl.created_at
    FROM gas_logs gl
    LEFT JOIN products p ON gl.product_id = p.productID
    LEFT JOIN models m ON p.modelID = m.modelID
    LEFT JOIN gas_master gm ON gl.gas_id = gm.gas_id
    LEFT JOIN gas_batch_details gbd ON gl.batch_id = gbd.batch_id
    LEFT JOIN regions r ON gbd.regionID = r.regionID
    ORDER BY gl.created_at DESC
");

if ($logsResult) {
    $counter = 1;
    while ($row = $logsResult->fetch_assoc()) {
        $row['serial'] = $counter++;
        $gasLogs[] = $row;
    }
}
?>

<div class="gases-section">
    <h2 style="margin-bottom: 25px; color: #333; font-size: 24px; font-weight: 600;">Gas Inventory Overview</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Gas Amount</div>
            <div style="font-size: 28px; font-weight: bold;">RS <?php echo number_format($totalGasAmount, 2); ?></div>
        </div>
        <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Paid</div>
            <div style="font-size: 28px; font-weight: bold;">RS <?php echo number_format($totalGasPaid, 2); ?></div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Pending</div>
            <div style="font-size: 28px; font-weight: bold;">RS <?php echo number_format($totalGasPending, 2); ?></div>
        </div>
        <div style="background: linear-gradient(135deg, #ff9800 0%, #ff6f00 100%); padding: 20px; border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Vendors with Pending</div>
            <div style="font-size: 28px; font-weight: bold;">
                <?php
                $vendorsWithPending = 0;
                foreach ($vendorPendencies as $vp) {
                    if (floatval($vp['total_pending']) > 0) {
                        $vendorsWithPending++;
                    }
                }
                echo $vendorsWithPending;
                ?>
            </div>
        </div>
    </div>
    
    <!-- Gases Table -->
    <div class="gases-table" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">#</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Gas Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Vendor</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Region</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Qty (kg)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Available (kg)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Avg Unit Price</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Amount</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Paid</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Pending</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Batches</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gases as $gas): ?>
                    <tr style="border-bottom: 1px solid #e0e0e0; transition: background-color 0.2s ease;">
                        <td style="padding: 12px; color: #666;"><?php echo $gas['serial']; ?></td>
                        <td style="padding: 12px;"><strong style="color: #333;"><?php echo htmlspecialchars($gas['gas_name']); ?></strong></td>
                        <td style="padding: 12px; color: #666;"><?php echo htmlspecialchars($gas['vendorName'] ?: 'N/A'); ?></td>
                        <td style="padding: 12px; color: #666;"><?php echo htmlspecialchars($gas['regionName'] ?: 'N/A'); ?></td>
                        <td style="padding: 12px; color: #333; font-weight: 600;"><?php echo number_format($gas['total_quantity'], 2); ?></td>
                        <td style="padding: 12px;">
                            <span style="color: #11998e; font-weight: 600; background: #e8f5f0; padding: 4px 8px; border-radius: 4px;">
                                <?php echo number_format($gas['total_available'], 2); ?>
                            </span>
                        </td>
                        <td style="padding: 12px; color: #666;">RS <?php echo number_format($gas['avg_unit_price'], 2); ?></td>
                        <td style="padding: 12px; color: #333; font-weight: 600;">RS <?php echo number_format($gas['total_amount'], 2); ?></td>
                        <td style="padding: 12px; color: #11998e; font-weight: 600;">RS <?php echo number_format($gas['paid_price'], 2); ?></td>
                        <td style="padding: 12px;">
                            <?php if (floatval($gas['pending_price']) > 0): ?>
                                <span style="color: #f44336; font-weight: 700; background: #ffebee; padding: 4px 10px; border-radius: 12px;">
                                    RS <?php echo number_format($gas['pending_price'], 2); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #4caf50; font-weight: 600;">RS 0.00</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                <?php echo $gas['batch_count']; ?>
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <button class="btn btn-sm btn-info" onclick="viewGasDetail(<?php echo htmlspecialchars(json_encode($gas)); ?>)" style="padding: 6px 12px; font-size: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px;">View</button>
                            <button class="btn btn-sm btn-logs" onclick="viewGasLogs(<?php echo $gas['gas_id']; ?>)" style="padding: 6px 12px; font-size: 12px; background: #16a085; color: white; border: none; border-radius: 4px; cursor: pointer;">Logs</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($gases)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 30px; color: #999;">No gases found. Start by adding a new gas from the Add New menu.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Vendor Pendencies Section -->
<div class="vendor-pendencies-section" style="margin-top: 40px; padding: 0 20px;">
    <h2 style="margin-bottom: 25px; color: #333; font-size: 24px; font-weight: 600;">🏪 Vendor Pendencies</h2>

    <div class="vendor-pendencies-table" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">#</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Vendor Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Batch Entries</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Amount</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Paid</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Pending</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Status</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: #333;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendorPendencies as $vendor): ?>
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 12px; color: #666;"><?php echo $vendor['serial']; ?></td>
                        <td style="padding: 12px;"><strong style="color: #333;"><?php echo htmlspecialchars($vendor['vendorName']); ?></strong></td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                <?php echo intval($vendor['gas_count']); ?>
                            </span>
                        </td>
                        <td style="padding: 12px; color: #333; font-weight: 600;">RS <?php echo number_format($vendor['total_amount'], 2); ?></td>
                        <td style="padding: 12px; color: #11998e; font-weight: 600;">RS <?php echo number_format($vendor['total_paid'], 2); ?></td>
                        <td style="padding: 12px;">
                            <?php if (floatval($vendor['total_pending']) > 0): ?>
                                <strong style="color: #f44336;">RS <?php echo number_format($vendor['total_pending'], 2); ?></strong>
                            <?php else: ?>
                                <strong style="color: #4caf50;">RS 0.00</strong>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php if (floatval($vendor['total_pending']) > 0): ?>
                                <span style="background: #ffebee; color: #c62828; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Pending</span>
                            <?php else: ?>
                                <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Cleared</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <?php if (floatval($vendor['total_pending']) > 0): ?>
                                <button class="btn btn-sm" onclick='payVendorPending(<?php echo intval($vendor['vendorID']); ?>, <?php echo json_encode($vendor['vendorName'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, <?php echo floatval($vendor['total_pending']); ?>)' style="padding: 6px 14px; font-size: 12px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Pay</button>
                            <?php else: ?>
                                <span style="color: #999; font-size: 12px;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($vendorPendencies)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #999;">No vendor pendencies found. Add gas with a vendor to track payments here.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Gas Allocation Logs Table -->
<div class="gas-logs-section" style="margin-top: 40px;">
    <h2 style="margin-bottom: 25px; color: #333; font-size: 24px; font-weight: 600;">⛽ Gas Allocation History</h2>
    
    <div class="gas-logs-table" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">#</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Product Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Serial Number</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Gas Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Batch Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Region</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Qty Used (kg)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Unit Price</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Price</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Allocated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gasLogs as $log): ?>
                    <tr style="border-bottom: 1px solid #e0e0e0; transition: background-color 0.2s ease;">
                        <td style="padding: 12px; color: #666;"><?php echo $log['serial']; ?></td>
                        <td style="padding: 12px;"><strong style="color: #333;"><?php echo htmlspecialchars($log['product_name'] ?: 'N/A'); ?></strong></td>
                        <td style="padding: 12px;"><strong style="color: #333; font-family: monospace;"><?php echo htmlspecialchars($log['serial_number']); ?></strong></td>
                        <td style="padding: 12px; color: #333;"><?php echo htmlspecialchars($log['gas_name']); ?></td>
                        <td style="padding: 12px; color: #666;"><?php echo htmlspecialchars($log['batchName'] ?: 'N/A'); ?></td>
                        <td style="padding: 12px; color: #666;"><?php echo htmlspecialchars($log['regionName'] ?: 'N/A'); ?></td>
                        <td style="padding: 12px; color: #333; font-weight: 600;"><?php echo number_format($log['quantity_used'], 2); ?></td>
                        <td style="padding: 12px; color: #666;">RS <?php echo number_format($log['unit_price'], 2); ?></td>
                        <td style="padding: 12px; color: #333; font-weight: 600;">RS <?php echo number_format($log['total_price'], 2); ?></td>
                        <td style="padding: 12px; color: #666; font-size: 12px;">
                            <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px;">
                                <?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($gasLogs)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 30px; color: #999;">No gas allocations found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Gas Detail Modal -->
<div id="viewGasDetailModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: white; padding: 20px; border-bottom: 2px solid #f0f0f0; border-radius: 16px 16px 0 0; z-index: 10;">
            <h2 style="margin: 0; color: #667eea;">Gas Details</h2>
            <button onclick="closeViewGasDetailModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <div id="gasDetailContent" style="padding: 20px;">
            <!-- Gas detail will be inserted here -->
        </div>
        <div style="bottom: 0; background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeViewGasDetailModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<!-- Gas Logs/Batches Modal -->
<div id="gasLogsModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 900px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px 16px 0 0; z-index: 10; color: white;">
            <h2 style="margin: 0;">Gas Batch Logs</h2>
            <button onclick="closeGasLogsModal()" style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.2); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <div id="gasLogsContent" style="padding: 20px;">
            <!-- Gas logs will be inserted here -->
        </div>
        <div style="bottom: 0; background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeGasLogsModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<script>
window.payVendorPending = function(vendorID, vendorName, totalPending) {
    Swal.fire({
        title: 'Pay Vendor Pending',
        html: `
            <p style="margin-bottom: 12px;">Vendor: <strong>${vendorName}</strong></p>
            <p style="margin-bottom: 16px;">Total Pending: <strong style="color: #f44336;">RS ${parseFloat(totalPending).toFixed(2)}</strong></p>
            <label for="vendorPaymentAmount" style="display:block; text-align:left; margin-bottom:8px; font-weight:600;">Payment Amount</label>
            <input id="vendorPaymentAmount" type="number" class="swal2-input" placeholder="Enter amount to pay" step="0.01" min="0.01" max="${totalPending}" value="${parseFloat(totalPending).toFixed(2)}" style="width:100%; margin:0;">
            <p style="margin-top: 10px; font-size: 12px; color: #666; text-align: left;">You can pay the full pending amount or a partial payment.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirm Payment',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#11998e',
        focusConfirm: false,
        preConfirm: function() {
            const amount = parseFloat(document.getElementById('vendorPaymentAmount').value);
            if (!amount || amount <= 0) {
                Swal.showValidationMessage('Please enter a valid payment amount');
                return false;
            }
            if (amount > totalPending + 0.0001) {
                Swal.showValidationMessage('Payment cannot exceed pending amount (RS ' + parseFloat(totalPending).toFixed(2) + ')');
                return false;
            }
            return amount;
        }
    }).then(function(result) {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: 'backend/payVendorPending.php',
            type: 'POST',
            data: {
                vendorID: vendorID,
                paymentAmount: result.value
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Recorded',
                        html: `
                            <p>${response.message}</p>
                            <p style="margin-top:8px;">Remaining Pending: <strong>RS ${parseFloat(response.remaining_pending || 0).toFixed(2)}</strong></p>
                        `,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(function() {
                        loadContent('gases');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        text: response.message || 'Could not record payment'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Server error while recording payment'
                });
            }
        });
    });
};

$(document).ready(function() {
    // View Gas Detail
    window.viewGasDetail = function(gas) {
        const usedQty = gas.total_quantity - gas.total_available;
        const detailsHtml = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; margin-bottom: 20px; color: white;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Gas Name</p>
                        <p style="margin: 0; font-size: 20px; font-weight: 600;">${gas.gas_name}</p>
                    </div>
                    <div>
                        <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Region</p>
                        <p style="margin: 0; font-size: 20px; font-weight: 600;">${gas.regionName || 'N/A'}</p>
                    </div>
                    <div>
                        <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Vendor</p>
                        <p style="margin: 0; font-size: 20px; font-weight: 600;">${gas.vendorName || 'N/A'}</p>
                    </div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea;">
                    <p style="margin: 0 0 8px 0; color: #666; font-size: 12px;">Total Quantity</p>
                    <p style="margin: 0; font-size: 24px; font-weight: 600; color: #333;">${parseFloat(gas.total_quantity).toFixed(2)} kg</p>
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #11998e;">
                    <p style="margin: 0 0 8px 0; color: #666; font-size: 12px;">Available Quantity</p>
                    <p style="margin: 0; font-size: 24px; font-weight: 600; color: #11998e;">${parseFloat(gas.total_available).toFixed(2)} kg</p>
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #f39c12;">
                    <p style="margin: 0 0 8px 0; color: #666; font-size: 12px;">Used Quantity</p>
                    <p style="margin: 0; font-size: 24px; font-weight: 600; color: #f39c12;">${parseFloat(usedQty).toFixed(2)} kg</p>
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea;">
                    <p style="margin: 0 0 8px 0; color: #666; font-size: 12px;">Average Unit Price</p>
                    <p style="margin: 0; font-size: 24px; font-weight: 600; color: #333;">RS ${parseFloat(gas.avg_unit_price).toFixed(2)}</p>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                    <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Total Amount</p>
                    <p style="margin: 0; font-size: 22px; font-weight: 600;">RS ${parseFloat(gas.total_amount || 0).toFixed(2)}</p>
                </div>
                <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                    <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Paid Price</p>
                    <p style="margin: 0; font-size: 22px; font-weight: 600;">RS ${parseFloat(gas.paid_price || 0).toFixed(2)}</p>
                </div>
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                    <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Pending Price</p>
                    <p style="margin: 0; font-size: 22px; font-weight: 600;">RS ${parseFloat(gas.pending_price || 0).toFixed(2)}</p>
                </div>
            </div>
            <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Total Batches</p>
                <p style="margin: 0; font-size: 28px; font-weight: 600;">${gas.batch_count}</p>
            </div>
        `;
        $('#gasDetailContent').html(detailsHtml);
        $('#viewGasDetailModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    };

    // Close View Gas Detail Modal
    window.closeViewGasDetailModal = function() {
        $('#viewGasDetailModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    };

    // View Gas Logs
    window.viewGasLogs = function(gasId) {
        $.ajax({
            url: 'backend/getGasLogs.php',
            type: 'GET',
            data: { gas_id: gasId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayGasLogs(response.data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to fetch gas logs'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Server error while fetching gas logs'
                });
            }
        });
    };

    function displayGasLogs(batches) {
        let html = `
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">#</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Batch Name</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Vendor</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Region</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Quantity (kg)</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Available (kg)</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Unit Price</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Price</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Paid</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Pending</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (batches.length === 0) {
            html += `
                <tr>
                    <td colspan="11" style="padding: 20px; text-align: center; color: #999;">No batch logs found.</td>
                </tr>
            `;
        } else {
            batches.forEach((batch, index) => {
                const rowColor = index % 2 === 0 ? '#f9f9f9' : 'white';
                html += `
                    <tr style="background: ${rowColor}; border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 12px; color: #666;">${index + 1}</td>
                        <td style="padding: 12px; color: #333; font-weight: 600;">${batch.batchName}</td>
                        <td style="padding: 12px; color: #666;">${batch.vendorName || 'N/A'}</td>
                        <td style="padding: 12px; color: #666;">${batch.regionName || 'N/A'}</td>
                        <td style="padding: 12px; color: #333;">${parseFloat(batch.quantity).toFixed(2)}</td>
                        <td style="padding: 12px;">
                            <span style="color: #11998e; font-weight: 600; background: #e8f5f0; padding: 4px 8px; border-radius: 4px;">
                                ${parseFloat(batch.available).toFixed(2)}
                            </span>
                        </td>
                        <td style="padding: 12px; color: #666;">RS ${parseFloat(batch.unit_price).toFixed(2)}</td>
                        <td style="padding: 12px; color: #333; font-weight: 600;">RS ${parseFloat(batch.total_price).toFixed(2)}</td>
                        <td style="padding: 12px; color: #11998e; font-weight: 600;">RS ${parseFloat(batch.paid_price || 0).toFixed(2)}</td>
                        <td style="padding: 12px; color: #f44336; font-weight: 600;">RS ${parseFloat(batch.pending_price || 0).toFixed(2)}</td>
                        <td style="padding: 12px; color: #666; font-size: 12px;">${batch.createdAt}</td>
                    </tr>
                `;
            });
        }

        html += `
                    </tbody>
                </table>
            </div>
        `;

        $('#gasLogsContent').html(html);
        $('#gasLogsModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    // Close Gas Logs Modal
    window.closeGasLogsModal = function() {
        $('#gasLogsModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    };

    // Close modals when clicking outside
    $(document).on('click', '#viewGasDetailModal', function(e) {
        if (e.target.id === 'viewGasDetailModal') {
            closeViewGasDetailModal();
        }
    });

    $(document).on('click', '#gasLogsModal', function(e) {
        if (e.target.id === 'gasLogsModal') {
            closeGasLogsModal();
        }
    });
});
</script>

<style>
    .gases-section {
        padding: 20px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table tbody tr:hover {
        background-color: #f5f5f5;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
</style>

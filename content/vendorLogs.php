<?php
require __DIR__ . '/../db.php';

$gases = [];
$result = $conn->query("
    SELECT
        gm.gas_id,
        gm.paid_price,
        gm.pending_price,
        (gm.quantity * gm.unit_price) as total_amount
    FROM gas_master gm
");

$totalGasPending = 0;
$totalGasPaid = 0;
$totalGasAmount = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $gases[] = $row;
        $totalGasPending += floatval($row['pending_price']);
        $totalGasPaid += floatval($row['paid_price']);
        $totalGasAmount += floatval($row['total_amount']);
    }
}

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

$vendorsWithPending = 0;
foreach ($vendorPendencies as $vp) {
    if (floatval($vp['total_pending']) > 0) {
        $vendorsWithPending++;
    }
}
?>

<div class="vendor-logs-section" style="padding: 20px;">
    <h2 style="margin-bottom: 25px; color: #333; font-size: 24px; font-weight: 600;">Vendor Logs Overview</h2>

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
            <div style="font-size: 28px; font-weight: bold;"><?php echo $vendorsWithPending; ?></div>
        </div>
    </div>
</div>

<div class="vendor-pendencies-section" style="padding: 0 20px 20px;">
    <h2 style="margin-bottom: 25px; color: #333; font-size: 24px; font-weight: 600;">Vendor Pendencies</h2>

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
                            <button class="btn btn-sm" onclick='viewVendorPurchaseLogs(<?php echo intval($vendor['vendorID']); ?>, <?php echo json_encode($vendor['vendorName'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' style="padding: 6px 14px; font-size: 12px; background: #16a085; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; margin-right: 6px;">Logs</button>
                            <?php if (floatval($vendor['total_pending']) > 0): ?>
                                <button class="btn btn-sm" onclick='payVendorPending(<?php echo intval($vendor['vendorID']); ?>, <?php echo json_encode($vendor['vendorName'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, <?php echo floatval($vendor['total_pending']); ?>)' style="padding: 6px 14px; font-size: 12px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Pay</button>
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

<!-- Vendor Purchase Logs Modal -->
<div id="vendorPurchaseLogsModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
        <div class="form-container vendor-purchase-logs-modal" style="max-width: 1000px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="position: relative; z-index: 1;">
        <div style="top: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px 16px 0 0; color: white;">
            <h2 style="margin: 0;" id="vendorPurchaseLogsModalTitle">Vendor Purchase Logs</h2>
            <button onclick="closeVendorPurchaseLogsModal()" style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.2); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <div style="padding: 20px 20px 0;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                <button onclick="exportVendorPurchaseLogsCSV()" style="padding: 10px 20px; background: #4caf50; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; box-shadow: 0 4px 12px rgba(76, 175, 80, 0.25);">
                    Export CSV
                </button>
            </div>
        </div>
        <div id="vendorPurchaseLogsContent" style="padding: 0 20px 20px;">
            <!-- Purchase logs will be inserted here -->
        </div>
        <div style="background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeVendorPurchaseLogsModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
        </div>
    </div>
</div>

<script>
window.currentVendorLogsExport = { vendorID: 0, vendorName: '' };

window.viewVendorPurchaseLogs = function(vendorID, vendorName) {
    window.currentVendorLogsExport = {
        vendorID: vendorID,
        vendorName: vendorName || ''
    };

    $.ajax({
        url: 'backend/getVendorPurchaseLogs.php',
        type: 'GET',
        data: { vendorID: vendorID },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayVendorPurchaseLogs(response.data || [], response.vendor || { vendorName: vendorName });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to fetch vendor purchase logs'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Server error while fetching vendor purchase logs'
            });
        }
    });
};

function displayVendorPurchaseLogs(logs, vendor) {
    const vendorName = (vendor && vendor.vendorName) ? vendor.vendorName : (window.currentVendorLogsExport.vendorName || 'Vendor');
    $('#vendorPurchaseLogsModalTitle').text('Purchase Logs - ' + vendorName);

    let html = `
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">#</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Gas Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Batch Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Region</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Quantity (kg)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Available (kg)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Unit Price</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Total Price</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Paid</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Pending</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #333;">Purchased At</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (!logs.length) {
        html += `
            <tr>
                <td colspan="11" style="padding: 20px; text-align: center; color: #999;">No purchase logs found for this vendor.</td>
            </tr>
        `;
    } else {
        logs.forEach(function(log, index) {
            const rowColor = index % 2 === 0 ? '#f9f9f9' : 'white';
            html += `
                <tr style="background: ${rowColor}; border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 12px; color: #666;">${index + 1}</td>
                    <td style="padding: 12px; color: #333; font-weight: 600;">${escapeVendorHtml(log.gas_name || 'N/A')}</td>
                    <td style="padding: 12px; color: #333; font-weight: 600;">${escapeVendorHtml(log.batchName || 'N/A')}</td>
                    <td style="padding: 12px; color: #666;">${escapeVendorHtml(log.regionName || 'N/A')}</td>
                    <td style="padding: 12px; color: #333;">${parseFloat(log.quantity || 0).toFixed(2)}</td>
                    <td style="padding: 12px;">
                        <span style="color: #11998e; font-weight: 600; background: #e8f5f0; padding: 4px 8px; border-radius: 4px;">
                            ${parseFloat(log.available || 0).toFixed(2)}
                        </span>
                    </td>
                    <td style="padding: 12px; color: #666;">RS ${parseFloat(log.unit_price || 0).toFixed(2)}</td>
                    <td style="padding: 12px; color: #333; font-weight: 600;">RS ${parseFloat(log.total_price || 0).toFixed(2)}</td>
                    <td style="padding: 12px; color: #11998e; font-weight: 600;">RS ${parseFloat(log.paid_price || 0).toFixed(2)}</td>
                    <td style="padding: 12px; color: #f44336; font-weight: 600;">RS ${parseFloat(log.pending_price || 0).toFixed(2)}</td>
                    <td style="padding: 12px; color: #666; font-size: 12px;">${escapeVendorHtml(log.createdAt || 'N/A')}</td>
                </tr>
            `;
        });
    }

    html += `
                </tbody>
            </table>
        </div>
    `;

    $('#vendorPurchaseLogsContent').html(html);
    $('#vendorPurchaseLogsModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
}

window.closeVendorPurchaseLogsModal = function() {
    $('#vendorPurchaseLogsModal').fadeOut(300);
    $('body').css('overflow', 'auto');
};

window.exportVendorPurchaseLogsCSV = function() {
    const vendorID = window.currentVendorLogsExport.vendorID;
    if (!vendorID) {
        Swal.fire({
            icon: 'warning',
            title: 'Nothing to Export',
            text: 'Open vendor purchase logs first.'
        });
        return;
    }

    window.location.href = 'backend/exportVendorPurchaseLogsCSV.php?vendorID=' + encodeURIComponent(vendorID);
};

function escapeVendorHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

$(document).on('click', '#vendorPurchaseLogsModal', function(e) {
    if (e.target.id === 'vendorPurchaseLogsModal') {
        closeVendorPurchaseLogsModal();
    }
});

$(document).on('click', '#vendorPurchaseLogsModal .form-container', function(e) {
    e.stopPropagation();
});

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
                        loadContent('vendorLogs');
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
</script>

<style>
    .vendor-logs-section .table tbody tr:hover,
    .vendor-pendencies-section .table tbody tr:hover {
        background-color: #f5f5f5;
    }

    .vendor-purchase-logs-modal {
        background: white !important;
        padding: 0 !important;
        overflow: hidden;
    }

    .vendor-purchase-logs-modal::before {
        display: none !important;
    }
</style>

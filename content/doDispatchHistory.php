<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$regionFilter = '';
if (isset($adminRole) && $adminRole !== 'superadmin') {
    $regionFilter = " AND ds.regionID = " . intval($regionID);
}

// Fetch all dispatch records with DO details — keep approval check intact
$query = "
    SELECT 
        ds.*,
        do.DO_Name,
        do.CNIC,
        COUNT(dsi.itemID) as itemCount,
        ua.full_name AS approvedByAdminName,
        us.full_name AS approvedBySuperName
    FROM do_sales ds
    INNER JOIN distributing_officer do ON ds.DO_ID = do.DO_ID
    LEFT JOIN do_sale_items dsi ON ds.saleID = dsi.saleID
    LEFT JOIN users ua ON ds.approvedByAdmin = ua.user_id
    LEFT JOIN users us ON ds.approvedBySuperAdmin = us.user_id
    WHERE (ds.approvedByAdmin IS NOT NULL AND ds.approvedBySuperAdmin IS NOT NULL)" . $regionFilter . "
    GROUP BY ds.saleID
    ORDER BY ds.saleDate DESC, ds.createdAt DESC
";

$result = $conn->query($query);
$dispatches = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dispatches[] = $row;
    }
}
?>

<style>
    .history-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .history-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .history-header h2 {
        margin: 0 0 5px 0;
        font-size: 28px;
    }
    
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #667eea;
    }
    
    .stat-card .label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }
    
    .stat-card .value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .history-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    .history-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .history-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }
    
    .history-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    
    .history-table tbody tr:hover {
        background: #f8f9ff;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }
    
    .status-paid {
        background: #4caf50;
        color: white;
    }
    
    .status-partial {
        background: #ff9800;
        color: white;
    }
    
    .status-pending {
        background: #f44336;
        color: white;
    }
    
    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        margin-right: 5px;
        transition: all 0.3s;
    }
    
    .btn-view {
        background: #2196f3;
        color: white;
    }
    
    .btn-view:hover {
        background: #1976d2;
        transform: translateY(-2px);
    }
    
    .btn-print {
        background: #4caf50;
        color: white;
    }
    
    .btn-print:hover {
        background: #388e3c;
        transform: translateY(-2px);
    }
    
    .btn-payment {
        background: #9c27b0;
        color: white;
    }
    
    .btn-payment:hover {
        background: #7b1fa2;
        transform: translateY(-2px);
    }
    
    .search-box {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        width: 300px;
        font-size: 14px;
        margin-bottom: 15px;
    }
    
    .search-box:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* View Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        overflow: auto;
    }
    
    .modal-content {
        background-color: white;
        margin: 3% auto;
        padding: 0;
        width: 90%;
        max-width: 900px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 25px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
    }
    
    .modal-close:hover {
        opacity: 0.8;
    }
    
    .modal-body {
        padding: 25px;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .detail-label {
        font-weight: bold;
        color: #666;
    }
    
    .detail-value {
        color: #333;
    }
</style>

<div class="history-container">
    <div class="history-header">
        <h2>📚 DO Dispatch History</h2>
        <p style="margin: 0; opacity: 0.9;">Complete record of all dispatches to distributing officers</p>
    </div>
    
    <div class="stats-cards">
        <div class="stat-card">
            <div class="label">Total Dispatches</div>
            <div class="value"><?php echo count($dispatches); ?></div>
        </div>
        <div class="stat-card" style="border-left-color: #4caf50;">
            <div class="label">Total Revenue</div>
            <div class="value">RS <?php echo number_format(array_sum(array_column($dispatches, 'grandTotal')), 2); ?></div>
        </div>
        <div class="stat-card" style="border-left-color: #ff9800;">
            <div class="label">Total Pending</div>
            <div class="value">RS <?php echo number_format(array_sum(array_column($dispatches, 'pendingAmount')), 2); ?></div>
        </div>
        <div class="stat-card" style="border-left-color: #2196f3;">
            <div class="label">Total Items</div>
            <div class="value"><?php echo array_sum(array_column($dispatches, 'itemCount')); ?></div>
        </div>
    </div>
    
    <div class="table-card">
        <input type="text" id="searchHistory" class="search-box" placeholder="🔍 Search by DO name, CNIC, or Sale ID...">
        
        <table class="history-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>DO Name</th>
                    <th>CNIC</th>
                    <th>Items</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Pending</th>
                    <th>Approved By Head</th>
                    <th>Approved By CEO</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
                <?php foreach ($dispatches as $dispatch): ?>
                <tr>
                    <td><strong>#<?php echo str_pad($dispatch['saleID'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo date('d M Y', strtotime($dispatch['saleDate'])); ?></td>
                    <td><?php echo htmlspecialchars($dispatch['DO_Name']); ?></td>
                    <td><code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px;"><?php echo htmlspecialchars($dispatch['CNIC']); ?></code></td>
                    <td><span class="status-badge" style="background: #2196f3;"><?php echo $dispatch['itemCount']; ?> items</span></td>
                    <td><strong>RS <?php echo number_format($dispatch['grandTotal'], 2); ?></strong></td>
                    <td style="color: #4caf50;">RS <?php echo number_format($dispatch['amountPaid'], 2); ?></td>
                    <td style="color: <?php echo $dispatch['pendingAmount'] > 0 ? '#f44336' : '#4caf50'; ?>;">
                        <strong>RS <?php echo number_format($dispatch['pendingAmount'], 2); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($dispatch['approvedByAdminName'] ?: ('ID: ' . ($dispatch['approvedByAdmin'] ?? 'N/A'))); ?></td>
                    <td><?php echo htmlspecialchars($dispatch['approvedBySuperName'] ?: ('ID: ' . ($dispatch['approvedBySuperAdmin'] ?? 'N/A'))); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $dispatch['paymentStatus']; ?>">
                            <?php echo strtoupper($dispatch['paymentStatus']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-action btn-view" onclick="viewDispatch(<?php echo $dispatch['saleID']; ?>)">
                            👁️ View
                        </button>
                        <button class="btn-action btn-print" onclick="printReceipt(<?php echo $dispatch['saleID']; ?>)">
                            🖨️ Print
                        </button>
                        <?php if ($dispatch['pendingAmount'] > 0): ?>
                        <button class="btn-action btn-payment" onclick="openPaymentModal(<?php echo $dispatch['saleID']; ?>, <?php echo $dispatch['pendingAmount']; ?>)">
                            💰 Pay
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;">📋 Dispatch Details</h3>
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 style="margin: 0;">💰 Record Payment</h3>
            <button class="modal-close" onclick="closePaymentModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="paymentForm" onsubmit="submitPayment(event)">
                <input type="hidden" id="paymentSaleID" name="saleID">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #666;">
                        Pending Amount:
                    </label>
                    <div style="background: #ffebee; padding: 15px; border-radius: 8px; text-align: center;">
                        <span style="font-size: 24px; font-weight: bold; color: #f44336;">
                            RS <span id="pendingAmountDisplay">0.00</span>
                        </span>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="paymentAmount" style="display: block; margin-bottom: 8px; font-weight: bold; color: #666;">
                        Payment Amount: <span style="color: #f44336;">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="paymentAmount" 
                        name="paymentAmount" 
                        step="0.01" 
                        min="0.01"
                        required
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;"
                        placeholder="Enter payment amount">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="paymentRemarks" style="display: block; margin-bottom: 8px; font-weight: bold; color: #666;">
                        Remarks: <span style="color: #999; font-weight: normal;">(Optional)</span>
                    </label>
                    <textarea 
                        id="paymentRemarks" 
                        name="remarks" 
                        rows="3"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; resize: vertical;"
                        placeholder="Enter any notes or remarks..."></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button 
                        type="button" 
                        onclick="closePaymentModal()"
                        style="padding: 12px 24px; background: #999; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        style="padding: 12px 24px; background: #4caf50; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">
                        💰 Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Search functionality
        $('#searchHistory').on('keyup', function() {
            const searchText = $(this).val().toLowerCase();
            
            $('#historyTableBody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
            });
        });
    });
    
    function viewDispatch(saleID) {
        // Show loading
        $('#modalBody').html('<div style="text-align: center; padding: 40px;"><div style="font-size: 18px; color: #666;">Loading...</div></div>');
        $('#viewModal').show();
        
        // Fetch dispatch details
        $.ajax({
            url: 'backend/getDispatchDetails.php',
            type: 'GET',
            data: { saleID: saleID },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayDispatchDetails(response.data);
                } else {
                    $('#modalBody').html('<div style="text-align: center; padding: 40px; color: #f44336;">Error loading details</div>');
                }
            },
            error: function() {
                $('#modalBody').html('<div style="text-align: center; padding: 40px; color: #f44336;">Failed to load details</div>');
            }
        });
    }
    
    function displayDispatchDetails(data) {
        let itemsHtml = '';
        data.items.forEach((item, index) => {
            itemsHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td><code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px;">${item.serialNumber}</code></td>
                    <td>${item.productName}</td>
                    <td>${item.batchNumber}</td>
                    <td>RS ${parseFloat(item.sellingPrice).toFixed(2)}</td>
                    <td>${parseFloat(item.discountPercent).toFixed(2)}%</td>
                    <td>RS ${parseFloat(item.finalPrice).toFixed(2)}</td>
                </tr>
            `;
        });
        
        let paymentsHtml = '';
        if (data.payments && data.payments.length > 0) {
            data.payments.forEach((payment, index) => {
                paymentsHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${new Date(payment.paymentDate).toLocaleDateString('en-GB')} ${new Date(payment.paymentDate).toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'})}</td>
                        <td>RS ${parseFloat(payment.paymentAmount).toFixed(2)}</td>
                        <td>${payment.paymentMethod || 'Cash'}</td>
                        <td>${payment.notes || '-'}</td>
                        <td>System</td>
                    </tr>
                `;
            });
        } else {
            paymentsHtml = '<tr><td colspan="6" style="text-align: center; color: #999;">No payments recorded yet</td></tr>';
        }
        
        const html = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <h4 style="margin: 0 0 15px 0; color: #667eea;">Sale Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Sale ID:</span>
                        <span class="detail-value">#${String(data.sale.saleID).padStart(6, '0')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sale Date:</span>
                        <span class="detail-value">${new Date(data.sale.saleDate).toLocaleDateString('en-GB')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Days:</span>
                        <span class="detail-value">${data.sale.paymentDays} days</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Due Date:</span>
                        <span class="detail-value">${data.sale.dueDate ? new Date(data.sale.dueDate).toLocaleDateString('en-GB') : 'N/A'}</span>
                    </div>
                </div>
                <div>
                    <h4 style="margin: 0 0 15px 0; color: #667eea;">DO Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${data.sale.DO_Name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">CNIC:</span>
                        <span class="detail-value">${data.sale.CNIC}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact:</span>
                        <span class="detail-value">${data.sale.contactNumber}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${data.sale.Address}</span>
                    </div>
                </div>
            </div>
            
            <h4 style="margin: 20px 0 10px 0; color: #667eea;">Items</h4>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Final Price</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">RS ${parseFloat(data.sale.totalAmount).toFixed(2)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Discount Amount:</span>
                    <span class="detail-value">RS ${parseFloat(data.sale.discountAmount).toFixed(2)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label" style="font-size: 18px; color: #667eea;">Grand Total:</span>
                    <span class="detail-value" style="font-size: 18px; font-weight: bold; color: #667eea;">RS ${parseFloat(data.sale.grandTotal).toFixed(2)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value" style="color: #4caf50;">RS ${parseFloat(data.sale.amountPaid).toFixed(2)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Pending Amount:</span>
                    <span class="detail-value" style="color: ${parseFloat(data.sale.pendingAmount) > 0 ? '#f44336' : '#4caf50'};">RS ${parseFloat(data.sale.pendingAmount).toFixed(2)}</span>
                </div>
                <div class="detail-row" style="border-bottom: none;">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-${data.sale.paymentStatus}">
                            ${data.sale.paymentStatus.toUpperCase()}
                        </span>
                    </span>
                </div>
            </div>
            
            <h4 style="margin: 20px 0 10px 0; color: #667eea;">💳 Payment History</h4>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Remarks</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    ${paymentsHtml}
                </tbody>
            </table>
        `;
        
        $('#modalBody').html(html);
    }
    
    function closeViewModal() {
        $('#viewModal').hide();
    }
    
    function printReceipt(saleID) {
        window.open('backend/generateDOReceipt.php?saleID=' + saleID, '_blank');
    }
    
    function openPaymentModal(saleID, pendingAmount) {
        $('#paymentSaleID').val(saleID);
        $('#pendingAmountDisplay').text(parseFloat(pendingAmount).toFixed(2));
        $('#paymentAmount').attr('max', pendingAmount);
        $('#paymentAmount').val('');
        $('#paymentRemarks').val('');
        $('#paymentModal').show();
    }
    
    function closePaymentModal() {
        $('#paymentModal').hide();
    }
    
    function submitPayment(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const pendingAmount = parseFloat($('#pendingAmountDisplay').text());
        const paymentAmount = parseFloat(formData.get('paymentAmount'));
        
        if (paymentAmount > pendingAmount) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Amount',
                text: 'Payment amount cannot exceed pending amount (RS ' + pendingAmount.toFixed(2) + ')'
            });
            return;
        }
        
        $.ajax({
            url: 'backend/recordDOPayment.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    closePaymentModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Recorded!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to record payment. Please try again.'
                });
            }
        });
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const viewModal = document.getElementById('viewModal');
        const paymentModal = document.getElementById('paymentModal');
        
        if (event.target == viewModal) {
            closeViewModal();
        }
        if (event.target == paymentModal) {
            closePaymentModal();
        }
    }
</script>

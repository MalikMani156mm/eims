<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Fetch pending and partially paid ledger dispatches
$query = "
    SELECT 
        ls.*,
        COUNT(lsi.itemID) as itemCount,
        DATEDIFF(ls.dueDate, CURDATE()) as daysRemaining,
        CASE 
            WHEN ls.dueDate IS NULL OR ls.dueDate = '0000-00-00' THEN 'no_due'
            WHEN DATEDIFF(ls.dueDate, CURDATE()) < 0 THEN 'overdue'
            WHEN DATEDIFF(ls.dueDate, CURDATE()) <= 7 THEN 'urgent'
            ELSE 'normal'
        END as urgencyStatus
    FROM ledger_sales ls
    LEFT JOIN ledger_sale_items lsi ON ls.saleID = lsi.saleID
    WHERE ls.paymentStatus IN ('pending', 'partial')
    GROUP BY ls.saleID
    ORDER BY 
        CASE 
            WHEN ls.dueDate IS NULL OR ls.dueDate = '0000-00-00' THEN 3
            WHEN DATEDIFF(ls.dueDate, CURDATE()) < 0 THEN 1
            WHEN DATEDIFF(ls.dueDate, CURDATE()) <= 7 THEN 2
            ELSE 3
        END,
        ls.dueDate ASC
";

$result = $conn->query($query);
$pendingDispatches = [];
$overdueCount = 0;
$urgentCount = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pendingDispatches[] = $row;
        if ($row['urgencyStatus'] == 'overdue') {
            $overdueCount++;
        } elseif ($row['urgencyStatus'] == 'urgent') {
            $urgentCount++;
        }
    }
}

$totalPending = array_sum(array_column($pendingDispatches, 'pendingAmount'));
?>

<style>
    .pending-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .pending-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
    }
    
    .pending-header h2 {
        margin: 0 0 5px 0;
        font-size: 28px;
    }
    
    .alert-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .alert-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid;
    }
    
    .alert-card.danger {
        border-left-color: #f44336;
    }
    
    .alert-card.warning {
        border-left-color: #ff9800;
    }
    
    .alert-card.info {
        border-left-color: #2196f3;
    }
    
    .alert-card .label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }
    
    .alert-card .value {
        font-size: 24px;
        font-weight: bold;
    }
    
    .alert-card.danger .value {
        color: #f44336;
    }
    
    .alert-card.warning .value {
        color: #ff9800;
    }
    
    .alert-card.info .value {
        color: #2196f3;
    }
    
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .pending-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    .pending-table thead {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .pending-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }
    
    .pending-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    
    .pending-table tbody tr:hover {
        background: #f0fdf9;
    }
    
    .pending-table tbody tr.overdue {
        background: #ffebee !important;
    }
    
    .pending-table tbody tr.urgent {
        background: #fff3e0 !important;
    }
    
    .urgency-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }
    
    .urgency-overdue {
        background: #f44336;
        color: white;
        animation: pulse 2s infinite;
    }
    
    .urgency-urgent {
        background: #ff9800;
        color: white;
    }
    
    .urgency-normal {
        background: #4caf50;
        color: white;
    }
    
    .urgency-no_due {
        background: #9e9e9e;
        color: white;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.6;
        }
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
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
        border-color: #11998e;
        box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
    }
</style>

<div class="pending-container">
    <div class="pending-header">
        <h2>⏳ Ledgers Pending Payments</h2>
        <p style="margin: 0; opacity: 0.9;">Track pending and overdue payments from ledgers</p>
    </div>
    
    <div class="alert-stats">
        <div class="alert-card danger">
            <div class="label">🚨 Overdue Payments</div>
            <div class="value"><?php echo $overdueCount; ?></div>
        </div>
        <div class="alert-card warning">
            <div class="label">⚠️ Due in 7 Days</div>
            <div class="value"><?php echo $urgentCount; ?></div>
        </div>
        <div class="alert-card info">
            <div class="label">📋 Total Pending</div>
            <div class="value"><?php echo count($pendingDispatches); ?></div>
        </div>
        <div class="alert-card info" style="border-left-color: #f44336;">
            <div class="label" style="color: #f44336;">💰 Amount Pending</div>
            <div class="value" style="color: #f44336;">RS <?php echo number_format($totalPending, 2); ?></div>
        </div>
    </div>
    
    <div class="table-card">
        <input type="text" id="searchPending" class="search-box" placeholder="🔍 Search by ledger name, CNIC, or Sale ID...">
        
        <table class="pending-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Sale Date</th>
                    <th>Ledger Name</th>
                    <th>CNIC</th>
                    <th>Items</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Pending</th>
                    <th>Due Date</th>
                    <th>Days Left</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pendingTableBody">
                <?php foreach ($pendingDispatches as $dispatch): ?>
                <tr class="<?php echo $dispatch['urgencyStatus']; ?>">
                    <td><strong>#<?php echo str_pad($dispatch['saleID'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo date('d M Y', strtotime($dispatch['saleDate'])); ?></td>
                    <td><?php echo htmlspecialchars($dispatch['ledgerName']); ?></td>
                    <td><code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px;"><?php echo htmlspecialchars($dispatch['ledgerCNIC']); ?></code></td>
                    <td><span class="status-badge" style="background: #2196f3;"><?php echo $dispatch['itemCount']; ?> items</span></td>
                    <td><strong>RS <?php echo number_format($dispatch['grandTotal'], 2); ?></strong></td>
                    <td style="color: #4caf50;">RS <?php echo number_format($dispatch['amountPaid'], 2); ?></td>
                    <td style="color: #f44336;"><strong>RS <?php echo number_format($dispatch['pendingAmount'], 2); ?></strong></td>
                    <td>
                        <?php if (!empty($dispatch['dueDate']) && $dispatch['dueDate'] != '0000-00-00'): ?>
                            <?php echo date('d M Y', strtotime($dispatch['dueDate'])); ?>
                        <?php else: ?>
                            <span style="color: #999;">No due date</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if ($dispatch['urgencyStatus'] == 'no_due') {
                            echo '<span class="urgency-badge urgency-no_due">No Due Date</span>';
                        } elseif ($dispatch['urgencyStatus'] == 'overdue') {
                            echo '<span class="urgency-badge urgency-overdue">🚨 ' . abs($dispatch['daysRemaining']) . ' days overdue</span>';
                        } elseif ($dispatch['urgencyStatus'] == 'urgent') {
                            echo '<span class="urgency-badge urgency-urgent">⚠️ ' . $dispatch['daysRemaining'] . ' days left</span>';
                        } else {
                            echo '<span class="urgency-badge urgency-normal">✅ ' . $dispatch['daysRemaining'] . ' days left</span>';
                        }
                        ?>
                    </td>
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
                        <button class="btn-action btn-payment" onclick="openPaymentModal(<?php echo $dispatch['saleID']; ?>, <?php echo $dispatch['pendingAmount']; ?>)">
                            💰 Pay
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (count($pendingDispatches) === 0): ?>
                <tr>
                    <td colspan="12" style="text-align: center; padding: 40px; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
                        <div style="font-size: 18px; font-weight: bold;">All Clear!</div>
                        <div style="font-size: 14px; margin-top: 5px;">No pending payments at the moment</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); overflow:auto;">
    <div style="background:white; margin:5% auto; padding:0; width:90%; max-width:500px; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
        <div style="background:linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color:white; padding:20px 25px; border-radius:12px 12px 0 0; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;">💰 Record Payment</h3>
            <button onclick="closePaymentModal()" style="color:white; font-size:28px; font-weight:bold; cursor:pointer; background:none; border:none;">&times;</button>
        </div>
        <div style="padding:25px;">
            <form id="paymentForm" onsubmit="submitPayment(event)">
                <input type="hidden" id="paymentSaleID" name="saleID">
                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#666;">Pending Amount:</label>
                    <div style="background:#ffebee; padding:15px; border-radius:8px; text-align:center;">
                        <span style="font-size:24px; font-weight:bold; color:#f44336;">RS <span id="pendingAmountDisplay">0.00</span></span>
                    </div>
                </div>
                <div style="margin-bottom:20px;">
                    <label for="paymentAmount" style="display:block; margin-bottom:8px; font-weight:bold; color:#666;">Payment Amount: <span style="color:#f44336;">*</span></label>
                    <input type="number" id="paymentAmount" name="paymentAmount" step="0.01" min="0.01" required
                        style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;" placeholder="Enter payment amount">
                </div>
                <div style="margin-bottom:20px;">
                    <label for="paymentRemarks" style="display:block; margin-bottom:8px; font-weight:bold; color:#666;">Remarks: <span style="color:#999; font-weight:normal;">(Optional)</span></label>
                    <textarea id="paymentRemarks" name="remarks" rows="3"
                        style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:14px; resize:vertical;"
                        placeholder="Enter any notes or remarks..."></textarea>
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" onclick="closePaymentModal()"
                        style="padding:12px 24px; background:#999; color:white; border:none; border-radius:8px; cursor:pointer; font-size:14px;">Cancel</button>
                    <button type="submit"
                        style="padding:12px 24px; background:#4caf50; color:white; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:bold;">💰 Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Search functionality
        $('#searchPending').on('keyup', function() {
            const searchText = $(this).val().toLowerCase();
            $('#pendingTableBody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
            });
        });
        
        // Update overdue count in sidebar
        updateLedgerOverdueCount(<?php echo $overdueCount; ?>);
    });
    
    function viewDispatch(saleID) {
        loadContent('ledgersDispatchHistory');
        setTimeout(() => {
            if (typeof viewDispatch === 'function') {
                viewDispatch(saleID);
            }
        }, 500);
    }
    
    function printReceipt(saleID) {
        window.open('backend/generateLedgerReceipt.php?saleID=' + saleID, '_blank');
    }
    
    function openPaymentModal(saleID, pendingAmount) {
        $('#paymentSaleID').val(saleID);
        $('#pendingAmountDisplay').text(parseFloat(pendingAmount).toFixed(2));
        $('#paymentAmount').attr('max', pendingAmount).val('');
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
            Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Payment amount cannot exceed pending amount (RS ' + pendingAmount.toFixed(2) + ')' });
            return;
        }
        
        $.ajax({
            url: 'backend/recordLedgerPayment.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    closePaymentModal();
                    Swal.fire({ icon: 'success', title: 'Payment Recorded!', text: response.message, timer: 2000, showConfirmButton: false })
                        .then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: response.message });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to record payment. Please try again.' });
            }
        });
    }
    
    function updateLedgerOverdueCount(count) {
        const badge = $('#overdueCountLedger');
        if (count > 0) { badge.text(count).show(); } else { badge.hide(); }
    }
    
    window.onclick = function(event) {
        if (event.target == document.getElementById('paymentModal')) {
            closePaymentModal();
        }
    }
</script>

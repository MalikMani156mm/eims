<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Only admins (and superadmin) should access — admin will see only their region
if (!isset($adminRole)) {
    echo '<div style="padding:20px;">Access denied</div>';
    exit;
}

$regionFilter = '';
if ($adminRole !== 'superadmin') {
    $regionFilter = ' AND ds.regionID = ' . intval($regionID);
}

// Fetch DO sales awaiting admin approval (both approval columns NULL)
$query = "
    SELECT ds.*, do.DO_Name, do.CNIC, COUNT(dsi.itemID) as itemCount,
        COALESCE((
            SELECT SUM(CASE WHEN s2.paymentStatus IN ('pending','partial') THEN s2.pendingAmount ELSE 0 END)
            FROM do_sales s2
            WHERE s2.DO_ID = ds.DO_ID
        ), 0) as doPendingAmount
    FROM do_sales ds
    INNER JOIN distributing_officer do ON ds.DO_ID = do.DO_ID
    LEFT JOIN do_sale_items dsi ON ds.saleID = dsi.saleID
    WHERE ds.approvedByAdmin IS NULL AND ds.approvedBySuperAdmin IS NULL" . $regionFilter . "
    GROUP BY ds.saleID
    ORDER BY ds.saleDate DESC, ds.createdAt DESC
";

$result = $conn->query($query);
$sales = [];
if ($result) {
    while ($row = $result->fetch_assoc()) $sales[] = $row;
}
?>

<style>
/* reuse doApproval styles */
.approval-container { padding:20px; max-width:1100px; margin:0 auto; }
.approval-header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; padding:18px; border-radius:12px; margin-bottom:18px; }
.approval-card { background:#fff; padding:18px; border-radius:10px; box-shadow:0 6px 24px rgba(0,0,0,0.06); }
.approval-table { width:100%; border-collapse:collapse; }
.approval-table th { background: #667eea; color:#fff; padding:12px; text-align:left; }
.approval-table td { padding:12px; border-bottom:1px solid #f1f3fb; }
.btn-small { padding:8px 12px; border-radius:8px; border:0; cursor:pointer; font-weight:600; }
.btn-view { background:#2196f3; color:#fff; }
.btn-approve { background:#4caf50; color:#fff; }
.btn-reject { background:#f44336; color:#fff; }
.badge-region { background:#f5f5f5; padding:6px 10px; border-radius:8px; color:#333; font-weight:600; }
</style>

<div class="approval-container">
    <div class="approval-header">
        <h2 style="margin:0">🔔 Dispatches — Awaiting Admin Approval</h2>
        <p style="margin:6px 0 0 0; opacity:0.9">Approve or reject dispatches for your region</p>
    </div>

    <div class="approval-card">
        <table class="approval-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Name</th>
                    <th>CNIC</th>
                    <th>Pending</th>
                    <th>Items</th>
                    <th>Grand Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="8" style="padding:18px; text-align:center; color:#666">No dispatches awaiting approval</td></tr>
                <?php else: foreach ($sales as $s): ?>
                    <tr>
                        <td><strong>#<?php echo str_pad($s['saleID'],6,'0',STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($s['saleDate'])); ?></td>
                        <td><?php echo htmlspecialchars($s['DO_Name']); ?></td>
                        <td><code style="background:#f7f8fb;padding:4px 8px;border-radius:6px"><?php echo htmlspecialchars($s['CNIC']); ?></code></td>
                        <td><strong>RS <?php echo number_format($s['doPendingAmount'] ?? 0,2); ?></strong></td>
                        <td><?php echo $s['itemCount']; ?> items</td>
                        <td><strong>RS <?php echo number_format($s['grandTotal'],2); ?></strong></td>
                        <td>
                            <button class="btn-small btn-view" onclick="viewSale(<?php echo (int)$s['saleID']; ?>)">View</button>
                            <button class="btn-small btn-approve" onclick="approveSale(<?php echo (int)$s['saleID']; ?>, this)">Approve</button>
                            <button class="btn-small btn-reject" onclick="rejectSale(<?php echo (int)$s['saleID']; ?>, this)">Reject</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Modal -->
<div id="saleViewModal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="max-width:900px; margin:4% auto; background:white; border-radius:12px; overflow:hidden;">
        <div style="background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; padding:16px; position:relative;">
            <h3 id="saleViewTitle" style="margin:0">Dispatch</h3>
            <button onclick="$('#saleViewModal').hide()" style="position:absolute; right:12px; top:12px; background:rgba(255,255,255,0.2); border:0; color:#fff; width:36px; height:36px; border-radius:50%">&times;</button>
        </div>
        <div id="saleViewBody" style="padding:18px; max-height:70vh; overflow:auto;"></div>
    </div>
</div>

<script>
function viewSale(id) {
    $('#saleViewBody').html('<div style="padding:40px; text-align:center; color:#666">Loading...</div>');
    $('#saleViewModal').show();
    $.get('backend/getDispatchDetails.php', { saleID: id }, function(resp){
            if (resp && resp.success) {
            let html = '';
            html += '<div style="margin-bottom:12px"><strong>Sale ID:</strong> #' + String(resp.data.sale.saleID).padStart(6,'0') + '</div>';
            html += '<div style="margin-bottom:12px"><strong>Warehouse:</strong> ' + (resp.data.sale.DO_Name || '') + ' (' + (resp.data.sale.CNIC || '') + ')</div>';
            html += '<div style="margin-bottom:12px"><strong>Amount Paid:</strong> RS ' + (resp.data.sale.amountPaid ? parseFloat(resp.data.sale.amountPaid).toFixed(2) : '0.00') + '</div>';
            html += '<div style="margin-bottom:12px"><strong>Pending Amount:</strong> RS ' + (resp.data.sale.pendingAmount ? parseFloat(resp.data.sale.pendingAmount).toFixed(2) : '0.00') + '</div>';
            // Show payment days and due date if present
            if (resp.data.sale.paymentDays !== 0 && resp.data.sale.paymentDays !== null && resp.data.sale.paymentDays !== '') {
                html += '<div style="margin-bottom:12px"><strong>Payment Days:</strong> ' + resp.data.sale.paymentDays + '</div>';
            }
            if (resp.data.sale.dueDate) {
                // try to format date, fallback to raw string
                var dd = new Date(resp.data.sale.dueDate);
                var dueFormatted = isNaN(dd.getTime()) ? resp.data.sale.dueDate : dd.toLocaleDateString('en-GB');
                html += '<div style="margin-bottom:12px"><strong>Due Date:</strong> ' + dueFormatted + '</div>';
            }
            html += '<h4>Items</h4>';
            html += '<table style="width:100%; border-collapse:collapse">';
            html += '<tr><th style="text-align:left;padding:6px">#</th><th style="text-align:left;padding:6px">Serial</th><th style="text-align:left;padding:6px">Product</th><th style="text-align:left;padding:6px">Price</th></tr>';
            resp.data.items.forEach(function(it, idx){
                html += '<tr><td style="padding:6px">' + (idx+1) + '</td><td style="padding:6px"><code style="background:#f7f8fb;padding:4px 6px;border-radius:4px">' + (it.serialNumber||'') + '</code></td><td style="padding:6px">' + (it.productName||'') + '</td><td style="padding:6px">RS ' + parseFloat(it.finalPrice).toFixed(2) + '</td></tr>';
            });
            html += '</table>';
            $('#saleViewBody').html(html);
        } else {
            $('#saleViewBody').html('<div style="padding:40px; text-align:center; color:#f44336">Failed to load</div>');
        }
    }, 'json').fail(function(){ $('#saleViewBody').html('<div style="padding:40px; text-align:center; color:#f44336">Server error</div>'); });
}

function approveSale(id, btn) {
    Swal.fire({ title: 'Approve this dispatch?', icon: 'question', showCancelButton: true }).then(r=>{ if(!r.isConfirmed) return; $(btn).prop('disabled', true).text('Approving...'); $.post('backend/approveDOSale.php',{ saleID: id }, function(resp){ if(resp && resp.success){ Swal.fire('Approved','Dispatch approved','success').then(()=> location.reload()); } else { Swal.fire('Error', resp && resp.message ? resp.message : 'Failed','error'); $(btn).prop('disabled', false).text('Approve'); } }, 'json').fail(()=>{ Swal.fire('Error','Server error','error'); $(btn).prop('disabled', false).text('Approve'); }); });
}

function rejectSale(id, btn) {
    Swal.fire({ title: 'Reject this dispatch?', text: 'This will return serials and inventory to available.', icon: 'warning', showCancelButton: true }).then(r=>{ if(!r.isConfirmed) return; $(btn).prop('disabled', true).text('Rejecting...'); $.post('backend/rejectDOSale.php',{ saleID: id }, function(resp){ if(resp && resp.success){ Swal.fire('Rejected','Dispatch rejected and inventory reverted','success').then(()=> location.reload()); } else { Swal.fire('Error', resp && resp.message ? resp.message : 'Failed','error'); $(btn).prop('disabled', false).text('Reject'); } }, 'json').fail(()=>{ Swal.fire('Error','Server error','error'); $(btn).prop('disabled', false).text('Reject'); }); });
}
</script>

<?php $conn->close(); ?>

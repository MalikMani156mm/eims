<?php
require '../adminAuth.php';
require '../db.php';

// Only superadmin should see this page; double-check
if (!isset($adminRole) || $adminRole !== 'superadmin') {
    echo '<div style="padding:20px;">Access denied</div>';
    exit;
}

// Fetch pending DOs (status = 1)
$sql = "SELECT d.*, r.regionName FROM distributing_officer d LEFT JOIN regions r ON d.regionID = r.regionID WHERE d.status = 1 ORDER BY d.createdAt DESC";
$res = $conn->query($sql);
$officers = [];
if ($res) {
    while ($row = $res->fetch_assoc()) $officers[] = $row;
}
?>

<style>
/* Inspired by doDispatchHistory styles */
.approval-container { padding:20px; max-width:1100px; margin:0 auto; }
.approval-header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; padding:18px; border-radius:12px; margin-bottom:18px; }
.approval-card { background:#fff; padding:18px; border-radius:10px; box-shadow:0 6px 24px rgba(0,0,0,0.06); }
.approval-table { width:100%; border-collapse:collapse; }
.approval-table th { background: #667eea; color:#fff; padding:12px; text-align:left; }
.approval-table td { padding:12px; border-bottom:1px solid #f1f3fb; }
.btn-small { padding:8px 12px; border-radius:8px; border:0; cursor:pointer; font-weight:600; }
.btn-view { background:#2196f3; color:#fff; }
.btn-approve { background:#4caf50; color:#fff; }
.btn-delete { background:#f44336; color:#fff; }
.badge-region { background:#f5f5f5; padding:6px 10px; border-radius:8px; color:#333; font-weight:600; }
</style>

<div class="approval-container">
    <div class="approval-header">
        <h2 style="margin:0">✅ Distributing Officers — Pending Approval</h2>
        <p style="margin:6px 0 0 0; opacity:0.9">Review and approve new distributing officers</p>
    </div>

    <div class="approval-card">
        <table class="approval-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>CNIC</th>
                    <th>Contact</th>
                    <th>Region</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($officers)): ?>
                    <tr><td colspan="7" style="padding:18px; text-align:center; color:#666">No pending officers</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($officers as $off): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($off['DO_Name']); ?></strong></td>
                            <td><code style="background:#f7f8fb;padding:4px 8px;border-radius:6px"><?php echo htmlspecialchars($off['CNIC']); ?></code></td>
                            <td><?php echo htmlspecialchars($off['contactNumber']); ?></td>
                            <td><span class="badge-region"><?php echo htmlspecialchars($off['regionName'] ?: 'N/A'); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($off['createdAt'])); ?></td>
                            <td>
                                <button class="btn-small btn-view" onclick='viewDO(<?php echo json_encode($off); ?>)'>View</button>
                                <button class="btn-small btn-approve" onclick="approveDO(<?php echo (int)$off['DO_ID']; ?>, this)">Approve</button>
                                <button class="btn-small btn-delete" onclick="confirmDelete(<?php echo (int)$off['DO_ID']; ?>, this)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Modal -->
<div id="doViewModal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="max-width:700px; margin:6% auto; background:white; border-radius:12px; overflow:hidden;">
        <div style="background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; padding:16px; position:relative;">
            <h3 id="doViewTitle" style="margin:0">Officer</h3>
            <button onclick="$('#doViewModal').hide()" style="position:absolute; right:12px; top:12px; background:rgba(255,255,255,0.2); border:0; color:#fff; width:36px; height:36px; border-radius:50%">&times;</button>
        </div>
        <div id="doViewBody" style="padding:18px;"></div>
    </div>
</div>

<script>
function viewDO(off) {
    const html = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
            <div>
                <div style="font-size:12px; color:#666">Full Name</div>
                <div style="font-weight:700">${off.DO_Name || 'N/A'}</div>
            </div>
            <div>
                <div style="font-size:12px; color:#666">Region</div>
                <div style="font-weight:700">${off.regionName || 'N/A'}</div>
            </div>
        </div>
        <div style="margin-top:12px">
            <div style="font-size:12px; color:#666">CNIC</div>
            <div>${off.CNIC || 'N/A'}</div>
        </div>
        <div style="margin-top:12px">
            <div style="font-size:12px; color:#666">Contact</div>
            <div>${off.contactNumber || 'N/A'}</div>
        </div>
        <div style="margin-top:12px">
            <div style="font-size:12px; color:#666">Address</div>
            <div>${off.Address || 'N/A'}</div>
        </div>
        <div style="margin-top:18px; text-align:right"><button class="btn-small btn-approve" onclick="approveDO(${off.DO_ID}, this)">Approve</button></div>
    `;
    $('#doViewTitle').text(off.DO_Name || 'Officer');
    $('#doViewBody').html(html);
    $('#doViewModal').show();
}

function approveDO(id, btn) {
    Swal.fire({ title: 'Approve officer?', text: 'Confirm approval and record approver.', icon: 'question', showCancelButton: true }).then(r => {
        if (!r.isConfirmed) return;
        $(btn).prop('disabled', true).text('Approving...');
        $.post('backend/approveDistributingOfficer.php', { officerID: id }, function(resp){
            if (resp && resp.success) {
                Swal.fire('Approved','Officer approved','success').then(()=> location.reload());
            } else {
                Swal.fire('Error', resp && resp.message ? resp.message : 'Failed','error');
                $(btn).prop('disabled', false).text('Approve');
            }
        }, 'json').fail(()=>{ Swal.fire('Error','Server error','error'); $(btn).prop('disabled', false).text('Approve'); });
    });
}

function confirmDelete(id, btn){
    Swal.fire({ title:'Delete officer?', text:'This action cannot be undone.', icon:'warning', showCancelButton:true }).then(r=>{ if(!r.isConfirmed) return; $(btn).prop('disabled',true).text('Deleting...'); $.post('backend/deleteDistributingOfficer.php',{officerID:id}, function(resp){ if(resp && resp.success){ Swal.fire('Deleted','Officer removed','success').then(()=> location.reload()); } else { Swal.fire('Error', resp && resp.message ? resp.message : 'Failed','error'); $(btn).prop('disabled',false).text('Delete'); } }, 'json').fail(()=>{ Swal.fire('Error','Server error','error'); $(btn).prop('disabled',false).text('Delete'); }); });
}
</script>

<?php $conn->close(); ?>

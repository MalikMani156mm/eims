<?php
require '../adminAuth.php';
require '../db.php';
?>

<div class="container">
    <div class="packages-table">
        <h3>📤 Issue Serialized Parts</h3>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
            <table class="table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom: 1px solid #eee;">
                        <th style="padding:12px">Part Name</th>
                        <th style="padding:12px">Batch</th>
                        <th style="padding:12px">Region</th>
                        <th style="padding:12px">Available Serials</th>
                        <th style="padding:12px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // List only parts that have serials available
                        $sql = "SELECT p.partName, p.batchName, p.regionID, COALESCE(r.regionName, 'Unknown') AS regionName,
                                    COUNT(CASE WHEN p.status = 'available' AND p.serialNumber IS NOT NULL THEN 1 END) AS availSerials
                                FROM parts p
                                LEFT JOIN regions r ON p.regionID = r.regionID
                                GROUP BY p.partName, p.batchName, p.regionID
                                HAVING availSerials > 0
                                ORDER BY p.partName";

                        $res = $conn->query($sql);
                        if ($res && $res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                $partName = htmlspecialchars($row['partName']);
                                $regionID = (int)$row['regionID'];
                                $regionName = htmlspecialchars($row['regionName']);
                                $avail = (int)$row['availSerials'];
                                echo "<tr>";
                                echo "<td style=\"padding:12px\">{$partName}</td>";
                                $batchEsc = htmlspecialchars($row['batchName']);
                                echo "<td style=\"padding:12px\">{$batchEsc}</td>";
                                echo "<td style=\"padding:12px\">{$regionName}</td>";
                                echo "<td style=\"padding:12px\">{$avail}</td>";
                                echo "<td style=\"padding:12px\"><button type=\"button\" class=\"btn btn-sm btn-primary issue-serials\" data-part=\"{$partName}\" data-region=\"{$regionID}\" data-batch=\"{$batchEsc}\">Issue</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="5" style="padding:12px">No serialized parts available</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Serial selection modal -->
<div id="issueSerialsModal" style="display:none; position: fixed; z-index: 9999; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.6); padding:20px; overflow:auto;">
    <div style="max-width:800px; margin:40px auto; background:white; border-radius:12px; overflow:hidden;">
        <div style="background:#2b6cb0; color:#fff; padding:16px; position:relative;">
            <h3 style="margin:0">Select Serials to Issue</h3>
            <button type="button" onclick="closeSerialModal()" style="position:absolute; right:12px; top:10px; background:rgba(255,255,255,0.2); border:0; color:#fff; width:36px; height:36px; border-radius:50%; cursor:pointer;">&times;</button>
        </div>
        <div style="padding:18px;">
            <div style="margin-bottom:8px;"><strong id="serialPartTitle">-</strong><div id="serialRegion" style="color:#666; font-size:13px"></div></div>
            <div style="max-height:320px; overflow:auto; border:1px solid #eee; padding:12px; border-radius:8px; font-family:monospace;">
                <div id="serialsList">Loading...</div>
            </div>

            <div style="margin-top:12px; display:flex; gap:12px; align-items:center;">
                <label style="margin:0">Issued To</label>
                <input id="serialIssuedTo" class="form-control" type="text" style="width:220px;" placeholder="Tech Team A">
                <label style="margin:0">Issued Product Serial</label>
                <input id="serialIssuedProductSerial" class="form-control" type="text" style="width:200px;" placeholder="Product serial number">
                <button id="selectAllSerials" class="btn btn-sm">Select All</button>
            </div>

            <div style="margin-top:16px; text-align:right;">
                <button id="confirmSerialIssue" class="btn btn-primary">Issue Selected</button>
                <button type="button" onclick="closeSerialModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeSerialModal() { $('#issueSerialsModal').fadeOut(150); $('body').css('overflow','auto'); }

    $(document).off('click', '.issue-serials').on('click', '.issue-serials', function() {
        const part = $(this).data('part');
        const region = $(this).data('region');
        const batch = $(this).data('batch');
        $('#serialPartTitle').text(part + (batch? ' — '+batch : ''));
        $('#serialRegion').text('Loading...');
        $('#serialsList').html('Loading...');
        $('#serialIssuedTo').val('');

        $.post('../eims/backend/getPartSerials.php', { partName: part, regionID: region }, function(resp) {
            if (!resp || !resp.success) { Swal.fire('Error','Failed to load serials','error'); return; }
            $('#serialRegion').text(resp.regionName || 'Unknown');
            const avail = resp.available || [];
            if (!avail.length) { $('#serialsList').html('<div>No available serials</div>'); } else {
                let html = '<div style="display:flex; flex-direction:column; gap:6px">';
                avail.forEach(s => {
                    const sn = s.serialNumber || '(no serial)';
                    const qty = parseInt(s.quantity)||0;
                    // for serialized items qty should be 1 per row, but handle qty>1
                    for (let i=0;i<Math.max(1,qty);i++) {
                        const id = 'serial_cb_'+Math.random().toString(36).slice(2,9);
                        html += '<label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" class="serial-select" data-serial="'+ (s.serialNumber||'') +'" id="'+id+'"> <span>'+ (s.serialNumber||'(no serial)') + (qty>1? ' × '+qty : '') + (s.createdAt? ' – '+s.createdAt : '') +'</span></label>';
                    }
                });
                html += '</div>';
                $('#serialsList').html(html);
            }
            $('body').css('overflow','hidden');
            $('#issueSerialsModal').fadeIn(150);
            // store context
            $('#issueSerialsModal').data('context', { partName: part, regionID: region, batchName: batch });
        }, 'json');
    });

    $('#selectAllSerials').on('click', function(e){ e.preventDefault(); $('.serial-select').prop('checked', true); });

    $('#confirmSerialIssue').on('click', function(){
        const ctx = $('#issueSerialsModal').data('context') || {};
        const selected = $('.serial-select:checked').map(function(){ return $(this).data('serial'); }).get();
        if (!selected.length) { Swal.fire('Error','Select at least one serial','error'); return; }
        const issuedTo = $('#serialIssuedTo').val().trim();
        const issuedProductSerial = $('#serialIssuedProductSerial').val().trim();
        if (!issuedTo) { Swal.fire('Error','"Issued To" is required','error'); return; }
        if (!issuedProductSerial) { Swal.fire('Error','"Issued Product Serial" is required','error'); return; }
        $.post('../eims/backend/issueSerialParts.php', { partName: ctx.partName, regionID: ctx.regionID, serials: JSON.stringify(selected), issuedTo: issuedTo, issuedProductSerialNumber: issuedProductSerial }, function(resp){
            if (!resp || !resp.success) { Swal.fire('Error', resp && resp.message?resp.message:'Issue failed','error'); return; }
            Swal.fire('Success', resp.message||'Issued','success');
            closeSerialModal();
            if (typeof loadContent === 'function') loadContent('issuePartsSerial');
        }, 'json').fail(function(){ Swal.fire('Error','Unable to contact server','error'); });
    });
</script>

<?php $conn->close(); ?>

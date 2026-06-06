<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
?>

<div class="container">
    <div class="packages-table">
        <h3>📤 Issue Parts to Technical Team</h3>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
            <table class="table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom: 1px solid #eee;">
                        <th style="padding:12px">Part Name</th>
                        <th style="padding:12px">Batch</th>
                        <th style="padding:12px">Region</th>
                        <th style="padding:12px">Available</th>
                        <th style="padding:12px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // List only aggregated rows (serialNumber IS NULL) that are available
                        $sql = "SELECT p.partName, p.batchName, p.regionID, COALESCE(r.regionName, 'Unknown') AS regionName,
                                    SUM(CASE WHEN p.status = 'available' THEN p.quantity ELSE 0 END) AS availableCount
                                FROM parts p
                                LEFT JOIN regions r ON p.regionID = r.regionID
                                WHERE p.serialNumber IS NULL
                                GROUP BY p.partName, p.batchName, p.regionID
                                HAVING availableCount > 0
                                ORDER BY p.partName";

                        $res = $conn->query($sql);
                        if ($res && $res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                $partName = htmlspecialchars($row['partName']);
                                $regionID = (int)$row['regionID'];
                                $regionName = htmlspecialchars($row['regionName']);
                                $available = (int)$row['availableCount'];
                                echo "<tr>";
                                echo "<td style=\"padding:12px\">{$partName}</td>";
                                $batchNameEsc = htmlspecialchars($row['batchName']);
                                echo "<td style=\"padding:12px\">{$batchNameEsc}</td>";
                                echo "<td style=\"padding:12px\">{$regionName}</td>";
                                echo "<td style=\"padding:12px\">{$available}</td>";
                                echo "<td style=\"padding:12px\"><button type=\"button\" class=\"btn btn-sm btn-primary issue-part\" data-part=\"{$partName}\" data-region=\"{$regionID}\" data-batch=\"{$batchNameEsc}\">Issue</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="5" style="padding:12px">No parts available for issuing</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Issue Modal -->
<div id="issuePartsModal" style="display:none; position: fixed; z-index: 9999; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.6); padding:20px; overflow:auto;">
    <div style="max-width:600px; margin:40px auto; background:white; border-radius:12px; overflow:hidden;">
        <div style="background:#4a5568; color:#fff; padding:16px; position:relative;">
            <h3 style="margin:0">Issue Part</h3>
            <button type="button" onclick="closeIssueModal()" style="position:absolute; right:12px; top:10px; background:rgba(255,255,255,0.2); border:0; color:#fff; width:36px; height:36px; border-radius:50%; cursor:pointer;">&times;</button>
        </div>
        <div style="padding:18px;">
            <div style="margin-bottom:12px;"><strong id="issuePartName">-</strong><div id="issueRegionName" style="color:#666; font-size:13px"></div></div>

            <div class="form-group" style="margin-bottom:12px;">
                <label>Available Quantity</label>
                <div id="issueAvailable" style="font-weight:600">0</div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="issueQty">Quantity to issue</label>
                <input id="issueQty" class="form-control" type="number" min="1" value="1" style="width:120px;">
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="issuedTo">Issued To (optional)</label>
                <input id="issuedTo" class="form-control" type="text" placeholder="e.g., Tech Team A">
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="issuedProductSerial">Issued Product Serial Number</label>
                <input id="issuedProductSerial" class="form-control" type="text" placeholder="Enter product serial number">
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:16px;">
                <button id="confirmIssueBtn" class="btn btn-primary">Issue</button>
                <button type="button" onclick="closeIssueModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeIssueModal() {
        $('#issuePartsModal').fadeOut(150);
        $('body').css('overflow','auto');
    }

    var _issueContext = {};

    $(document).off('click', '.issue-part').on('click', '.issue-part', function() {
        const part = $(this).data('part');
        const region = $(this).data('region');
        const batch = $(this).data('batch');

        _issueContext = { partName: part, regionID: region, batchName: batch };

        // Fetch available count for this row to ensure latest
        $.post('../backend/getPartSerials.php', { partName: part, regionID: region }, function(resp) {
            if (!resp || !resp.success) {
                Swal.fire('Error', resp && resp.message ? resp.message : 'Failed to load availability', 'error');
                return;
            }

            // Sum available quantities for NULL-serial rows
            const avail = resp.available || [];
            const totalAvail = avail.reduce((acc,s) => acc + (parseInt(s.quantity)||0), 0);

            $('#issuePartName').text(part + (batch ? ' — ' + batch : ''));
            $('#issueRegionName').text(resp.regionName || 'Unknown');
            $('#issueAvailable').text(totalAvail);
            $('#issueQty').val( Math.max(1, Math.min(1, totalAvail)) );
            $('#issueQty').attr('max', totalAvail);

            _issueContext.available = totalAvail;

            $('body').css('overflow','hidden');
            $('#issuePartsModal').fadeIn(150);
        }, 'json').fail(function() {
            Swal.fire('Error', 'Unable to contact server', 'error');
        });
    });

    $('#confirmIssueBtn').on('click', function() {
        const qty = parseInt($('#issueQty').val() || '0');
        const max = parseInt(_issueContext.available || 0);
        if (!qty || qty < 1) {
            Swal.fire('Error', 'Enter a valid quantity', 'error');
            return;
        }
        if (qty > max) {
            Swal.fire('Error', 'Quantity cannot exceed available ('+max+')', 'error');
            return;
        }

        const issuedTo = $('#issuedTo').val().trim();
        const issuedProductSerial = $('#issuedProductSerial').val().trim();

        if (!issuedTo) { Swal.fire('Error','"Issued To" is required','error'); return; }
        if (!issuedProductSerial) { Swal.fire('Error','"Issued Product Serial Number" is required','error'); return; }

        // Send to backend
        $.post('../backend/issueParts.php', { partName: _issueContext.partName, regionID: _issueContext.regionID, batchName: _issueContext.batchName, quantity: qty, issuedTo: issuedTo, issuedProductSerialNumber: issuedProductSerial }, function(resp) {
            if (!resp || !resp.success) {
                Swal.fire('Error', resp && resp.message ? resp.message : 'Issue failed', 'error');
                return;
            }

            Swal.fire('Success', resp.message || 'Issued', 'success');
            closeIssueModal();
            // Reload content area to refresh list
            if (typeof loadContent === 'function') loadContent('issueParts');
        }, 'json').fail(function() {
            Swal.fire('Error', 'Unable to contact server', 'error');
        });
    });
</script>

<!-- Issue Logs -->
<div style="margin-top:24px; background:white; padding:16px; border-radius:8px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
    <h4 style="margin-top:0">Issue Log (recent)</h4>
    <div style="overflow:auto">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid #eee;">
                    <th style="padding:8px">#</th>
                    <th style="padding:8px">Part</th>
                    <th style="padding:8px">Batch</th>
                    <th style="padding:8px">Region</th>
                    <th style="padding:8px">Qty</th>
                    <th style="padding:8px">Issued To</th>
                    <th style="padding:8px">Issued By</th>
                    <th style="padding:8px">When</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $logSql = "SELECT l.*, COALESCE(r.regionName,'Unknown') AS regionName FROM issue_parts_log l LEFT JOIN regions r ON l.regionID = r.regionID ORDER BY l.issuedAt DESC LIMIT 100";
                    $logRes = $conn->query($logSql);
                    if ($logRes && $logRes->num_rows > 0) {
                        $counter = 1;
                        while ($lr = $logRes->fetch_assoc()) {
                            $pn = htmlspecialchars($lr['partName']);
                            $bn = htmlspecialchars($lr['batchName']);
                            $rn = htmlspecialchars($lr['regionName']);
                            $q = (int)$lr['quantityIssued'];
                            $it = htmlspecialchars($lr['issuedTo'] ?? '');
                            $ibn = htmlspecialchars($lr['issuedByName'] ?? '');
                            $when = htmlspecialchars($lr['issuedAt']);
                            echo "<tr>";
                            echo "<td style=\"padding:8px\">{$counter}</td>";
                            echo "<td style=\"padding:8px\">{$pn}</td>";
                            echo "<td style=\"padding:8px\">{$bn}</td>";
                            echo "<td style=\"padding:8px\">{$rn}</td>";
                            echo "<td style=\"padding:8px\">{$q}</td>";
                            echo "<td style=\"padding:8px\">{$it}</td>";
                            echo "<td style=\"padding:8px\">{$ibn}</td>";
                            echo "<td style=\"padding:8px\">{$when}</td>";
                            echo "</tr>";
                            $counter++;
                        }
                    } else {
                        echo '<tr><td colspan="10" style="padding:12px">No logs found</td></tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php $conn->close(); ?>

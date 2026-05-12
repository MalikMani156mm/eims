<?php
require '../adminAuth.php';
require '../db.php';
?>

<div class="container">
    <div class="packages-table">
        <h3>📦 Parts Inventory</h3>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
            <table class="table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom: 1px solid #eee;">
                        <th style="padding:12px">Part Name</th>
                        <th style="padding:12px">Batch</th>
                        <th style="padding:12px">Region</th>
                        <th style="padding:12px">Total</th>
                        <th style="padding:12px">Available</th>
                        <th style="padding:12px">Used</th>
                        <th style="padding:12px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.partName, p.batchName, p.regionID, COALESCE(r.regionName, 'Unknown') AS regionName,
                                                                SUM(CASE WHEN p.status = 'available' THEN IF(p.quantity > 0, p.quantity, 1) ELSE 0 END) AS availableCount,
                                                                SUM(CASE WHEN p.status = 'used' THEN IF(p.quantity > 0, p.quantity, 1) ELSE 0 END) AS usedCount
                                                        FROM parts p
                                                        LEFT JOIN regions r ON p.regionID = r.regionID
                                                        GROUP BY p.partName, p.batchName, p.regionID
                                                        ORDER BY p.partName";

                    $res = $conn->query($sql);
                    if ($res && $res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            $partName = htmlspecialchars($row['partName']);
                            $regionID = (int)$row['regionID'];
                            $regionName = htmlspecialchars($row['regionName']);
                            $available = (int)$row['availableCount'];
                            $used = (int)$row['usedCount'];
                            $total = $available + $used;
                            echo "<tr>";
                            echo "<td style=\"padding:12px\">{$partName}</td>";
                            $batchNameEsc = htmlspecialchars($row['batchName']);
                            echo "<td style=\"padding:12px\">{$batchNameEsc}</td>";
                            echo "<td style=\"padding:12px\">{$regionName}</td>";
                            echo "<td style=\"padding:12px\">" . $total . "</td>";
                            echo "<td style=\"padding:12px\">{$available}</td>";
                            echo "<td style=\"padding:12px\">{$used}</td>";
                            echo "<td style=\"padding:12px\"><button type=\"button\" class=\"btn btn-sm btn-primary view-serials\" data-part=\"{$partName}\" data-region=\"{$regionID}\">View Serials</button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="7" style="padding:12px">No parts found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Serial List Modal -->
<div id="partsSerialModal" style="display:none; position: fixed; z-index: 9999; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.6); padding:20px; overflow:auto;">
    <div style="max-width:800px; margin:40px auto; background:white; border-radius:12px; overflow:hidden;">
        <div style="background:#667eea; color:#fff; padding:16px; position:relative;">
            <h3 style="margin:0">Serial Numbers</h3>
            <button type="button" onclick="closePartsModal()" style="position:absolute; right:12px; top:10px; background:rgba(255,255,255,0.2); border:0; color:#fff; width:36px; height:36px; border-radius:50%; cursor:pointer;">&times;</button>
        </div>
        <div style="padding:18px;">
            <div style="margin-bottom:16px;">
                <strong id="modalPartName">-</strong>
                <div id="modalRegionName" style="color:#666; font-size:13px"></div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div style="border:1px solid #eee; padding:12px; border-radius:8px;">
                    <div style="font-weight:600; margin-bottom:8px">Available (<span id="availCount">0</span>)</div>
                    <div id="availList" style="max-height:300px; overflow:auto; font-family:monospace; font-size:13px; color:#333;"></div>
                </div>
                <div style="border:1px solid #eee; padding:12px; border-radius:8px;">
                    <div style="font-weight:600; margin-bottom:8px">Used (<span id="usedCount">0</span>)</div>
                    <div id="usedList" style="max-height:300px; overflow:auto; font-family:monospace; font-size:13px; color:#333;"></div>
                </div>
            </div>

            <div style="margin-top:16px; text-align:right;">
                <button type="button" onclick="closePartsModal()" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function closePartsModal() {
        $('#partsSerialModal').fadeOut(150);
        $('body').css('overflow', 'auto');
    }

    // Ensure we don't attach multiple handlers if this file is re-loaded via AJAX
    $(document).off('click', '.view-serials').on('click', '.view-serials', function() {
        const part = $(this).data('part');
        const region = $(this).data('region');
        $('#modalPartName').text(part);
        $('#modalRegionName').text('Loading region...');
        $('#availList').empty();
        $('#usedList').empty();
        $('#availCount').text('0');
        $('#usedCount').text('0');

        $.post('../eims/backend/getPartSerials.php', {
            partName: part,
            regionID: region
        }, function(resp) {
            if (!resp || !resp.success) {
                Swal.fire('Error', resp && resp.message ? resp.message : 'Failed to load serials', 'error');
                return;
            }

            $('#modalRegionName').text(resp.regionName || 'Unknown');

            const avail = resp.available || [];
            const used = resp.used || [];

            // Sum quantities (handles NULL-serial rows where quantity > 1)
            const totalAvail = avail.reduce((acc, s) => acc + (parseInt(s.quantity) || 0), 0);
            const totalUsed = used.reduce((acc, s) => acc + (parseInt(s.quantity) || 0), 0);

            $('#availCount').text(totalAvail);
            $('#usedCount').text(totalUsed);

            if (avail.length === 0) {
                $('#availList').html('<div style="color:#777">No available serials</div>');
            } else {
                avail.forEach(s => {
                    const qty = parseInt(s.quantity) || 0;
                    if (!s.serialNumber) {
                        // Row without serial number (shows quantity)
                        $('#availList').append('<div>' + ("(no serial)") + (qty > 1 ? ' × ' + qty : '') + (s.createdAt ? ' — ' + s.createdAt : '') + '</div>');
                    } else {
                        $('#availList').append('<div>' + s.serialNumber + (qty > 1 ? ' × ' + qty : '') + (s.createdAt ? ' — ' + s.createdAt : '') + '</div>');
                    }
                });
            }

            if (used.length === 0) {
                $('#usedList').html('<div style="color:#777">No used serials</div>');
            } else {
                used.forEach(s => {
                    const qty = parseInt(s.quantity) || 0;
                    if (!s.serialNumber) {
                        $('#usedList').append('<div>' + ("(no serial)") + (qty > 1 ? ' × ' + qty : '') + (s.issuedDate ? ' — issued: ' + s.issuedDate : (s.createdAt ? ' — ' + s.createdAt : '')) + '</div>');
                    } else {
                        $('#usedList').append('<div>' + s.serialNumber + (qty > 1 ? ' × ' + qty : '') + (s.issuedDate ? ' — issued: ' + s.issuedDate : (s.createdAt ? ' — ' + s.createdAt : '')) + '</div>');
                    }
                });
            }

            $('body').css('overflow', 'hidden');
            $('#partsSerialModal').fadeIn(150);
        }, 'json').fail(function() {
            Swal.fire('Error', 'Unable to contact server', 'error');
        });
    });
</script>

<?php
$conn->close();
?>
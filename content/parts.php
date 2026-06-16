<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$brands = [];
$brandsRes = $conn->query("SELECT brandID, brandName FROM brands ORDER BY brandName ASC");
if ($brandsRes) {
    while ($r = $brandsRes->fetch_assoc()) {
        $brands[] = $r;
    }
}

$sizes = [];
$sizesRes = $conn->query("SELECT sizeID, sizeName FROM sizes ORDER BY sizeName ASC");
if ($sizesRes) {
    while ($r = $sizesRes->fetch_assoc()) {
        $sizes[] = $r;
    }
}

$types = [];
$typesRes = $conn->query("SELECT typeID, typeName FROM types WHERE status = 0 ORDER BY typeName ASC");
if ($typesRes) {
    while ($r = $typesRes->fetch_assoc()) {
        $types[] = $r;
    }
}

$regions = [];
$regionsRes = $conn->query("SELECT regionID, regionName FROM regions ORDER BY regionName ASC");
if ($regionsRes) {
    while ($r = $regionsRes->fetch_assoc()) {
        $regions[] = $r;
    }
}
?>

<div class="container">
    <div class="packages-table">
        <h3>📦 Parts Inventory</h3>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
            <div style="display:flex; gap:12px; align-items:center; margin-bottom:12px;">
                <div style="min-width:220px;">
                    <select id="filterBrandParts" class="form-control">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?php echo $b['brandID']; ?>"><?php echo htmlspecialchars($b['brandName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1;">
                    <input id="searchPartName" type="search" class="form-control" placeholder="Search part name..." />
                </div>
            </div>
            <table class="table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom: 1px solid #eee;">
                        <th style="padding:12px">Part Name</th>
                        <th style="padding:12px">Batch</th>
                        <th style="padding:12px">Brand</th>
                        <th style="padding:12px">Tonnage / Size</th>
                        <th style="padding:12px">Type</th>
                        <th style="padding:12px">Region</th>
                        <th style="padding:12px">Total</th>
                        <th style="padding:12px">Available</th>
                        <th style="padding:12px">Used</th>
                        <th style="padding:12px">Actions</th>
                    </tr>
                </thead>
                <tbody id="partsTableBody">
                    <?php
                    $sql = "SELECT p.partName, p.batchName, b.brandName, p.brandID, p.sizeID, COALESCE(s.sizeName, 'N/A') AS sizeName,
                                   p.typeID, COALESCE(t.typeName, 'N/A') AS typeName, p.regionID, COALESCE(r.regionName, 'Unknown') AS regionName,
                                   SUM(CASE WHEN p.status = 'available' THEN IF(p.quantity > 0, p.quantity, 1) ELSE 0 END) AS availableCount,
                                   SUM(CASE WHEN p.status = 'used' THEN IF(p.quantity > 0, p.quantity, 1) ELSE 0 END) AS usedCount
                            FROM parts p
                            LEFT JOIN regions r ON p.regionID = r.regionID
                            LEFT JOIN brands b ON p.brandID = b.brandID
                            LEFT JOIN sizes s ON p.sizeID = s.sizeID
                            LEFT JOIN types t ON p.typeID = t.typeID
                            GROUP BY p.partName, p.batchName, p.regionID, p.brandID, p.sizeID, p.typeID
                            ORDER BY p.partName";

                    $res = $conn->query($sql);
                    if ($res && $res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            $partNameRaw = $row['partName'];
                            $partName = htmlspecialchars($partNameRaw);
                            $batchNameRaw = $row['batchName'];
                            $batchNameEsc = htmlspecialchars($batchNameRaw);
                            $regionID = (int)$row['regionID'];
                            $brandID = (int)($row['brandID'] ?? 0);
                            $sizeID = (int)($row['sizeID'] ?? 0);
                            $typeID = (int)($row['typeID'] ?? 0);
                            $sizeName = htmlspecialchars(trim($row['sizeName']));
                            $typeName = htmlspecialchars(trim($row['typeName']));
                            $regionName = htmlspecialchars($row['regionName']);
                            $brandNameEsc = htmlspecialchars($row['brandName']);
                            $available = (int)$row['availableCount'];
                            $used = (int)$row['usedCount'];
                            $total = $available + $used;

                            echo '<tr class="parts-row" data-part="' . $partName . '" data-batch="' . $batchNameEsc . '" data-region="' . $regionID . '" data-brand="' . $brandID . '" data-size="' . $sizeID . '" data-type="' . $typeID . '">';
                            echo '<td style="padding:12px">' . $partName . '</td>';
                            echo '<td style="padding:12px">' . $batchNameEsc . '</td>';
                            echo '<td style="padding:12px">' . $brandNameEsc . '</td>';
                            echo '<td style="padding:12px">' . $sizeName . '</td>';
                            echo '<td style="padding:12px">' . $typeName . '</td>';
                            echo '<td style="padding:12px">' . $regionName . '</td>';
                            echo '<td style="padding:12px">' . $total . '</td>';
                            echo '<td style="padding:12px">' . $available . '</td>';
                            echo '<td style="padding:12px">' . $used . '</td>';
                            echo '<td style="padding:12px; white-space:nowrap;">';
                            echo '<button type="button" class="btn btn-sm btn-primary view-serials" data-part="' . $partName . '" data-region="' . $regionID . '" data-size="' . $sizeID . '" data-type="' . $typeID . '" style="margin-right:6px;">View Serials</button>';
                            echo '<button type="button" class="btn btn-sm btn-secondary edit-part-group"'
                                . ' data-part="' . $partName . '"'
                                . ' data-batch="' . $batchNameEsc . '"'
                                . ' data-region="' . $regionID . '"'
                                . ' data-brand="' . $brandID . '"'
                                . ' data-size="' . $sizeID . '"'
                                . ' data-type="' . $typeID . '"'
                                . ' data-total="' . $total . '"'
                                . ' data-available="' . $available . '"'
                                . ' data-used="' . $used . '">Edit</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="10" style="padding:12px">No parts found</td></tr>';
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

<!-- Edit Part Group Modal -->
<div id="editPartModal" style="display:none; position: fixed; z-index: 9999; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.6); padding:20px; overflow:auto;">
    <div style="max-width:640px; margin:40px auto; background:white; border-radius:12px; overflow:hidden;">
        <div style="background:#764ba2; color:#fff; padding:16px; position:relative;">
            <h3 style="margin:0">Edit Part Group</h3>
            <button type="button" onclick="closeEditPartModal()" style="position:absolute; right:12px; top:10px; background:rgba(255,255,255,0.2); border:0; color:#fff; width:36px; height:36px; border-radius:50%; cursor:pointer;">&times;</button>
        </div>
        <form id="editPartForm" style="padding:20px;">
            <input type="hidden" id="editOldBatchName" name="oldBatchName">
            <input type="hidden" id="editOldRegionID" name="oldRegionID">
            <input type="hidden" id="editOldBrandID" name="oldBrandID">
            <input type="hidden" id="editOldSizeID" name="oldSizeID">
            <input type="hidden" id="editOldTypeID" name="oldTypeID">

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label" style="color:#333;">Part Name</label>
                <input type="text" id="editPartName" name="partName" class="form-control" readonly style="background:#f5f5f5;">
            </div>

            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:16px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="color:#333;">Total Qty</label>
                    <input type="text" id="editTotalQty" class="form-control" readonly style="background:#f5f5f5;">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="color:#333;">Available</label>
                    <input type="text" id="editAvailableQty" class="form-control" readonly style="background:#f5f5f5;">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="color:#333;">Used</label>
                    <input type="text" id="editUsedQty" class="form-control" readonly style="background:#f5f5f5;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="editBatchName" class="form-label" style="color:#333;">Batch Name *</label>
                <input type="text" id="editBatchName" name="batchName" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="editBrandID" class="form-label" style="color:#333;">Brand *</label>
                <select id="editBrandID" name="brandID" class="form-control" required>
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo $b['brandID']; ?>"><?php echo htmlspecialchars($b['brandName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="editSizeID" class="form-label" style="color:#333;">Tonnage / Size *</label>
                <select id="editSizeID" name="sizeID" class="form-control" required>
                    <option value="">Select Size</option>
                    <?php foreach ($sizes as $s): ?>
                        <option value="<?php echo $s['sizeID']; ?>"><?php echo htmlspecialchars($s['sizeName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="editTypeID" class="form-label" style="color:#333;">Type *</label>
                <select id="editTypeID" name="typeID" class="form-control" required>
                    <option value="">Select Type</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?php echo $t['typeID']; ?>"><?php echo htmlspecialchars($t['typeName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label for="editRegionID" class="form-label" style="color:#333;">Region *</label>
                <select id="editRegionID" name="regionID" class="form-control" required>
                    <option value="">Select Region</option>
                    <?php foreach ($regions as $r): ?>
                        <option value="<?php echo $r['regionID']; ?>"><?php echo htmlspecialchars($r['regionName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeEditPartModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closePartsModal() {
        $('#partsSerialModal').fadeOut(150);
        $('body').css('overflow', 'auto');
    }

    function closeEditPartModal() {
        $('#editPartModal').fadeOut(150);
        $('body').css('overflow', 'auto');
    }

    $(document).off('click', '.view-serials').on('click', '.view-serials', function() {
        const part = $(this).data('part');
        const region = $(this).data('region');
        const sizeID = $(this).data('size');
        const typeID = $(this).data('type');
        $('#modalPartName').text(part);
        $('#modalRegionName').text('Loading region...');
        $('#availList').empty();
        $('#usedList').empty();
        $('#availCount').text('0');
        $('#usedCount').text('0');

        $.post('../backend/getPartSerials.php', {
            partName: part,
            regionID: region,
            sizeID: sizeID,
            typeID: typeID
        }, function(resp) {
            if (!resp || !resp.success) {
                Swal.fire('Error', resp && resp.message ? resp.message : 'Failed to load serials', 'error');
                return;
            }

            $('#modalRegionName').text(resp.regionName || 'Unknown');

            const avail = resp.available || [];
            const used = resp.used || [];
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
                        $('#availList').append('<div>' + '(no serial)' + (qty > 1 ? ' × ' + qty : '') + (s.createdAt ? ' — ' + s.createdAt : '') + '</div>');
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
                        $('#usedList').append('<div>' + '(no serial)' + (qty > 1 ? ' × ' + qty : '') + (s.issuedDate ? ' — issued: ' + s.issuedDate : (s.createdAt ? ' — ' + s.createdAt : '')) + '</div>');
                    } else {
                        $('#usedList').append('<div>Serial Number: ' + s.serialNumber + (qty > 1 ? ' × ' + qty : '') + (s.issuedDate ? ' — issued: ' + s.issuedDate : (s.createdAt ? ' — ' + s.createdAt : '')) + '</div>');
                    }
                });
            }

            $('body').css('overflow', 'hidden');
            $('#partsSerialModal').fadeIn(150);
        }, 'json').fail(function() {
            Swal.fire('Error', 'Unable to contact server', 'error');
        });
    });

    $(document).off('click', '.edit-part-group').on('click', '.edit-part-group', function() {
        const btn = $(this);

        $('#editPartName').val(btn.data('part'));
        $('#editOldBatchName').val(btn.data('batch'));
        $('#editOldRegionID').val(btn.data('region'));
        $('#editOldBrandID').val(btn.data('brand'));
        $('#editOldSizeID').val(btn.data('size'));
        $('#editOldTypeID').val(btn.data('type'));

        $('#editBatchName').val(btn.data('batch'));
        $('#editBrandID').val(btn.data('brand'));
        $('#editSizeID').val(btn.data('size'));
        $('#editTypeID').val(btn.data('type'));
        $('#editRegionID').val(btn.data('region'));

        $('#editTotalQty').val(btn.data('total'));
        $('#editAvailableQty').val(btn.data('available'));
        $('#editUsedQty').val(btn.data('used'));

        $('body').css('overflow', 'hidden');
        $('#editPartModal').fadeIn(150);
    });

    $(document).off('submit', '#editPartForm').on('submit', '#editPartForm', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'backend/updatePartGroup.php',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: resp.message,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(function() {
                        closeEditPartModal();
                        if (typeof loadContent === 'function') {
                            loadContent('parts');
                        }
                    });
                } else {
                    Swal.fire('Error', resp.message || 'Failed to update part group', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to contact server', 'error');
            }
        });
    });

    function filterParts() {
        const brand = $('#filterBrandParts').val();
        const q = ($('#searchPartName').val() || '').toLowerCase().trim();
        let visible = 0;
        $('.parts-row').each(function() {
            const r = $(this);
            const rowBrand = (r.data('brand') || '').toString();
            const part = (r.data('part') || '').toLowerCase();
            let show = true;
            if (brand && rowBrand !== brand) show = false;
            if (q && part.indexOf(q) === -1) show = false;
            if (show) { r.show(); visible++; } else { r.hide(); }
        });
        if (visible === 0) {
            if ($('#noPartsRow').length === 0) {
                $('#partsTableBody').append('<tr id="noPartsRow"><td colspan="10" style="padding:12px">No parts match the filter</td></tr>');
            }
        } else {
            $('#noPartsRow').remove();
        }
    }

    $('#filterBrandParts').on('change', filterParts);
    $('#searchPartName').on('input', filterParts);
</script>

<?php
$conn->close();
?>

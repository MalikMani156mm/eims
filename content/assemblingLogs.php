<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$regions = [];
$regionsResult = $conn->query("SELECT regionID, regionName FROM regions ORDER BY regionName ASC");
if ($regionsResult) {
    while ($row = $regionsResult->fetch_assoc()) {
        $regions[] = $row;
    }
}

$products = [];
$regionIDToUse = isset($regionID) ? intval($regionID) : 0;
$whereClause = 'WHERE p.status = 0';
if (isset($adminRole) && $adminRole !== 'superadmin') {
    $whereClause = "WHERE p.status = 0 AND p.regionID = " . $regionIDToUse;
}

$sql = "
    SELECT
        p.*,
        b.brandName,
        c.categoryName,
        col.colorName,
        m.modelName,
        s.sizeName,
        r.regionName
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoriesID
    LEFT JOIN brands b ON p.brandID = b.brandID
    LEFT JOIN colors col ON p.colorID = col.colorID
    LEFT JOIN models m ON p.modelID = m.modelID
    LEFT JOIN sizes s ON p.sizeID = s.sizeID
    LEFT JOIN regions r ON p.regionID = r.regionID
    $whereClause
    ORDER BY p.createdAt DESC
";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="container">
    <div class="packages-table">
        <h3>📝 Assembling Logs</h3>

        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; color: white;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; text-align: center;">
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="totalAssemblingLogs"><?php echo count($products); ?></div>
                    <div style="font-size: 14px; opacity: 0.9;">Pending Assembly</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;">
                        <?php
                        $totalQty = 0;
                        foreach ($products as $p) {
                            $totalQty += intval($p['quantity']);
                        }
                        echo $totalQty;
                        ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Quantity</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;">
                        RS <?php
                        $totalCost = 0;
                        foreach ($products as $p) {
                            $totalCost += (intval($p['quantity']) * floatval($p['cost']));
                        }
                        echo number_format($totalCost, 2);
                        ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Cost</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Color</th>
                        <th>Model</th>
                        <th>Tonnage</th>
                        <th>Region</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Cost/Unit</th>
                        <th>Issued On</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="assemblingLogsTableBody">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="13" class="text-center">No pending assembling logs found</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $serial = 1;
                        foreach ($products as $product):
                        ?>
                            <tr id="assembling-row-<?php echo $product['productID']; ?>">
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($product['productName']); ?></td>
                                <td><?php echo htmlspecialchars($product['categoryName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['brandName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['colorName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['modelName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['sizeName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['regionName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['batchNumber']); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td><strong>RS <?php echo number_format($product['cost'], 2); ?></strong></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($product['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-primary" style="padding: 8px 12px; margin-right: 4px;" onclick='viewAssemblingProduct(<?php echo json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>View</button>
                                    <button class="btn btn-info" style="padding: 8px 12px; margin-right: 4px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none;" onclick="printAssemblingProduct(<?php echo intval($product['productID']); ?>)">Print</button>
                                    <button class="btn btn-success" style="padding: 8px 12px; margin-right: 4px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none;" onclick='confirmAssembleProduct(<?php echo intval($product['productID']); ?>, <?php echo json_encode($product['productName'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, <?php echo intval($product['regionID']); ?>, <?php echo json_encode($product['regionName'] ?? 'N/A', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Assembled</button>
                                    <button class="btn btn-danger" style="padding: 8px 12px; background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); border: none;" onclick='confirmDeclineAssemblingProduct(<?php echo intval($product['productID']); ?>, <?php echo json_encode($product['productName'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Decline</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="viewAssemblingModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: white; padding: 20px; border-bottom: 2px solid #f0f0f0; border-radius: 16px 16px 0 0; z-index: 10;">
            <h2 style="margin: 0; color: #667eea;">Assembling Log Details</h2>
            <button onclick="closeAssemblingViewModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <div id="assemblingDetailsContent" style="padding: 20px;"></div>
        <div style="background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeAssemblingViewModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<script>
    var assemblingRegions = <?php echo json_encode($regions); ?>;

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function viewAssemblingProduct(product) {
        const totalCost = (product.quantity * product.cost).toFixed(2);
        const detailsHtml = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Product Name</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.productName || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Category</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.categoryName || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Brand</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.brandName || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Color</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.colorName || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Model</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.modelName || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Tonnage</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.sizeName || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Batch Number</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.batchNumber || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Region</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${escapeHtml(product.regionName || 'N/A')}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Quantity</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${product.quantity || '0'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Cost Per Unit</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">RS ${parseFloat(product.cost || 0).toFixed(2)}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Total Cost</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #4caf50; font-size: 18px; font-weight: 600;">RS ${parseFloat(totalCost || 0).toFixed(2)}</p>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Issued On</label>
                    <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; color: #333;">${new Date(product.createdAt).toLocaleString()}</p>
                </div>
                <div style="margin-top: 20px;">
                    <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">Description</label>
                    <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; min-height: 60px; color: #333;">${escapeHtml(product.description || 'No description available')}</p>
                </div>
            </div>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 12px; border: 1px solid #e0e0e0;">
                <h4 style="margin: 0 0 15px 0; color: #667eea;">🏷️ Serial Numbers</h4>
                <div id="assemblingSerialsList">
                    <p style="color: #666; margin: 0; text-align: center;">Loading serial numbers...</p>
                </div>
            </div>
        `;

        $('#assemblingDetailsContent').html(detailsHtml);
        $('#viewAssemblingModal').fadeIn(300);
        $('body').css('overflow', 'hidden');

        $.ajax({
            url: 'backend/getProductSerials.php',
            type: 'GET',
            data: { productID: product.productID },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#assemblingSerialsList').html(renderAssemblingSerials(response.data));
                } else {
                    $('#assemblingSerialsList').html('<p style="color: #e53935; margin: 0; text-align: center;">' + escapeHtml(response.message || 'Failed to load serial numbers') + '</p>');
                }
            },
            error: function() {
                $('#assemblingSerialsList').html('<p style="color: #e53935; margin: 0; text-align: center;">Failed to load serial numbers</p>');
            }
        });
    }

    function renderAssemblingSerials(serials) {
        if (!serials || serials.length === 0) {
            return '<p style="color: #666; margin: 0; text-align: center;">No serial numbers found for this product</p>';
        }

        let html = `
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <th style="padding: 12px; text-align: left;">#</th>
                            <th style="padding: 12px; text-align: left;">Serial Number</th>
                            <th style="padding: 12px; text-align: left;">Batch</th>
                            <th style="padding: 12px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        serials.forEach(function(serial, index) {
            html += `
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 12px;">${index + 1}</td>
                    <td style="padding: 12px;"><code style="background:#f0f0f0; padding:4px 8px; border-radius:4px;">${escapeHtml(serial.serialNumber)}</code></td>
                    <td style="padding: 12px;">${escapeHtml(serial.batchNumber || 'N/A')}</td>
                    <td style="padding: 12px; text-align:center; text-transform:capitalize;">${escapeHtml(serial.status || 'available')}</td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        return html;
    }

    function closeAssemblingViewModal() {
        $('#viewAssemblingModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    }

    function buildRegionSelectOptions(selectedRegionID) {
        let options = '<option value="">Select Region</option>';
        assemblingRegions.forEach(function(region) {
            const selected = parseInt(region.regionID, 10) === parseInt(selectedRegionID, 10) ? ' selected' : '';
            options += `<option value="${region.regionID}"${selected}>${escapeHtml(region.regionName)}</option>`;
        });
        return options;
    }

    function printAssemblingProduct(productID) {
        window.open('backend/generateAssemblingPrint.php?productID=' + productID, '_blank');
    }

    function confirmDeclineAssemblingProduct(productID, productName) {
        Swal.fire({
            title: 'Decline Assembling Log?',
            html: `This will permanently remove <strong>${escapeHtml(productName)}</strong> and restore all issued parts and gases back to stock.<br><br>Serial numbers will become available for other products.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Decline',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: 'backend/declineAssemblingProduct.php',
                type: 'POST',
                data: { productID: productID },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Declined',
                            text: response.message || 'Assembling log declined successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            loadContent('assemblingLogs');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: response.message || 'Could not decline assembling log'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while declining the assembling log'
                    });
                }
            });
        });
    }

    function confirmAssembleProduct(productID, productName, currentRegionID, currentRegionName) {
        Swal.fire({
            title: 'Mark as Assembled?',
            html: `Are you sure you want to mark <strong>${escapeHtml(productName)}</strong> as assembled?<br><br>This will move it to inventory.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#11998e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Assemble',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            Swal.fire({
                title: 'Change Region?',
                html: `Current region: <strong>${escapeHtml(currentRegionName)}</strong><br><br>Do you want to change the region for this product?`,
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Yes, change region',
                denyButtonText: 'No, keep current region',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#667eea',
                denyButtonColor: '#11998e'
            }).then(function(regionResult) {
                if (regionResult.isDenied) {
                    markProductAsAssembled(productID, 0, currentRegionID);
                    return;
                }

                if (regionResult.isConfirmed) {
                    Swal.fire({
                        title: 'Select New Region',
                        html: `
                            <label for="assembleRegionSelect" style="display:block; text-align:left; margin-bottom:8px; font-weight:600;">Region</label>
                            <select id="assembleRegionSelect" class="swal2-input" style="width:100%; padding:10px; margin:0;">
                                ${buildRegionSelectOptions(currentRegionID)}
                            </select>
                        `,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Confirm Assemble',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#11998e',
                        preConfirm: function() {
                            const selectedRegion = document.getElementById('assembleRegionSelect').value;
                            if (!selectedRegion) {
                                Swal.showValidationMessage('Please select a region');
                                return false;
                            }
                            return selectedRegion;
                        }
                    }).then(function(selectResult) {
                        if (selectResult.isConfirmed) {
                            const shouldChangeRegion = parseInt(selectResult.value, 10) !== parseInt(currentRegionID, 10) ? 1 : 0;
                            markProductAsAssembled(productID, shouldChangeRegion, selectResult.value);
                        }
                    });
                }
            });
        });
    }

    function markProductAsAssembled(productID, changeRegion, regionID) {
        $.ajax({
            url: 'backend/markProductAssembled.php',
            type: 'POST',
            data: {
                productID: productID,
                changeRegion: changeRegion,
                regionID: regionID || 0
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Assembled!',
                        text: response.message || 'Product marked as assembled successfully',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(function() {
                        loadContent('assemblingLogs');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: response.message || 'Could not mark product as assembled'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the product'
                });
            }
        });
    }

    $(document).on('click', '#viewAssemblingModal', function(e) {
        if (e.target.id === 'viewAssemblingModal') {
            closeAssemblingViewModal();
        }
    });

    $(document).on('click', '#viewAssemblingModal .form-container', function(e) {
        e.stopPropagation();
    });
</script>

<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

if (!isset($adminRole) || ($adminRole !== 'admin' && $adminRole !== 'superadmin')) {
    echo '<div class="container"><div class="packages-table"><p style="padding:20px;text-align:center;">Access denied</p></div></div>';
    exit;
}

$products = [];
$where = 'p.cost IS NULL';

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
    WHERE {$where}
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
        <h3>✓ Assembling Approval — Pending Cost</h3>
        <p style="margin: -10px 0 20px 0; color: #666;">Products issued for assembling that are waiting for cost to be added.</p>
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
                        <th>Region</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Issued On</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="11" class="text-center">No products pending cost approval</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($product['productName']); ?></td>
                                <td><?php echo htmlspecialchars($product['categoryName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['brandName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['colorName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['modelName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['regionName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td><strong style="color: #11998e;"><?php echo htmlspecialchars($product['available'] ?? $product['quantity']); ?></strong></td>
                                <td><?php echo date('d M Y', strtotime($product['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary btn-view-pending-product" style="padding: 8px 16px; margin-right: 5px;" data-product="<?php echo htmlspecialchars(json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>">View</button>
                                    <button type="button" class="btn btn-success btn-add-product-cost" style="padding: 8px 16px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none;" data-product-id="<?php echo intval($product['productID']); ?>" data-product-name="<?php echo htmlspecialchars($product['productName'], ENT_QUOTES, 'UTF-8'); ?>">Add Cost</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div id="viewProductModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: white; padding: 20px; border-bottom: 2px solid #f0f0f0; border-radius: 16px 16px 0 0; z-index: 10;">
            <h2 style="margin: 0; color: #667eea;">Product Details</h2>
            <button onclick="closeViewModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <div id="productDetailsContent" style="padding: 20px;"></div>
        <div style="bottom: 0; background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeViewModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<script>
    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function viewProduct(product) {
        const detailsHtml = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Product Name</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.productName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Category</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.categoryName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Brand</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.brandName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Color</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.colorName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Model</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.modelName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Tonnage</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.sizeName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Batch Number</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.batchNumber || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Quantity</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.quantity || '0'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Available</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 600; color: #11998e; font-size: 16px;">${product.available || product.quantity || '0'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Region</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.regionName || 'N/A'}</p>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Created At</label>
                    <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${new Date(product.createdAt).toLocaleString()}</p>
                </div>
                <div style="margin-top: 20px;">
                    <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Description</label>
                    <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; min-height: 80px; font-weight: 500; color: #333;">${product.description || 'No description available'}</p>
                </div>
            </div>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 12px; border: 1px solid #e0e0e0;">
                <h4 style="margin: 0 0 15px 0; color: #667eea; font-size: 16px;">🏷️ Serial Numbers</h4>
                <div id="productSerialsList">
                    <p style="color: #666; margin: 0; text-align: center;">Loading serial numbers...</p>
                </div>
            </div>
        `;

        $('#productDetailsContent').html(detailsHtml);
        $('#viewProductModal').fadeIn(300);
        $('body').css('overflow', 'hidden');

        $.ajax({
            url: 'backend/getProductSerials.php',
            type: 'GET',
            data: { productID: product.productID },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#productSerialsList').html(renderProductSerials(response.data));
                } else {
                    $('#productSerialsList').html('<p style="color: #e53935; margin: 0; text-align: center;">' + escapeHtml(response.message || 'Failed to load serial numbers') + '</p>');
                }
            },
            error: function() {
                $('#productSerialsList').html('<p style="color: #e53935; margin: 0; text-align: center;">Failed to load serial numbers</p>');
            }
        });
    }

    function renderProductSerials(serials) {
        if (!serials || serials.length === 0) {
            return '<p style="color: #666; margin: 0; text-align: center;">No serial numbers found for this product</p>';
        }

        const statusColors = {
            available: { bg: '#e8f5e9', color: '#2e7d32' },
            issued: { bg: '#e3f2fd', color: '#1565c0' },
            damaged: { bg: '#ffebee', color: '#c62828' }
        };

        let html = `
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
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
            const status = (serial.status || 'available').toLowerCase();
            const badge = statusColors[status] || { bg: '#f5f5f5', color: '#666' };

            html += `
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 12px; color: #666;">${index + 1}</td>
                    <td style="padding: 12px;">
                        <code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-size: 14px;">${escapeHtml(serial.serialNumber)}</code>
                    </td>
                    <td style="padding: 12px; color: #333;">${escapeHtml(serial.batchNumber || 'N/A')}</td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="background: ${badge.bg}; color: ${badge.color}; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: capitalize;">${escapeHtml(status)}</span>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        return html;
    }

    function closeViewModal() {
        $('#viewProductModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    }

    function addProductCost(productID, productName) {
        Swal.fire({
            title: 'Add Assembling Cost',
            html: '<p style="margin:0 0 12px;color:#666;">Product: <strong>' + escapeHtml(productName) + '</strong></p>',
            input: 'number',
            inputLabel: 'Cost per unit (RS)',
            inputPlaceholder: 'Enter cost',
            inputAttributes: {
                min: 0.01,
                step: 0.01
            },
            showCancelButton: true,
            confirmButtonText: 'Save Cost',
            confirmButtonColor: '#11998e',
            preConfirm: (value) => {
                const cost = parseFloat(value);
                if (!value || isNaN(cost) || cost <= 0) {
                    Swal.showValidationMessage('Please enter a valid cost greater than 0');
                    return false;
                }
                return cost;
            }
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: 'backend/addProductCost.php',
                type: 'POST',
                data: {
                    productID: productID,
                    cost: result.value
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cost Added',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            if (typeof updateAssemblingApprovalAdminCount === 'function') {
                                updateAssemblingApprovalAdminCount();
                            }
                            loadContent('assemblingApprovalAdmin');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to save cost'
                    });
                }
            });
        });
    }

    $(document).on('click.assemblingApproval', '.btn-view-pending-product', function() {
        const productData = $(this).attr('data-product');
        if (!productData) {
            return;
        }
        try {
            viewProduct(JSON.parse(productData));
        } catch (e) {
            console.error('Failed to parse product data', e);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Could not open product details.' });
        }
    });

    $(document).on('click.assemblingApproval', '.btn-add-product-cost', function() {
        const productID = parseInt($(this).attr('data-product-id'), 10);
        const productName = $(this).attr('data-product-name') || '';
        if (!productID) {
            return;
        }
        addProductCost(productID, productName);
    });

    $(document).on('click.assemblingApproval', '#viewProductModal', function(e) {
        if (e.target.id === 'viewProductModal') {
            closeViewModal();
        }
    });

    $(document).on('click.assemblingApproval', '#viewProductModal .form-container', function(e) {
        e.stopPropagation();
    });
</script>

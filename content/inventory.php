<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Fetch all categories for filter dropdown
$categories = [];
$categoriesResult = $conn->query("SELECT * FROM categories ORDER BY categoryName ASC");
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch all regions for filter dropdown
$regions = [];
$regionsResult = $conn->query("SELECT * FROM regions ORDER BY regionName ASC");
if ($regionsResult) {
    while ($row = $regionsResult->fetch_assoc()) {
        $regions[] = $row;
    }
}

// Fetch all models for filter dropdown
$models = [];
$modelsResult = $conn->query("SELECT * FROM models ORDER BY modelName ASC");
if ($modelsResult) {
    while ($row = $modelsResult->fetch_assoc()) {
        $models[] = $row;
    }
}

// Fetch all colors for filter dropdown
$colors = [];
$colorsResult = $conn->query("SELECT * FROM colors ORDER BY colorName ASC");
if ($colorsResult) {
    while ($row = $colorsResult->fetch_assoc()) {
        $colors[] = $row;
    }
}

// Fetch all brands for filter dropdown
$brands = [];
$brandsResult = $conn->query("SELECT * FROM brands ORDER BY brandName ASC");
if ($brandsResult) {
    while ($row = $brandsResult->fetch_assoc()) {
        $brands[] = $row;
    }
}

// Fetch all sizes for filter dropdown
$sizes = [];
$sizesResult = $conn->query("SELECT * FROM sizes ORDER BY sizeName ASC");
if ($sizesResult) {
    while ($row = $sizesResult->fetch_assoc()) {
        $sizes[] = $row;
    }
}

// Fetch all products with related information and average cost from batches
$products = [];
// Apply region filter for non-superadmin users
$products = [];
$regionIDToUse = isset($regionID) ? intval($regionID) : 0;
$whereClause = '';
if (isset($adminRole) && $adminRole !== 'superadmin') {
    $whereClause = "WHERE p.regionID = " . $regionIDToUse;
}

$sql = "
    SELECT 
        p.*,
        b.brandName,
        c.categoryName,
        col.colorName,
        m.modelName,
        s.sizeName,
        r.regionName,
        COALESCE(AVG(pb.cost), p.cost) as averageCost
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoriesID
    LEFT JOIN brands b ON p.brandID = b.brandID
    LEFT JOIN colors col ON p.colorID = col.colorID
    LEFT JOIN models m ON p.modelID = m.modelID
    LEFT JOIN sizes s ON p.sizeID = s.sizeID
    LEFT JOIN regions r ON p.regionID = r.regionID
    LEFT JOIN product_batch pb ON p.productID = pb.productID
    " . $whereClause . "
    GROUP BY p.productID
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
        <h3>📦 Inventory Management</h3>

        <!-- Filters Section -->
        <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                <h4 style="margin: 0; color: #667eea;">🔍 Filters</h4>
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-left: auto; flex-wrap: wrap;">
                    <button onclick="clearFilters()" class="btn btn-secondary" style="padding: 8px 16px; border-radius: 6px; font-weight: 600;">Clear All</button>
                    <button onclick="exportInventoryCSV()" class="btn" style="padding: 8px 16px; background: #4caf50; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; box-shadow: 0 4px 12px rgba(76, 175, 80, 0.25);">📥 Export CSV</button>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <?php if (isset($adminRole) && $adminRole === 'superadmin'): ?>
                <div class="form-group">
                    <label for="filterRegion" class="form-label">Region</label>
                    <select id="filterRegion" class="form-control select2-filter">
                        <option value="">All Regions</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?php echo $region['regionID']; ?>">
                                <?php echo htmlspecialchars($region['regionName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- <div class="form-group">
                    <label for="filterCategory" class="form-label">Category</label>
                    <select id="filterCategory" class="form-control select2-filter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['categoriesID']; ?>">
                                <?php echo htmlspecialchars($category['categoryName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div> -->

                <div class="form-group">
                    <label for="filterBrand" class="form-label">Brand</label>
                    <select id="filterBrand" class="form-control select2-filter">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brandID']; ?>">
                                <?php echo htmlspecialchars($brand['brandName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="filterModel" class="form-label">Model</label>
                    <select id="filterModel" class="form-control select2-filter">
                        <option value="">All Models</option>
                        <?php foreach ($models as $model): ?>
                            <option value="<?php echo $model['modelID']; ?>">
                                <?php echo htmlspecialchars($model['modelName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="filterColor" class="form-label">Color</label>
                    <select id="filterColor" class="form-control select2-filter">
                        <option value="">All Colors</option>
                        <?php foreach ($colors as $color): ?>
                            <option value="<?php echo $color['colorID']; ?>">
                                <?php echo htmlspecialchars($color['colorName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="filterSize" class="form-label">Tonnage</label>
                    <select id="filterSize" class="form-control select2-filter">
                        <option value="">All Tonnage</option>
                        <?php foreach ($sizes as $size): ?>
                            <option value="<?php echo $size['sizeID']; ?>">
                                <?php echo htmlspecialchars($size['sizeName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; margin-top: 20px; margin-bottom: 20px; color: white;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; text-align: center;">
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="totalProducts"><?php echo count($products); ?></div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Products</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="totalQuantity">
                        <?php
                        $totalQty = 0;
                        foreach ($products as $p) {
                            $totalQty += $p['quantity'];
                        }
                        echo $totalQty;
                        ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Quantity</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="totalAvailable">
                        <?php
                        $totalAvailable = 0;
                        foreach ($products as $p) {
                            $totalAvailable += ($p['available'] ?? $p['quantity']);
                        }
                        echo $totalAvailable;
                        ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Available</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="visibleProducts"><?php echo count($products); ?></div>
                    <div style="font-size: 14px; opacity: 0.9;">Visible Items</div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Region</th>
                        <th>Model</th>
                        <th>Color</th>
                        <th>Tonnage</th>
                        <!-- <th>Batch Number</th> -->
                        <th>Stock In</th>
                        <th>Stock Out</th>
                        <th>Available</th>
                        <th>Avg Cost/Unit</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="12" class="text-center">No products found in inventory</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $serial = 1;
                        foreach ($products as $product):
                        ?>
                            <tr class="inventory-row"
                                data-category="<?php echo $product['categoryID']; ?>"
                                data-brand="<?php echo $product['brandID']; ?>"
                                data-region="<?php echo $product['regionID']; ?>"
                                data-model="<?php echo $product['modelID']; ?>"
                                data-color="<?php echo $product['colorID']; ?>"
                                data-size="<?php echo $product['sizeID']; ?>">
                                <td><?php echo $serial++; ?></td>
                                <td><strong><?php echo htmlspecialchars($product['productName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['categoryName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['brandName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['regionName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['modelName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['colorName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['sizeName'] ?? 'N/A'); ?></td>
                                <!-- <td>
                                    <code style="background: #f0f0f0; padding: 3px 8px; border-radius: 4px;">
                                        <?php echo htmlspecialchars($product['batchNumber'] ?? 'N/A'); ?>
                                    </code>
                                </td> -->
                                <td>
                                    <span style="background: <?php echo $product['quantity'] > 0 ? '#4caf50' : '#f44336'; ?>; color: white; padding: 5px 12px; border-radius: 5px; font-weight: bold;">
                                        <?php echo htmlspecialchars($product['quantity']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="background: <?php $issued = ($product['quantity'] - ($product['available'] ?? $product['quantity']));
                                                                echo $issued > 0 ? '#ff9800' : '#f44336'; ?>; color: white; padding: 5px 12px; border-radius: 5px; font-weight: bold;">
                                        <?php echo htmlspecialchars($issued); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="background: <?php echo ($product['available'] ?? $product['quantity']) > 0 ? '#11998e' : '#f44336'; ?>; color: white; padding: 5px 12px; border-radius: 5px; font-weight: bold;">
                                        <?php echo htmlspecialchars($product['available'] ?? $product['quantity']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #667eea;">RS <?php echo number_format($product['averageCost'] ?? $product['cost'], 2); ?></strong>
                                </td>
                                <!-- <td><?php echo date('d M Y', strtotime($product['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-primary" style="padding: 8px 16px;" onclick="viewInventoryProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">View</button>
                                </td> -->
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div id="viewInventoryModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: white; padding: 20px; border-bottom: 2px solid #f0f0f0; border-radius: 16px 16px 0 0; z-index: 10;">
            <h2 style="margin: 0; color: #667eea;">📦 Product Details</h2>
            <button onclick="closeInventoryModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <div id="inventoryDetailsContent" style="padding: 20px;">
            <!-- Product details will be inserted here -->
        </div>
        <div style="bottom: 0; background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeInventoryModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize Select2 on all filter dropdowns
        $('.select2-filter').select2({
            placeholder: function() {
                return $(this).find('option:first').text();
            },
            allowClear: true,
            width: '100%',
            theme: 'default'
        });

        // Variable to track if we're clearing filters
        let isClearing = false;

        // Bind change event to all Select2 filters
        $('.select2-filter').on('change', function() {
            if (!isClearing) {
                filterInventory();
            }
        });

        // Expose isClearing to global scope
        window.isClearing = false;

        // Check if there's a stored filter from dashboard
        const storedFilter = sessionStorage.getItem('inventoryFilter');
        if (storedFilter) {
            try {
                const filter = JSON.parse(storedFilter);

                // Map filter types to select IDs
                const filterMap = {
                    category: '#filterCategory',
                    model: '#filterModel',
                    region: '#filterRegion'
                };

                if (filterMap[filter.type]) {
                    $(filterMap[filter.type]).val(filter.value).trigger('change');

                    // Show notification
                    Swal.fire({
                        icon: 'info',
                        title: 'Filter Applied',
                        text: 'Showing filtered results from dashboard',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }

                // Clear the stored filter
                sessionStorage.removeItem('inventoryFilter');
            } catch (e) {
                console.error('Error parsing stored filter:', e);
            }
        }
    });

    function filterInventory() {
        const categoryFilter = $('#filterCategory').val();
        const brandFilter = $('#filterBrand').val();
        const regionFilter = $('#filterRegion').val();
        const modelFilter = $('#filterModel').val();
        const colorFilter = $('#filterColor').val();
        const sizeFilter = $('#filterSize').val();

        let visibleCount = 0;
        let visibleQty = 0;
        let visibleAvailable = 0;

        $('.inventory-row').each(function() {
            const row = $(this);
            const rowCategory = row.data('category').toString();
            const rowBrand = (row.data('brand') || '').toString();
            const rowRegion = row.data('region').toString();
            const rowModel = row.data('model').toString();
            const rowColor = row.data('color').toString();
            const rowSize = row.data('size').toString();

            let show = true;

            if (categoryFilter && rowCategory !== categoryFilter) show = false;
            if (brandFilter && rowBrand !== brandFilter) show = false;
            if (regionFilter && rowRegion !== regionFilter) show = false;
            if (modelFilter && rowModel !== modelFilter) show = false;
            if (colorFilter && rowColor !== colorFilter) show = false;
            if (sizeFilter && rowSize !== sizeFilter) show = false;

            if (show) {
                row.show();
                visibleCount++;
                // Extract quantity from the quantity cell (column 8)
                const qtyText = row.find('td:eq(8)').text().trim();
                visibleQty += parseInt(qtyText) || 0;
                // Extract available from the available cell (column 9)
                const availText = row.find('td:eq(9)').text().trim();
                visibleAvailable += parseInt(availText) || 0;
            } else {
                row.hide();
            }
        });

        // Update visible counts
        $('#visibleProducts').text(visibleCount);
        $('#totalQuantity').text(visibleQty);
        $('#totalAvailable').text(visibleAvailable);

        // Show message if no results
        if (visibleCount === 0) {
            if ($('#noResultsRow').length === 0) {
                $('#inventoryTableBody').append('<tr id="noResultsRow"><td colspan="11" class="text-center" style="color: #999;">No products match the selected filters</td></tr>');
            }
        } else {
            $('#noResultsRow').remove();
        }
    }

    function clearFilters() {
        // Set flag to prevent filterInventory from running
        window.isClearing = true;

        // Clear Select2 selections
        $('#filterCategory, #filterBrand, #filterRegion, #filterModel, #filterColor, #filterSize').val(null).trigger('change');

        // Show all rows
        $('.inventory-row').show();
        $('#noResultsRow').remove();

        // Recalculate totals from ALL visible rows
        let totalQty = 0;
        let totalAvail = 0;
        $('.inventory-row').each(function() {
            const qtyText = $(this).find('td:eq(8)').text().trim();
            const availText = $(this).find('td:eq(9)').text().trim();
            totalQty += parseInt(qtyText) || 0;
            totalAvail += parseInt(availText) || 0;
        });

        // Update all counts
        $('#visibleProducts').text($('.inventory-row').length);
        $('#totalQuantity').text(totalQty);
        $('#totalAvailable').text(totalAvail);

        // Reset flag after a short delay to allow Select2 to finish updating
        setTimeout(function() {
            window.isClearing = false;
        }, 100);
    }

    function viewInventoryProduct(product) {
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
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Region</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.regionName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Model</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.modelName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Color</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${product.colorName || 'N/A'}</p>
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
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333; font-size: 18px;">${product.quantity || '0'}</p>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Created At</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${new Date(product.createdAt).toLocaleString()}</p>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Description</label>
                    <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; min-height: 80px; font-weight: 500; color: #333;">${product.description || 'No description available'}</p>
                </div>
            </div>
        `;

        $('#inventoryDetailsContent').html(detailsHtml);
        $('#viewInventoryModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeInventoryModal() {
        $('#viewInventoryModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    }

    // Close modal when clicking outside
    $(document).on('click', '#viewInventoryModal', function(e) {
        if (e.target.id === 'viewInventoryModal') {
            closeInventoryModal();
        }
    });

    // Prevent modal close when clicking inside the content
    $(document).on('click', '#viewInventoryModal .form-container', function(e) {
        e.stopPropagation();
    });

    function exportInventoryCSV() {
        // Get current visible filters
        const categoryFilter = $('#filterCategory').val();
        const regionFilter = $('#filterRegion').val();
        const modelFilter = $('#filterModel').val();
        const colorFilter = $('#filterColor').val();
        const sizeFilter = $('#filterSize').val();

        // Build query parameters
        let params = '';
        if (categoryFilter) params += 'category=' + categoryFilter + '&';
        if (regionFilter) params += 'region=' + regionFilter + '&';
        if (modelFilter) params += 'model=' + modelFilter + '&';
        if (colorFilter) params += 'color=' + colorFilter + '&';
        if (sizeFilter) params += 'size=' + sizeFilter + '&';

        // Navigate to export script from the app root
        window.location.href = 'backend/exportInventoryCSV.php?' + params;
    }
</script>
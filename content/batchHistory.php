<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Fetch all batch history with product details
$batches = [];
$result = $conn->query("
    SELECT 
        pb.*,
        p.productName,
        c.categoryName,
        col.colorName,
        m.modelName,
        s.sizeName,
        r.regionName
    FROM product_batch pb
    INNER JOIN products p ON pb.productID = p.productID
    LEFT JOIN categories c ON p.categoryID = c.categoriesID
    LEFT JOIN colors col ON p.colorID = col.colorID
    LEFT JOIN models m ON p.modelID = m.modelID
    LEFT JOIN sizes s ON p.sizeID = s.sizeID
    LEFT JOIN regions r ON p.regionID = r.regionID
    ORDER BY pb.createdAt DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row;
    }
}

// Fetch all unique product names for filter dropdown
$products = [];
$productsResult = $conn->query("SELECT DISTINCT productID, productName FROM products ORDER BY productName ASC");
if ($productsResult) {
    while ($row = $productsResult->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="container">
    <div class="packages-table">
        <h3>🕒 Batch History</h3>
        
        <!-- Search and Filter Section -->
        <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
                <!-- Search by Batch Number -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        <span style="font-size: 14px;">🔍 Search Batch Number</span>
                    </label>
                    <input type="text" id="searchBatch" placeholder="Enter batch number..." style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: all 0.3s;" onkeyup="filterBatchHistory()">
                </div>
                
                <!-- Filter by Product -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        <span style="font-size: 14px;">📦 Filter by Product</span>
                    </label>
                    <select id="filterProduct" class="select2-filter" style="width: 100%;" onchange="filterBatchHistory()">
                        <option value="">All Products</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['productID']; ?>">
                                <?php echo htmlspecialchars($product['productName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Clear Button -->
                <div>
                    <button onclick="clearBatchFilters()" style="padding: 12px 24px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; white-space: nowrap;">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; color: white;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; text-align: center;">
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="totalBatches"><?php echo count($batches); ?></div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Batches</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="totalBatchQty">
                        <?php 
                        $totalQty = 0;
                        foreach ($batches as $b) {
                            $totalQty += $b['quantity'];
                        }
                        echo $totalQty;
                        ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Quantity</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="totalBatchCost">
                        <?php 
                        $totalCost = 0;
                        foreach ($batches as $b) {
                            $totalCost += ($b['quantity'] * $b['cost']);
                        }
                        echo 'RS ' . number_format($totalCost, 2);
                        ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Cost</div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold;" id="visibleBatches"><?php echo count($batches); ?></div>
                    <div style="font-size: 14px; opacity: 0.9;">Visible Batches</div>
                </div>
            </div>
        </div>

        <!-- Batch History Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Batch Number</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Model</th>
                        <th>Color</th>
                        <th>Tonnage</th>
                        <th>Region</th>
                        <th>Quantity</th>
                        <th>Cost/Unit</th>
                        <th>Total Cost</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody id="batchHistoryTableBody">
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="12" class="text-center">No batch history found</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $serial = 1;
                        foreach ($batches as $batch): 
                            $totalCost = $batch['quantity'] * $batch['cost'];
                        ?>
                            <tr class="batch-row" 
                                data-product-id="<?php echo $batch['productID']; ?>"
                                data-batch-number="<?php echo strtolower($batch['batchNumber']); ?>"
                                data-product-name="<?php echo strtolower($batch['productName']); ?>">
                                <td><?php echo $serial++; ?></td>
                                <td>
                                    <code style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px;">
                                        <?php echo htmlspecialchars($batch['batchNumber']); ?>
                                    </code>
                                </td>
                                <td><strong><?php echo htmlspecialchars($batch['productName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($batch['categoryName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($batch['modelName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($batch['colorName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($batch['sizeName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($batch['regionName'] ?? 'N/A'); ?></td>
                                <td>
                                    <span style="background: #4caf50; color: white; padding: 5px 12px; border-radius: 5px; font-weight: bold;">
                                        <?php echo htmlspecialchars($batch['quantity']); ?>
                                    </span>
                                </td>
                                <td><strong style="color: #667eea;">RS <?php echo number_format($batch['cost'], 2); ?></strong></td>
                                <td><strong style="color: #11998e; font-size: 15px;">RS <?php echo number_format($totalCost, 2); ?></strong></td>
                                <td>
                                    <span style="color: #666; font-size: 13px;">
                                        <?php echo date('d M Y', strtotime($batch['createdAt'])); ?><br>
                                        <span style="opacity: 0.7;"><?php echo date('h:i A', strtotime($batch['createdAt'])); ?></span>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize Select2 on product filter
        $('#filterProduct').select2({
            placeholder: 'All Products',
            allowClear: true,
            width: '100%',
            theme: 'default'
        });
        
        // Focus on search input for quick access
        $('#searchBatch').focus();
    });

    function filterBatchHistory() {
        const searchBatch = $('#searchBatch').val().toLowerCase().trim();
        const filterProduct = $('#filterProduct').val();
        
        let visibleCount = 0;
        let visibleQty = 0;
        let visibleCost = 0;
        
        $('.batch-row').each(function() {
            const row = $(this);
            const batchNumber = String(row.data('batch-number') || '');
            const productId = String(row.data('product-id') || '');
            const productName = String(row.data('product-name') || '');
            
            let show = true;
            
            // Filter by batch number (search both batch number and product name)
            if (searchBatch) {
                if (!batchNumber.includes(searchBatch) && !productName.includes(searchBatch)) {
                    show = false;
                }
            }
            
            // Filter by product
            if (filterProduct && productId !== filterProduct) {
                show = false;
            }
            
            if (show) {
                row.show();
                visibleCount++;
                // Extract quantity from the quantity cell
                const qtyText = row.find('td:eq(8)').text().trim();
                visibleQty += parseInt(qtyText) || 0;
                // Extract total cost from the total cost cell
                const costText = row.find('td:eq(10)').text().replace('RS', '').replace(',', '').trim();
                visibleCost += parseFloat(costText) || 0;
            } else {
                row.hide();
            }
        });
        
        // Update visible counts
        $('#visibleBatches').text(visibleCount);
        $('#totalBatchQty').text(visibleQty);
        $('#totalBatchCost').text('RS ' + visibleCost.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        // Show message if no results
        if (visibleCount === 0) {
            if ($('#noResultsRow').length === 0) {
                $('#batchHistoryTableBody').append('<tr id="noResultsRow"><td colspan="12" class="text-center" style="color: #999; padding: 40px;">No batches match your search criteria</td></tr>');
            }
        } else {
            $('#noResultsRow').remove();
        }
    }
    
    function clearBatchFilters() {
        // Clear search input
        $('#searchBatch').val('');
        
        // Clear Select2 product filter
        $('#filterProduct').val(null).trigger('change');
        
        // Show all rows
        $('.batch-row').show();
        $('#noResultsRow').remove();
        
        // Recalculate totals from ALL rows
        let totalQty = 0;
        let totalCost = 0;
        $('.batch-row').each(function() {
            const qtyText = $(this).find('td:eq(8)').text().trim();
            const costText = $(this).find('td:eq(10)').text().replace('RS', '').replace(',', '').trim();
            totalQty += parseInt(qtyText) || 0;
            totalCost += parseFloat(costText) || 0;
        });
        
        // Update all counts
        $('#visibleBatches').text($('.batch-row').length);
        $('#totalBatches').text($('.batch-row').length);
        $('#totalBatchQty').text(totalQty);
        $('#totalBatchCost').text('RS ' + totalCost.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        // Focus back to search input
        $('#searchBatch').focus();
    }
    
    // Add real-time search as user types
    $('#searchBatch').on('input', function() {
        filterBatchHistory();
    });
</script>

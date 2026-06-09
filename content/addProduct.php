<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Fetch all categories for dropdown
$categories = [];
$categoriesResult = $conn->query("SELECT * FROM categories ORDER BY categoryName ASC");
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch all colors for dropdown
$colors = [];
$colorsResult = $conn->query("SELECT * FROM colors ORDER BY colorName ASC");
if ($colorsResult) {
    while ($row = $colorsResult->fetch_assoc()) {
        $colors[] = $row;
    }
}

// Fetch all brands for dropdown
$brands = [];
$brandsResult = $conn->query("SELECT * FROM brands ORDER BY brandName ASC");
if ($brandsResult) {
    while ($row = $brandsResult->fetch_assoc()) {
        $brands[] = $row;
    }
}

// Fetch all models with category info for dropdown
$models = [];
$modelsResult = $conn->query("SELECT * FROM models ORDER BY modelName ASC");
if ($modelsResult) {
    while ($row = $modelsResult->fetch_assoc()) {
        $models[] = $row;
    }
}

// Fetch all sizes with category info for dropdown
$sizes = [];
$sizesResult = $conn->query("SELECT * FROM sizes ORDER BY sizeName ASC");
if ($sizesResult) {
    while ($row = $sizesResult->fetch_assoc()) {
        $sizes[] = $row;
    }
}

// Fetch all regions for dropdown
$regions = [];
$regionsResult = $conn->query("SELECT * FROM regions ORDER BY regionName ASC");
if ($regionsResult) {
    while ($row = $regionsResult->fetch_assoc()) {
        $regions[] = $row;
    }
}

// Fetch all parts (both with and without serial numbers)
$partsWithSerial = [];
$partsWithoutSerial = [];
$partsResult = $conn->query("
    SELECT DISTINCT partName FROM parts ORDER BY partName ASC
");
if ($partsResult) {
    while ($row = $partsResult->fetch_assoc()) {
        $partName = $row['partName'];

        // Check if this part has any serial numbers
        $serialCheck = $conn->query("SELECT COUNT(*) as count FROM parts WHERE partName = '$partName' AND serialNumber IS NOT NULL LIMIT 1");
        $serialCount = $serialCheck->fetch_assoc()['count'];

        if ($serialCount > 0) {
            // Parts with serials - get all available records with serial numbers
            $serialParts = [];
            $serialPartResult = $conn->query("SELECT partID, partName, serialNumber, batchName FROM parts WHERE partName = '$partName' AND serialNumber IS NOT NULL AND status = 'available' ORDER BY serialNumber ASC");
            if ($serialPartResult) {
                while ($p = $serialPartResult->fetch_assoc()) {
                    $serialParts[] = $p;
                }
            }
            if (!empty($serialParts)) {
                $partsWithSerial[$partName] = $serialParts;
            }
        } else {
            // Parts without serials - get available quantity, supporting both
            // one-row-per-item records and older aggregate quantity rows.
            $noSerialCheck = $conn->query("SELECT SUM(CASE WHEN quantity > 0 THEN quantity ELSE 1 END) as count FROM parts WHERE partName = '$partName' AND serialNumber IS NULL AND status = 'available'");
            $noSerialCount = $noSerialCheck->fetch_assoc()['count'];
            if ($noSerialCount > 0) {
                $partsWithoutSerial[$partName] = $noSerialCount;
            }
        }
    }
}

// Fetch all gases with their batches
$gases = [];
$gasesResult = $conn->query("
    SELECT DISTINCT gm.gas_id, gm.gas_name 
    FROM gas_master gm 
    ORDER BY gm.gas_name ASC
");
if ($gasesResult) {
    while ($row = $gasesResult->fetch_assoc()) {
        $gasId = $row['gas_id'];
        $gasName = $row['gas_name'];

        // Get all batches for this gas
        $batches = [];
        $batchesResult = $conn->query("
            SELECT batch_id, batchName, available, unit_price 
            FROM gas_batch_details 
            WHERE gas_id = $gasId AND available > 0
            ORDER BY batchName ASC
        ");
        if ($batchesResult) {
            while ($batch = $batchesResult->fetch_assoc()) {
                $batches[] = $batch;
            }
        }

        if (!empty($batches)) {
            $gases[$gasName] = [
                'gas_id' => $gasId,
                'batches' => $batches
            ];
        }
    }
}

// Fetch all products with related information
$products = [];
$result = $conn->query("
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
    ORDER BY p.createdAt DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="container">
    <!-- Success Alert (appears at top) -->
    <div id="successAlert" style="display: none; position: sticky; top: 20px; z-index: 999; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3); border-left: 5px solid #2e7d32;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">✓</span>
            <div>
                <strong style="display: block; font-size: 16px; margin-bottom: 4px;">Success!</strong>
                <span id="successAlertText" style="font-size: 14px;">Operation completed successfully</span>
            </div>
        </div>
    </div>

    <!-- Add Product Form -->
    <div class="form-container">
        <h2>Issue For Assembling</h2>
        <form id="addProductForm">
            <!-- Hidden CategoryID field - Always set to 1 -->
            <input type="hidden" id="categoryID" name="categoryID" value="1">

            <!-- Row 1: Product Name, Batch Number, Color -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="productName" class="form-label">Product Name</label>
                    <input type="text" id="productName" name="productName" class="form-control" placeholder="Enter product name" required>
                </div>

                <div class="form-group">
                    <label for="batchNumber" class="form-label">Batch Number</label>
                    <input type="text" id="batchNumber" name="batchNumber" class="form-control" placeholder="Enter batch number" required>
                </div>

                <div class="form-group">
                    <label for="colorID" class="form-label">Color</label>
                    <select id="colorID" name="colorID" class="form-control" required>
                        <option value="">Select Color</option>
                        <?php foreach ($colors as $color): ?>
                            <option value="<?php echo $color['colorID']; ?>">
                                <?php echo htmlspecialchars($color['colorName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Row 2: Model, Size, Batch Number -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="modelID" class="form-label">Model</label>
                    <select id="modelID" name="modelID" class="form-control" required>
                        <option value="">Select Model</option>
                        <?php foreach ($models as $model): ?>
                            <option value="<?php echo $model['modelID']; ?>" data-category="<?php echo $model['categoryID']; ?>">
                                <?php echo htmlspecialchars($model['modelName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sizeID" class="form-label">Tonnage</label>
                    <select id="sizeID" name="sizeID" class="form-control" required>
                        <option value="">Select Tonnage</option>
                        <?php foreach ($sizes as $size): ?>
                            <option value="<?php echo $size['sizeID']; ?>" data-category="<?php echo $size['categoryID']; ?>">
                                <?php echo htmlspecialchars($size['sizeName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cost" class="form-label">Assembling Cost</label>
                    <input type="number" id="cost" name="cost" class="form-control" placeholder="Enter price of AC" min="0" step="0.01" required>
                </div>
            </div>

            <!-- Row 3: Quantity (hidden/fixed to 1), Region (only for admin) -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                <!-- Quantity hidden field - Always set to 1 -->
                <input type="hidden" id="quantity" name="quantity" value="1">

                <!-- <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" value="1" disabled style="background-color: #f0f0f0; cursor: not-allowed;">
                </div> -->

                <!-- Region field - Only show for admin role -->
                <?php if ($adminRole === 'superadmin'): ?>
                    <div class="form-group">
                        <label for="regionID" class="form-label">Region</label>
                        <select id="regionID" name="regionID" class="form-control" required>
                            <option value="">Select Region</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo $region['regionID']; ?>">
                                    <?php echo htmlspecialchars($region['regionName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <input type="hidden" id="regionID" name="regionID" value="<?php echo $regionID; ?>">
                <!--<div class="form-group">
                        <label class="form-label">Region</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($regions[array_search($regionID, array_column($regions, 'regionID'))] ? $regions[array_search($regionID, array_column($regions, 'regionID'))]['regionName'] : 'Unknown'); ?>" disabled style="background-color: #f0f0f0; cursor: not-allowed;">
                    </div> -->
                <div class="form-group">
                    <label for="brandID" class="form-label">Brand</label>
                    <select id="brandID" name="brandID" class="form-control" required>
                        <option value="">Select Brand</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brandID']; ?>">
                                <?php echo htmlspecialchars($brand['brandName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Row 4: Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea id="description" name="description" class="form-control" placeholder="Enter product description" rows="3"></textarea>
            </div>

            <!-- Parts Selection Section -->
            <div id="partsSelectionContainer" style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #667eea;">
                <h4 style="margin-top: 0; color: #333; font-size: 18px;">📦 Select Parts to Issue</h4>

                <!-- Parts With Serial Numbers -->
                <?php if (!empty($partsWithSerial)): ?>
                    <div style="margin-bottom: 20px;">
                        <h5 style="color: #667eea; margin-bottom: 15px;">Parts with Serial Numbers</h5>
                        <?php foreach ($partsWithSerial as $partName => $serialParts): ?>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label class="form-label" style="color: black;"><?php echo htmlspecialchars($partName); ?></label>
                                <select class="form-control parts-serial-select" data-part-name="<?php echo htmlspecialchars($partName); ?>" multiple="multiple" style="width: 100%;">
                                    <?php foreach ($serialParts as $part): ?>
                                        <option value="<?php echo $part['partID']; ?>" data-serial="<?php echo htmlspecialchars($part['serialNumber']); ?>" data-batch="<?php echo htmlspecialchars($part['batchName']); ?>">
                                            <?php echo 'Serial No: ' . htmlspecialchars($part['serialNumber']) . ' (Batch: ' . htmlspecialchars($part['batchName']) . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Parts Without Serial Numbers -->
                <?php if (!empty($partsWithoutSerial)): ?>
                    <div style="margin-top: 20px;">
                        <h5 style="color: #667eea; margin-bottom: 15px;">Parts without Serial Numbers</h5>
                        <?php foreach ($partsWithoutSerial as $partName => $availableCount): ?>
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px; padding: 12px; background: white; border-radius: 8px;">
                                <input type="checkbox" class="parts-no-serial-checkbox" data-part-name="<?php echo htmlspecialchars($partName); ?>" data-available="<?php echo $availableCount; ?>" id="checkbox_<?php echo htmlspecialchars($partName); ?>" style="width: 20px; height: 20px; cursor: pointer;">
                                <label for="checkbox_<?php echo htmlspecialchars($partName); ?>" style="margin: 0; cursor: pointer; flex: 1; font-weight: 500;"><?php echo htmlspecialchars($partName); ?> (<?php echo $availableCount; ?> available)</label>
                                <div id="quantity_<?php echo htmlspecialchars($partName); ?>" style="display: none; gap: 10px; align-items: center;">
                                    <label style="margin: 0; font-size: 14px;">Quantity:</label>
                                    <input type="number" class="parts-no-serial-qty" data-part-name="<?php echo htmlspecialchars($partName); ?>" min="1" max="<?php echo $availableCount; ?>" style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Gases Selection Section -->
            <div style="background: #fff8f0; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #ff9800;">
                <h4 style="margin-top: 0; color: #333; font-size: 18px;">⛽ Select Gases to Issue</h4>

                <?php if (!empty($gases)): ?>
                    <div style="margin-bottom: 20px;">
                        <?php foreach ($gases as $gasName => $gasData): ?>
                            <div style="margin-bottom: 20px; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #ff9800;">
                                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                    <input type="checkbox" class="gas-checkbox" data-gas-id="<?php echo $gasData['gas_id']; ?>" data-gas-name="<?php echo htmlspecialchars($gasName); ?>" id="checkbox_gas_<?php echo $gasData['gas_id']; ?>" style="width: 20px; height: 20px; cursor: pointer;">
                                    <label for="checkbox_gas_<?php echo $gasData['gas_id']; ?>" style="margin: 0; cursor: pointer; font-weight: 600; flex: 1; font-size: 16px; color: #333;"><?php echo htmlspecialchars($gasName); ?></label>
                                </div>

                                <!-- Batches for this gas (hidden initially) -->
                                <div id="batches_gas_<?php echo $gasData['gas_id']; ?>" style="display: none; margin-left: 40px;">
                                    <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                        <h6 style="margin-top: 0; margin-bottom: 12px; color: #ff9800; font-size: 14px;">📦 Available Batches:</h6>
                                        <?php foreach ($gasData['batches'] as $batch): ?>
                                            <div style="margin-bottom: 12px; padding: 12px; background: white; border-radius: 6px; border: 1px solid #f0f0f0;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                    <label style="margin: 0; cursor: pointer; font-weight: 500; color: #333;">
                                                        <input type="checkbox" class="gas-batch-checkbox" data-gas-id="<?php echo $gasData['gas_id']; ?>" data-batch-id="<?php echo $batch['batch_id']; ?>" data-batch-name="<?php echo htmlspecialchars($batch['batchName']); ?>" data-available="<?php echo $batch['available']; ?>" data-unit-price="<?php echo $batch['unit_price']; ?>" style="width: 16px; height: 16px; cursor: pointer; margin-right: 8px;">
                                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($batch['batchName']); ?></span>
                                                    </label>
                                                    <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 600;">Available: <?php echo $batch['available']; ?> Units</span>
                                                </div>
                                                <div style="color: #666; font-size: 13px;">Assembling Cost: <strong>RS <?php echo number_format($batch['unit_price'], 2); ?></strong></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Quantity Inputs Container for multiple batches -->
                                    <div id="batch_quantities_gas_<?php echo $gasData['gas_id']; ?>" style="display: none; margin-bottom: 15px;">
                                        <!-- Batch quantity inputs will be added here dynamically -->
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #666; text-align: center;">No gases available</p>
                <?php endif; ?>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Issued</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Products -->
    <div class="packages-table">
        <h3>Previous Logs</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Color</th>
                        <th>Model</th>
                        <th>Region</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Cost/Unit</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="10" class="text-center">No products found</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $serial = 1;
                        $today = date('Y-m-d');
                        foreach ($products as $product):
                            $productDate = date('Y-m-d', strtotime($product['createdAt']));
                            $isToday = ($productDate === $today);
                        ?>
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
                                <td><strong>RS <?php echo number_format($product['cost'], 2); ?></strong></td>
                                <td class="text-center">
                                    <button class="btn btn-primary" style="padding: 8px 16px; margin-right: 5px;" onclick="viewProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">View</button>
                                    <!-- <button class="btn btn-success" style="padding: 8px 16px; margin-right: 5px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);" onclick="openUpdateModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">Update</button> -->
                                    <button class="btn btn-info" style="padding: 8px 16px; margin-right: 5px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);" onclick="openBatchLogsModal(<?php echo $product['productID']; ?>)">Logs</button>
                                    <?php if ($isToday): ?>
                                        <button class="btn-delete" onclick="deleteProduct(<?php echo $product['productID']; ?>)">Delete</button>
                                    <?php endif; ?>
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
        <div id="productDetailsContent" style="padding: 20px;">
            <!-- Product details will be inserted here -->
        </div>
        <div style="bottom: 0; background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeViewModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<!-- Serial Numbers Modal -->
<div id="serialNumbersModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); overflow: auto;">
    <div class="form-container" style="max-width: 800px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="position: sticky; top: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px 16px 0 0; z-index: 10;">
            <h2 style="margin: 0; color: white;">📝 Enter Serial Numbers</h2>
            <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Please enter <span id="serialCountText">0</span> unique serial numbers</p>
        </div>
        <form id="serialNumbersForm" style="padding: 20px;">
            <div id="serialInputsContainer" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                <!-- Serial number inputs will be generated here -->
            </div>
            <div style="display: flex; gap: 12px; justify-content: center; position: sticky; bottom: 0; background: white; padding: 20px 0; border-top: 2px solid #f0f0f0;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Save Product & Serials</button>
                <button type="button" class="btn btn-secondary" onclick="closeSerialModal()" style="padding: 12px 30px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Product Modal -->
<div id="updateProductModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); overflow: auto;">
    <div class="form-container" style="max-width: 600px; margin: 5% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 16px 16px 0 0;">
            <h2 style="margin: 0; color: white;">🔄 Update Product Batch</h2>
            <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Add new batch to <span id="updateProductName" style="font-weight: 600;"></span></p>
            <button onclick="closeUpdateModal()" style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.2); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <form id="updateProductForm" style="padding: 30px;">
            <input type="hidden" id="updateProductID" name="productID">

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="updateBatchNumber" class="form-label">Batch Number</label>
                <input type="text" id="updateBatchNumber" name="batchNumber" class="form-control" placeholder="Enter new batch number" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="updateQuantity" class="form-label">Quantity to Add</label>
                <input type="number" id="updateQuantity" name="quantity" class="form-control" placeholder="Enter quantity to add" min="1" required>
                <small style="color: #666; font-size: 12px;">This will be added to current quantity</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="updateCost" class="form-label">Cost Per Unit</label>
                <input type="number" id="updateCost" name="cost" class="form-control" placeholder="Enter cost per unit" min="0" step="0.01" required>
                <small style="color: #666; font-size: 12px;">This will update the product's unit cost</small>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #666;">Current Quantity:</span>
                    <strong id="currentQuantity">0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #666;">Current Cost/Unit:</span>
                    <strong id="currentCost">RS 0.00</strong>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center;">
                <button type="submit" class="btn btn-success" style="padding: 12px 30px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">Update Product</button>
                <button type="button" class="btn btn-secondary" onclick="closeUpdateModal()" style="padding: 12px 30px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Batch Logs Modal -->
<div id="batchLogsModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 800px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px 16px 0 0; z-index: 10; color: white;">
            <h2 style="margin: 0;">Batch Logs</h2>
            <!-- <button onclick="closeBatchLogsModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button> -->
        </div>
        <div style="padding: 20px;">
            <div id="batchLogsContent">
                <!-- Batch logs will be inserted here -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
    var productFormData = null;
    var updateProductData = null;

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    $(document).ready(function() {
        // Remove stale delegated handlers from previous AJAX loads of this page
        $(document).off('.addProduct');

        // Initialize Select2 for parts with serial numbers
        $('.parts-serial-select').select2({
            placeholder: 'Select parts...',
            allowClear: true,
            width: '100%'
        });

        // Handle checkboxes for parts without serial numbers
        $(document).on('change.addProduct', '.parts-no-serial-checkbox', function() {
            const partName = $(this).data('part-name');
            const quantityDiv = $('#quantity_' + partName.replace(/\s+/g, '_'));

            if ($(this).is(':checked')) {
                quantityDiv.show();
            } else {
                quantityDiv.hide();
            }
        });

        // ===== GAS SECTION HANDLERS =====
        // Handle gas checkbox click
        $(document).on('change.addProduct', '.gas-checkbox', function() {
            const gasId = $(this).data('gas-id');
            const batchesDiv = $('#batches_gas_' + gasId);

            if ($(this).is(':checked')) {
                batchesDiv.slideDown(300);
            } else {
                batchesDiv.slideUp(300);
                // Uncheck all batches and hide quantity inputs for this gas
                $('input[data-gas-id="' + gasId + '"].gas-batch-checkbox').prop('checked', false);
                $('#batch_quantities_gas_' + gasId).hide().empty();
            }
        });

        // Handle gas batch checkbox selection (allows multiple batches per gas)
        $(document).on('change.addProduct', '.gas-batch-checkbox', function() {
            const gasId = $(this).data('gas-id');
            const batchId = $(this).data('batch-id');
            const batchName = $(this).data('batch-name');
            const available = parseFloat($(this).data('available'));
            const unitPrice = parseFloat($(this).data('unit-price'));
            const container = $('#batch_quantities_gas_' + gasId);

            if ($(this).is(':checked')) {
                // Skip if quantity block already exists for this batch
                if (container.find('.gas-batch-qty-block[data-batch-id="' + batchId + '"]').length) {
                    return;
                }
                // Add quantity input for this batch
                const inputId = 'gas_qty_' + gasId + '_' + batchId;
                const html = `
                    <div class="gas-batch-qty-block" data-batch-id="${batchId}" style="padding: 12px; background: #f9f9f9; border-radius: 6px; margin-bottom: 10px; border-left: 4px solid #ff9800;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="color: #333;">${batchName}</strong>
                            <span style="font-size: 12px; color: #666;">Available: ${available} Units</span>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
                            <input type="number" id="${inputId}" class="gas-batch-qty" data-gas-id="${gasId}" data-batch-id="${batchId}" data-available="${available}" data-unit-price="${unitPrice}" min="0.01" step="0.01" placeholder="Quantity" style="flex: 1; padding: 8px; border: 2px solid #ff9800; border-radius: 4px;">
                            <span style="font-size: 12px; color: #666; white-space: nowrap;">Max: ${available}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #666;">
                            <span>Assembling Cost: <strong>RS ${unitPrice.toFixed(2)}</strong></span>
                            <span>Total: <strong class="batch-total-${gasId}-${batchId}" style="color: #ff9800;">RS 0.00</strong></span>
                        </div>
                    </div>
                `;
                container.append(html);
                container.show();
            } else {
                // Remove quantity input for this batch
                container.find('.gas-batch-qty-block[data-batch-id="' + batchId + '"]').fadeOut(200, function() {
                    $(this).remove();
                    if (container.find('.gas-batch-qty-block').length === 0) {
                        container.hide();
                    }
                });
            }
        });

        // Handle gas batch quantity input
        $(document).on('input.addProduct', '.gas-batch-qty', function() {
            const gasId = $(this).data('gas-id');
            const batchId = $(this).data('batch-id');
            const quantity = parseFloat($(this).val()) || 0;
            const unitPrice = parseFloat($(this).data('unit-price'));
            const available = parseFloat($(this).data('available'));

            // Validate max quantity
            if (quantity > available) {
                $(this).val(available);
            }

            // Update total cost display
            const totalCost = quantity * unitPrice;
            $(`.batch-total-${gasId}-${batchId}`).text('RS ' + totalCost.toFixed(2));
        });

        // Function to collect gas data (multiple batches per gas)
        function collectGasData() {
            const gasData = [];

            $('.gas-checkbox:checked').each(function() {
                const gasId = $(this).data('gas-id');
                const gasName = $(this).data('gas-name');
                const container = $('#batch_quantities_gas_' + gasId);

                // Get all selected batches for this gas
                container.find('.gas-batch-qty-block').each(function() {
                    const batchId = $(this).data('batch-id');
                    const qtyInput = $(this).find('.gas-batch-qty');
                    const quantity = parseFloat(qtyInput.val()) || 0;
                    const unitPrice = parseFloat(qtyInput.data('unit-price'));

                    if (quantity > 0) {
                        gasData.push({
                            gas_id: gasId,
                            gas_name: gasName,
                            batch_id: batchId,
                            quantity: quantity,
                            unit_price: unitPrice,
                            total_price: (quantity * unitPrice).toFixed(2)
                        });
                    }
                });
            });

            return gasData;
        }


        // Function to collect parts data
        function collectPartsData() {
            const partsData = {
                withSerial: [],
                withoutSerial: []
            };

            // Collect parts with serial numbers
            $('.parts-serial-select').each(function() {
                const selectedValues = $(this).val();
                const partName = $(this).data('part-name');

                if (selectedValues && selectedValues.length > 0) {
                    selectedValues.forEach(function(partID) {
                        const option = $(this).find('option[value="' + partID + '"]');
                        partsData.withSerial.push({
                            partID: partID,
                            partName: partName,
                            serial: option.data('serial'),
                            batch: option.data('batch')
                        });
                    }.bind(this));
                }
            });

            // Collect parts without serial numbers
            $('.parts-no-serial-checkbox:checked').each(function() {
                const partName = $(this).data('part-name');
                const quantityInput = $('.parts-no-serial-qty[data-part-name="' + partName + '"]');
                const quantity = parseInt(quantityInput.val()) || 0;

                if (quantity > 0) {
                    partsData.withoutSerial.push({
                        partName: partName,
                        quantity: quantity
                    });
                }
            });

            return partsData;
        }
        $('#categoryID').on('change', function() {
            const selectedCategory = $(this).val();

            // Filter models
            $('#modelID option').each(function() {
                const modelCategory = $(this).data('category');
                if ($(this).val() === '') {
                    $(this).show();
                } else if (selectedCategory === '' || modelCategory == selectedCategory) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#modelID').val('');

            // Filter sizes
            $('#sizeID option').each(function() {
                const sizeCategory = $(this).data('category');
                if ($(this).val() === '') {
                    $(this).show();
                } else if (selectedCategory === '' || sizeCategory == selectedCategory) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#sizeID').val('');
        });

        // When brand changes, fetch parts for that brand and update UI
        $('#brandID').on('change', function() {
            const brandID = $(this).val();
            if (!brandID) return;
            fetchPartsByBrand(brandID);
        });

        function fetchPartsByBrand(brandID) {
            $.ajax({
                url: 'backend/getPartsByBrand.php',
                type: 'GET',
                data: {
                    brandID: brandID
                },
                dataType: 'json',
                success: function(res) {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to fetch parts', 'error');
                        return;
                    }
                    renderPartsSelection(res.partsWithSerial, res.partsWithoutSerial);
                },
                error: function() {
                    Swal.fire('Error', 'Failed to fetch parts', 'error');
                }
            });
        }

        function renderPartsSelection(partsWithSerial, partsWithoutSerial) {
            let html = '';

            // Parts with serials
            if (Object.keys(partsWithSerial).length > 0) {
                html += '<div style="margin-bottom: 20px;">';
                html += '<h5 style="color: #667eea; margin-bottom: 15px;">Parts with Serial Numbers</h5>';
                for (const partName in partsWithSerial) {
                    const serialParts = partsWithSerial[partName];
                    html += '<div class="form-group" style="margin-bottom: 15px;">';
                    html += '<label class="form-label" style="color: black;">' + escapeHtml(partName) + '</label>';
                    html += '<select class="form-control parts-serial-select" data-part-name="' + escapeHtml(partName) + '" multiple="multiple" style="width: 100%;">';
                    for (const p of serialParts) {
                        html += '<option value="' + p.partID + '" data-serial="' + escapeHtml(p.serialNumber) + '" data-batch="' + escapeHtml(p.batchName) + '">';
                        html += 'Serial No: ' + escapeHtml(p.serialNumber) + ' (Batch: ' + escapeHtml(p.batchName) + ')';
                        html += '</option>';
                    }
                    html += '</select></div>';
                }
                html += '</div>';
            }

            // Parts without serials
            if (Object.keys(partsWithoutSerial).length > 0) {
                html += '<div style="margin-top: 20px;">';
                html += '<h5 style="color: #667eea; margin-bottom: 15px;">Parts without Serial Numbers</h5>';
                for (const partName in partsWithoutSerial) {
                    const availableCount = partsWithoutSerial[partName];
                    const safeId = sanitizeId(partName);
                    html += '<div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px; padding: 12px; background: white; border-radius: 8px;">';
                    html += '<input type="checkbox" class="parts-no-serial-checkbox" data-part-name="' + escapeHtml(partName) + '" data-safe="' + safeId + '" data-available="' + availableCount + '" id="checkbox_' + safeId + '" style="width: 20px; height: 20px; cursor: pointer;">';
                    html += '<label for="checkbox_' + safeId + '" style="margin: 0; cursor: pointer; flex: 1; font-weight: 500;">' + escapeHtml(partName) + ' (' + availableCount + ' available)</label>';
                    html += '<div id="quantity_' + safeId + '" style="display: none; gap: 10px; align-items: center;">';
                    html += '<label style="margin: 0; font-size: 14px;">Quantity:</label>';
                    html += '<input type="number" class="parts-no-serial-qty" data-part-name="' + escapeHtml(partName) + '" data-safe="' + safeId + '" min="1" max="' + availableCount + '" style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
                    html += '</div></div>';
                }
                html += '</div>';
            }

            if (!html) html = '<p style="color: #666; text-align: center;">No parts available for selected brand</p>';

            $('#partsSelectionContainer').html(html);

            // Re-bind simple interactions for newly injected elements
            $('.parts-no-serial-checkbox').on('change', function() {
                const safe = $(this).data('safe');
                if ($(this).is(':checked')) {
                    $('#quantity_' + safe).show();
                } else {
                    $('#quantity_' + safe).hide();
                }
            });
        }

        function sanitizeId(text) {
            return String(text).replace(/[^a-zA-Z0-9_-]/g, '_');
        }

        $('#addProductForm').on('submit', function(e) {
            e.preventDefault();

            const quantity = parseInt($('#quantity').val());

            if (quantity <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Quantity',
                    text: 'Quantity must be greater than 0'
                });
                return;
            }

            // Collect parts data
            const partsData = collectPartsData();

            // Collect gas data
            const gasData = collectGasData();

            // Store form data
            productFormData = $(this).serializeArray();

            // Add parts data to productFormData
            productFormData.push({
                name: 'partsData',
                value: JSON.stringify(partsData)
            });

            // Add gas data to productFormData
            productFormData.push({
                name: 'gasData',
                value: JSON.stringify(gasData)
            });

            // Open serial numbers modal
            openSerialModal(quantity);
        });

        // Handle serial numbers form submission
        $('#serialNumbersForm').on('submit', function(e) {
            e.preventDefault();

            // Collect all serial numbers
            const serialNumbers = [];
            $('.serial-input').each(function() {
                const value = $(this).val().trim();
                if (value) {
                    serialNumbers.push(value);
                }
            });

            // Validate all serials are entered
            const expectedCount = parseInt($('#serialCountText').text());
            if (serialNumbers.length !== expectedCount) {
                Swal.fire({
                    icon: 'error',
                    title: 'Incomplete',
                    text: `Please enter all ${expectedCount} serial numbers`
                });
                return;
            }

            // Check for duplicates within the form
            const uniqueSerials = new Set(serialNumbers);
            if (uniqueSerials.size !== serialNumbers.length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Duplicate Serial Numbers',
                    text: 'Each serial number must be unique'
                });
                return;
            }

            // Validate against database before any save operations
            validateSerialNumbersInDb(serialNumbers, function(isValid, message) {
                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Serial Number',
                        text: message || 'One or more serial numbers already exist in the database'
                    });
                    return;
                }

                if (updateProductData !== null) {
                    saveSerialNumbers(updateProductData.productID, updateProductData.batchNumber, serialNumbers);
                } else {
                    $.ajax({
                        url: 'backend/saveProduct.php',
                        type: 'POST',
                        data: $.param(productFormData),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success && response.productID) {
                                saveSerialNumbers(response.productID, response.batchNumber, serialNumbers);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to add product'
                                });
                                closeSerialModal();
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to add product'
                            });
                            closeSerialModal();
                        }
                    });
                }
            });
        });
    });

    function validateSerialNumbersInDb(serialNumbers, callback) {
        $.ajax({
            url: 'backend/validateProductSerials.php',
            type: 'POST',
            data: {
                serialNumbers: JSON.stringify(serialNumbers)
            },
            dataType: 'json',
            success: function(response) {
                callback(response.success === true, response.message);
            },
            error: function() {
                callback(false, 'Failed to validate serial numbers');
            }
        });
    }

    function openSerialModal(quantity) {
        $('#serialCountText').text(quantity);

        // Generate input fields
        const container = $('#serialInputsContainer');
        container.empty();

        for (let i = 1; i <= quantity; i++) {
            const inputHtml = `
                <div class="form-group">
                    <label for="serial${i}" class="form-label">Serial #${i}</label>
                    <input type="text" id="serial${i}" class="form-control serial-input" placeholder="Enter serial number ${i}" required>
                </div>
            `;
            container.append(inputHtml);
        }

        $('#serialNumbersModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeSerialModal() {
        $('#serialNumbersModal').fadeOut(300);
        $('body').css('overflow', 'auto');
        $('#serialInputsContainer').empty();
        productFormData = null;
        updateProductData = null;
    }

    function showSuccessAlert(message) {
        $('#successAlertText').text(message);
        $('#successAlert').slideDown(300);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('#successAlert').slideUp(300);
        }, 5000);
    }

    function openSerialModalForUpdate(quantity, batchNumber) {
        $('#serialCountText').text(quantity);

        // Generate input fields
        const container = $('#serialInputsContainer');
        container.empty();

        for (let i = 1; i <= quantity; i++) {
            const inputHtml = `
                <div class="form-group">
                    <label for="serial${i}" class="form-label">Serial #${i}</label>
                    <input type="text" id="serial${i}" class="form-control serial-input" placeholder="Enter serial number ${i}" required>
                </div>
            `;
            container.append(inputHtml);
        }

        $('#serialNumbersModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function rollbackNewProduct(productID) {
        $.ajax({
            url: 'backend/deleteProduct.php',
            type: 'POST',
            data: { id: productID },
            dataType: 'json'
        });
        closeSerialModal();
    }

    function saveSerialNumbers(productID, batchNumber, serialNumbers) {
        const isUpdate = (updateProductData !== null);

        // Extract parts and gas data from productFormData
        let partsData = {};
        let gasData = [];
        for (let i = 0; i < productFormData.length; i++) {
            if (productFormData[i].name === 'partsData') {
                partsData = JSON.parse(productFormData[i].value);
            }
            if (productFormData[i].name === 'gasData') {
                gasData = JSON.parse(productFormData[i].value);
            }
        }

        $.ajax({
            url: 'backend/saveSerialNumbers.php',
            type: 'POST',
            data: {
                productID: productID,
                batchNumber: batchNumber,
                serialNumbers: JSON.stringify(serialNumbers),
                partsData: JSON.stringify(partsData),
                gasData: JSON.stringify(gasData)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    closeSerialModal();
                    showSuccessAlert(isUpdate ? 'Product updated and serial numbers added successfully' : 'Product and serial numbers added successfully');
                    setTimeout(() => {
                        loadContent('addProduct');
                    }, 2000);
                } else {
                    if (!isUpdate) {
                        rollbackNewProduct(productID);
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to save serial numbers. No parts or gases were issued.'
                    });
                }
            },
            error: function() {
                if (!isUpdate) {
                    rollbackNewProduct(productID);
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to save serial numbers. No parts or gases were issued.'
                });
            }
        });
    }

    function openUpdateModal(product) {
        $('#updateProductID').val(product.productID);
        $('#updateProductName').text(product.productName);
        $('#currentQuantity').text(product.quantity);
        $('#currentCost').text('RS ' + parseFloat(product.cost || 0).toFixed(2));

        // Clear form
        $('#updateBatchNumber').val('');
        $('#updateQuantity').val('');
        $('#updateCost').val('');

        $('#updateProductModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeUpdateModal() {
        $('#updateProductModal').fadeOut(300);
        $('body').css('overflow', 'auto');
        $('#updateProductForm')[0].reset();
    }

    // Handle update form submission
    $('#updateProductForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: 'backend/updateProductBatch.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    closeUpdateModal();
                    // Open serial numbers modal for the new quantity
                    updateProductData = {
                        productID: response.productID,
                        batchNumber: response.batchNumber,
                        quantity: response.quantity
                    };
                    openSerialModalForUpdate(response.quantity, response.batchNumber);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update product'
                });
            }
        });
    });

    function deleteProduct(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'backend/deleteProduct.php',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                loadContent('addProduct');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete product'
                        });
                    }
                });
            }
        });
    }

    function viewProduct(product) {
        const totalCost = (product.quantity * product.cost).toFixed(2);
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
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Cost Per Unit</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">RS ${parseFloat(product.cost || 0).toFixed(2)}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Cost</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333; font-size: 18px; color: #4caf50;">RS ${parseFloat(totalCost || 0).toFixed(2)}</p>
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

    // Close modal when clicking outside
    $(document).on('click', '#viewProductModal', function(e) {
        if (e.target.id === 'viewProductModal') {
            closeViewModal();
        }
    });

    // Prevent modal close when clicking inside the content
    $(document).on('click', '#viewProductModal .form-container', function(e) {
        e.stopPropagation();
    });

    // Batch Logs Modal Functions
    function openBatchLogsModal(productID) {
        $.ajax({
            url: 'backend/getBatchLogs.php',
            type: 'GET',
            data: {
                productID: productID
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayBatchLogs(response.data, response.totals);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to fetch batch logs'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to fetch batch logs'
                });
            }
        });
    }

    function displayBatchLogs(batches, totals) {
        let html = `
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">S.No</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Batch Number</th>
                            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Quantity</th>
                            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Cost/Unit</th>
                            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Total Cost</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (batches.length === 0) {
            html += `
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #999;">No batch logs found</td>
                </tr>
            `;
        } else {
            batches.forEach((batch, index) => {
                const batchTotal = (batch.quantity * batch.cost).toFixed(2);
                const rowColor = index % 2 === 0 ? '#f9f9f9' : 'white';
                html += `
                    <tr style="background: ${rowColor};">
                        <td style="padding: 12px; border: 1px solid #ddd;">${index + 1}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: 500;">${batch.batchNumber}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">${batch.quantity}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">RS ${parseFloat(batch.cost).toFixed(2)}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right; font-weight: 600;">RS ${batchTotal}</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">${new Date(batch.createdAt).toLocaleDateString()}</td>
                    </tr>
                `;
            });
        }

        html += `
                    </tbody>
                    <tfoot>
                        <tr style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; font-weight: bold;">
                            <td colspan="2" style="padding: 15px; border: 1px solid #ddd; text-align: right;">TOTAL:</td>
                            <td style="padding: 15px; border: 1px solid #ddd; text-align: right; font-size: 16px;">${totals.totalQuantity}</td>
                            <td style="padding: 15px; border: 1px solid #ddd;"></td>
                            <td style="padding: 15px; border: 1px solid #ddd; text-align: right; font-size: 16px;">RS ${parseFloat(totals.totalCost).toFixed(2)}</td>
                            <td style="padding: 15px; border: 1px solid #ddd;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        $('#batchLogsContent').html(html);
        $('#batchLogsModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeBatchLogsModal() {
        $('#batchLogsModal').fadeOut(300);
        $('body').css('overflow', 'auto');
        $('#batchLogsContent').empty();
    }

    // Close batch logs modal when clicking outside
    $(document).on('click', '#batchLogsModal', function(e) {
        if (e.target.id === 'batchLogsModal') {
            closeBatchLogsModal();
        }
    });

    // Prevent modal close when clicking inside the content
    $(document).on('click', '#batchLogsModal .form-container', function(e) {
        e.stopPropagation();
    });
</script>

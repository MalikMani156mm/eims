<?php
require '../adminAuth.php';
require '../db.php';

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

// Fetch all models for dropdown
$models = [];
$modelsResult = $conn->query("SELECT * FROM models ORDER BY modelName ASC");
if ($modelsResult) {
    while ($row = $modelsResult->fetch_assoc()) {
        $models[] = $row;
    }
}

// Fetch all sizes for dropdown
$sizes = [];
$sizesResult = $conn->query("SELECT * FROM sizes ORDER BY sizeName ASC");
if ($sizesResult) {
    while ($row = $sizesResult->fetch_assoc()) {
        $sizes[] = $row;
    }
}

// Fetch all products with related information
$products = [];
$result = $conn->query("
    SELECT 
        p.*,
        c.categoryName,
        col.colorName,
        m.modelName,
        s.sizeName,
        r.regionName
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoriesID
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
    <!-- Add Product Form -->
    <div class="form-container">
        <h2>Add New Product</h2>
        <form id="addProductForm">
            <!-- Row 1: Product Name, Category, Color -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="productName" class="form-label">Product Name</label>
                    <input type="text" id="productName" name="productName" class="form-control" placeholder="Enter product name" required>
                </div>

                <div class="form-group">
                    <label for="categoryID" class="form-label">Category</label>
                    <select id="categoryID" name="categoryID" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['categoriesID']; ?>">
                                <?php echo htmlspecialchars($category['categoryName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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

            <!-- Row 2: Model, Size, Quantity -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="modelID" class="form-label">Model</label>
                    <select id="modelID" name="modelID" class="form-control" required>
                        <option value="">Select Model</option>
                        <?php foreach ($models as $model): ?>
                            <option value="<?php echo $model['modelID']; ?>">
                                <?php echo htmlspecialchars($model['modelName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sizeID" class="form-label">Size</label>
                    <select id="sizeID" name="sizeID" class="form-control" required>
                        <option value="">Select Size</option>
                        <?php foreach ($sizes as $size): ?>
                            <option value="<?php echo $size['sizeID']; ?>">
                                <?php echo htmlspecialchars($size['sizeName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" placeholder="Enter quantity" min="0" required>
                </div>
            </div>

            <!-- Row 3: Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea id="description" name="description" class="form-control" placeholder="Enter product description" rows="3"></textarea>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Products -->
    <div class="packages-table">
        <h3>Existing Products</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Color</th>
                        <th>Model</th>
                        <th>Quantity</th>
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
                                <td><?php echo htmlspecialchars($product['colorName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['modelName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-primary" style="padding: 8px 16px; margin-right: 5px;" onclick="viewProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">View</button>
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
<div id="viewProductModal" class="modal" style="display:none;">
    <div class="form-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <h2>Product Details</h2>
        <div id="productDetailsContent" style="margin-top: 20px;">
            <!-- Product details will be inserted here -->
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#addProductForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'backend/saveProduct.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
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
                        text: 'Failed to add product'
                    });
                }
            });
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
        const detailsHtml = `
            <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 15px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Product Name:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${product.productName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Category:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${product.categoryName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Color:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${product.colorName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Model:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${product.modelName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Size:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${product.sizeName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Quantity:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${product.quantity || '0'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Region:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${product.regionName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Created At:</label>
                        <p style="margin: 0; padding: 10px; background: white; border-radius: 8px;">${new Date(product.createdAt).toLocaleString()}</p>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <label style="font-weight: 600; color: #667eea; display: block; margin-bottom: 5px;">Description:</label>
                    <p style="margin: 0; padding: 10px; background: white; border-radius: 8px; min-height: 60px;">${product.description || 'No description available'}</p>
                </div>
            </div>
        `;
        
        $('#productDetailsContent').html(detailsHtml);
        $('#viewProductModal').fadeIn(300);
    }

    function closeViewModal() {
        $('#viewProductModal').fadeOut(300);
    }

    // Close modal when clicking outside
    $('#viewProductModal').on('click', function(e) {
        if (e.target.id === 'viewProductModal') {
            closeViewModal();
        }
    });
</script>
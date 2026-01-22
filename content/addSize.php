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

// Fetch all sizes with category names
$sizes = [];
$result = $conn->query("SELECT s.*, c.categoryName FROM sizes s LEFT JOIN categories c ON s.categoryID = c.categoriesID ORDER BY s.createdAt DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sizes[] = $row;
    }
}
?>

<div class="container">
    <!-- Add Size Form -->
    <div class="form-container">
        <h2>Add New Size</h2>
        <form id="addSizeForm">
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
                <label for="sizeName" class="form-label">Size Name</label>
                <input type="text" id="sizeName" name="sizeName" class="form-control" placeholder="Enter size name (e.g., 1 ton, 40 kg)" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Size</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Sizes -->
    <div class="packages-table">
        <h3>Existing Sizes</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Size Name</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="sizesTableBody">
                    <?php if (empty($sizes)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No sizes found</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($sizes as $size): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($size['sizeName']); ?></td>
                                <td><?php echo htmlspecialchars($size['categoryName'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($size['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn-delete" onclick="deleteSize(<?php echo $size['sizeID']; ?>)">Delete</button>
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
    $('#addSizeForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'backend/saveSize.php',
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
                        loadContent('addSize');
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
                    text: 'Failed to add size'
                });
            }
        });
    });
});

function deleteSize(id) {
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
                url: 'backend/deleteSize.php',
                type: 'POST',
                data: { id: id },
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
                            loadContent('addSize');
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
                        text: 'Failed to delete size'
                    });
                }
            });
        }
    });
}
</script>

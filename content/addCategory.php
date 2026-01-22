<?php
require '../adminAuth.php';
require '../db.php';

// Fetch all categories
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY createdAT DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<div class="container">
    <!-- Add Category Form -->
    <div class="form-container">
        <h2>Add New Category</h2>
        <form id="addCategoryForm">
            <div class="form-group">
                <label for="categoryName" class="form-label">Category Name</label>
                <input type="text" id="categoryName" name="categoryName" class="form-control" placeholder="Enter category name" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Category</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Categories -->
    <div class="packages-table">
        <h3>Existing Categories</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Category Name</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No categories found</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($category['categoryName']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($category['createdAT'])); ?></td>
                                <td class="text-center">
                                    <button class="btn-delete" onclick="deleteCategory(<?php echo $category['categoriesID']; ?>)">Delete</button>
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
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'backend/saveCategory.php',
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
                        loadContent('addCategory');
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
                    text: 'Failed to add category'
                });
            }
        });
    });
});

function deleteCategory(id) {
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
                url: 'backend/deleteCategory.php',
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
                            loadContent('addCategory');
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
                        text: 'Failed to delete category'
                    });
                }
            });
        }
    });
}
</script>

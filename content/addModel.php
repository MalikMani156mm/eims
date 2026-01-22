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

// Fetch all models with category names
$models = [];
$result = $conn->query("SELECT m.*, c.categoryName FROM models m LEFT JOIN categories c ON m.categoryID = c.categoriesID ORDER BY m.createdAt DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $models[] = $row;
    }
}
?>

<div class="container">
    <!-- Add Model Form -->
    <div class="form-container">
        <h2>Add New Model</h2>
        <form id="addModelForm">
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
                <label for="modelName" class="form-label">Model Name</label>
                <input type="text" id="modelName" name="modelName" class="form-control" placeholder="Enter model name" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Model</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Models -->
    <div class="packages-table">
        <h3>Existing Models</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Model Name</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="modelsTableBody">
                    <?php if (empty($models)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No models found</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($models as $model): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($model['modelName']); ?></td>
                                <td><?php echo htmlspecialchars($model['categoryName'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($model['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn-delete" onclick="deleteModel(<?php echo $model['modelID']; ?>)">Delete</button>
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
    $('#addModelForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'backend/saveModel.php',
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
                        loadContent('addModel');
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
                    text: 'Failed to add model'
                });
            }
        });
    });
});

function deleteModel(id) {
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
                url: 'backend/deleteModel.php',
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
                            loadContent('addModel');
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
                        text: 'Failed to delete model'
                    });
                }
            });
        }
    });
}
</script>

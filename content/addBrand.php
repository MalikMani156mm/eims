<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Fetch all brands
$brands = [];
$result = $conn->query("SELECT * FROM brands ORDER BY createdAt DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
}
?>

<div class="container">
    <!-- Add Brand Form -->
    <div class="form-container">
        <h2>Add New Brand</h2>
        <form id="addBrandForm">
            <div class="form-group">
                <label for="brandName" class="form-label">Brand Name</label>
                <input type="text" id="brandName" name="brandName" class="form-control" placeholder="Enter brand name" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Brand</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Brands -->
    <div class="packages-table">
        <h3>Existing Brands</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Brand Name</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="brandsTableBody">
                    <?php if (empty($brands)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No brands found</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($brands as $brand): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($brand['brandName']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($brand['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn-delete" onclick="deleteBrand(<?php echo $brand['brandID']; ?>)">Delete</button>
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
    $('#addBrandForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'backend/saveBrand.php',
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
                        loadContent('addBrand');
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
                    text: 'Failed to add brand'
                });
            }
        });
    });
});

function deleteBrand(id) {
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
                url: 'backend/deleteBrand.php',
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
                            loadContent('addBrand');
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
                        text: 'Failed to delete brand'
                    });
                }
            });
        }
    });
}
</script>

<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

$vendors = [];
$result = $conn->query("SELECT * FROM vendors ORDER BY createdAt DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vendors[] = $row;
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2>Add New Vendor</h2>
        <form id="addVendorForm">
            <div class="form-group">
                <label for="vendorName" class="form-label">Vendor Name</label>
                <input type="text" id="vendorName" name="vendorName" class="form-control" placeholder="Enter vendor name" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Vendor</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="packages-table">
        <h3>Existing Vendors</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Vendor Name</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="vendorsTableBody">
                    <?php if (empty($vendors)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No vendors found</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($vendor['vendorName']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($vendor['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn-delete" onclick="deleteVendor(<?php echo $vendor['vendorID']; ?>)">Delete</button>
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
    $('#addVendorForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'backend/saveVendor.php',
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
                        loadContent('addVendor');
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
                    text: 'Failed to add vendor'
                });
            }
        });
    });
});

function deleteVendor(id) {
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
                url: 'backend/deleteVendor.php',
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
                            loadContent('addVendor');
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
                        text: 'Failed to delete vendor'
                    });
                }
            });
        }
    });
}
</script>

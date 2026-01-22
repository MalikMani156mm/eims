<?php
require '../adminAuth.php';
require '../db.php';

// Fetch all regions
$regions = [];
$result = $conn->query("SELECT * FROM regions ORDER BY createdAt DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $regions[] = $row;
    }
}
?>

<div class="container">
    <!-- Add Region Form -->
    <div class="form-container">
        <h2>Add New Region</h2>
        <form id="addRegionForm">
            <div class="form-group">
                <label for="regionName" class="form-label">Region Name</label>
                <input type="text" id="regionName" name="regionName" class="form-control" placeholder="Enter region name" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Region</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Regions -->
    <div class="packages-table">
        <h3>Existing Regions</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Region Name</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="regionsTableBody">
                    <?php if (empty($regions)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No regions found</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($regions as $region): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($region['regionName']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($region['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn-delete" onclick="deleteRegion(<?php echo $region['regionID']; ?>)">Delete</button>
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
    $('#addRegionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'backend/saveRegion.php',
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
                        loadContent('addRegion');
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
                    text: 'Failed to add region'
                });
            }
        });
    });
});

function deleteRegion(id) {
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
                url: 'backend/deleteRegion.php',
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
                            loadContent('addRegion');
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
                        text: 'Failed to delete region'
                    });
                }
            });
        }
    });
}
</script>

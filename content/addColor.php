<?php
require '../adminAuth.php';
require '../db.php';

// Fetch all colors
$colors = [];
$result = $conn->query("SELECT * FROM colors ORDER BY createdAT DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $colors[] = $row;
    }
}
?>

<div class="container">
    <!-- Add Color Form -->
    <div class="form-container">
        <h2>Add New Color</h2>
        <form id="addColorForm">
            <div class="form-group">
                <label for="colorName" class="form-label">Color Name</label>
                <input type="text" id="colorName" name="colorName" class="form-control" placeholder="Enter color name" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Color</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Colors -->
    <div class="packages-table">
        <h3>Existing Colors</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Color Name</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="colorsTableBody">
                    <?php if (empty($colors)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No colors found</td>
                        </tr>
                    <?php else: ?>
                        <?php $serial = 1; foreach ($colors as $color): ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($color['colorName']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($color['createdAT'])); ?></td>
                                <td class="text-center">
                                    <button class="btn-delete" onclick="deleteColor(<?php echo $color['colorID']; ?>)">Delete</button>
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
    $('#addColorForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'backend/saveColor.php',
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
                        loadContent('addColor');
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
                    text: 'Failed to add color'
                });
            }
        });
    });
});

function deleteColor(id) {
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
                url: 'backend/deleteColor.php',
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
                            loadContent('addColor');
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
                        text: 'Failed to delete color'
                    });
                }
            });
        }
    });
}
</script>

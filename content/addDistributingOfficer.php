<?php
require '../adminAuth.php';
require '../db.php';

// Fetch regions for dropdown
$regions = [];
$rres = $conn->query("SELECT regionID, regionName FROM regions ORDER BY regionName");
if ($rres) {
    while ($rrow = $rres->fetch_assoc()) {
        $regions[] = $rrow;
    }
}

// Determine auth user's region name
$authRegionName = '';
foreach ($regions as $r) {
    if ((int)$r['regionID'] === (int)$regionID) {
        $authRegionName = $r['regionName'];
        break;
    }
}

// Fetch distributing officers — include regionName via JOIN; restrict to auth region for non-superadmin
$officers = [];
if (isset($adminRole) && $adminRole === 'superadmin') {
    $resOff = $conn->query("SELECT d.*, r.regionName FROM distributing_officer d LEFT JOIN regions r ON d.regionID = r.regionID ORDER BY d.createdAt DESC");
} else {
    $stmtOff = $conn->prepare("SELECT d.*, r.regionName FROM distributing_officer d LEFT JOIN regions r ON d.regionID = r.regionID WHERE d.regionID = ? ORDER BY d.createdAt DESC");
    $stmtOff->bind_param('i', $regionID);
    $stmtOff->execute();
    $resOff = $stmtOff->get_result();
    $stmtOff->close();
}
if ($resOff) {
    while ($row = $resOff->fetch_assoc()) {
        $officers[] = $row;
    }
}
?>

<div class="container">
    <!-- Add Officer Form -->
    <div class="form-container">
        <h2>Add Distributing Officer</h2>
        
        <form id="addOfficerForm">
            <!-- Row 1: Name, CNIC -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name" required>
                </div>

                <div class="form-group">
                    <label for="cnic" class="form-label">CNIC</label>
                    <input type="text" id="cnic" name="cnic" class="form-control" placeholder="XXXXX-XXXXXXX-X" required maxlength="15">
                </div>
            </div>

            <!-- Row 2: Contact Number, Status -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="contactNumber" class="form-label">Contact Number</label>
                    <input type="text" id="contactNumber" name="contactNumber" class="form-control" placeholder="03XX-XXXXXXX" required>
                </div>

                <div class="form-group">
                    <?php if (isset($adminRole) && $adminRole === 'superadmin'): ?>
                        <label for="regionID" class="form-label">Region</label>
                        <select id="regionID" name="regionID" class="form-control" required>
                            <?php foreach ($regions as $reg): ?>
                                <option value="<?php echo (int)$reg['regionID']; ?>"><?php echo htmlspecialchars($reg['regionName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <label class="form-label">Region</label>
                        <div style="padding:8px 10px; background:#f5f5f5; border-radius:6px; color:#333"><?php echo htmlspecialchars($authRegionName ?: 'N/A'); ?></div>
                        <input type="hidden" id="regionID" name="regionID" value="<?php echo (int)$regionID; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="status" value="1">
                </div>
            </div>

            <!-- Row 3: Address (Full Width) -->
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3" placeholder="Enter complete address" required></textarea>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add Officer</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Officers -->
    <div class="packages-table">
        <h3>All Distributing Officers</h3>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Name</th>
                        <th>CNIC</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Region</th>
                        <th>Status</th>
                        <th>Added On</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="officersTableBody">
                    <?php if (empty($officers)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No distributing officers found</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $serial = 1;
                        $today = date('Y-m-d');
                        foreach ($officers as $officer): 
                            $officerDate = date('Y-m-d', strtotime($officer['createdAt']));
                            $isToday = ($officerDate === $today);
                            $isActive = ($officer['status'] == 0);
                        ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><strong><?php echo htmlspecialchars($officer['DO_Name']); ?></strong></td>
                                <td>
                                    <code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        <?php echo htmlspecialchars($officer['CNIC']); ?>
                                    </code>
                                </td>
                                <td><?php echo htmlspecialchars($officer['contactNumber']); ?></td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($officer['Address']); ?>
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($officer['regionName']); ?>
                                </td>
                                <td>
                                    <span style="background: <?php echo $isActive ? '#4caf50' : '#f44336'; ?>; color: white; padding: 5px 12px; border-radius: 5px; font-weight: bold; font-size: 12px;">
                                        <?php echo $isActive ? '✓ Active' : '✗ Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($officer['createdAt'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-primary" style="padding: 8px 16px; margin-right: 5px;" 
                                        onclick="viewOfficer(<?php echo htmlspecialchars(json_encode($officer)); ?>)">
                                        View
                                    </button>
                                    <button class="btn btn-warning" style="padding: 8px 16px; margin-right: 5px; background: #ff9800;" 
                                        onclick="openEditModal(<?php echo htmlspecialchars(json_encode($officer)); ?>)">
                                        Edit
                                    </button>
                                        <!-- Deactivate button removed per UX change -->
                                    <?php if ($isToday): ?>
                                        <button class="btn-delete" onclick="deleteOfficer(<?php echo $officer['DO_ID']; ?>)">
                                            Delete
                                        </button>
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

<!-- View Officer Modal -->
<div id="viewOfficerModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 600px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px 16px 0 0; z-index: 10; color: white;">
            <h2 style="margin: 0;">Officer Details</h2>
            <button onclick="closeViewModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px;">&times;</button>
        </div>
        <div id="officerDetailsContent" style="padding: 20px;">
            <!-- Officer details will be inserted here -->
        </div>
    </div>
</div>

<!-- Edit Officer Modal -->
<div id="editOfficerModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 600px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%); padding: 20px; border-radius: 16px 16px 0 0; z-index: 10; color: white;">
            <h2 style="margin: 0;">Edit Officer</h2>
            <button onclick="closeEditModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px;">&times;</button>
        </div>
        <div style="padding: 20px;">
            <form id="editOfficerForm">
                <input type="hidden" id="editOfficerID" name="officerID">
                
                <!-- Name (Read-only) -->
                <div class="form-group">
                    <label for="editName" class="form-label" style="color: black;">Full Name (Cannot be changed)</label>
                    <input type="text" id="editName" class="form-control" readonly style="background: #f5f5f5; cursor: not-allowed;">
                </div>

                <!-- CNIC -->
                <div class="form-group">
                    <label for="editCnic" class="form-label" style="color: black;">CNIC <span style="color: red;">*</span></label>
                    <input type="text" id="editCnic" name="cnic" class="form-control" required maxlength="15">
                </div>

                <!-- Contact Number -->
                <div class="form-group">
                    <label for="editContactNumber" class="form-label" style="color: black;">Contact Number <span style="color: red;">*</span></label>
                    <input type="text" id="editContactNumber" name="contactNumber" class="form-control" required>
                </div>

                <!-- Region (replace status) -->
                <div class="form-group">
                    <?php if (isset($adminRole) && $adminRole === 'superadmin'): ?>
                        <label for="editRegion" class="form-label" style="color: black;">Region <span style="color: red;">*</span></label>
                        <select id="editRegion" name="regionID" class="form-control" required>
                            <?php foreach ($regions as $reg): ?>
                                <option value="<?php echo (int)$reg['regionID']; ?>"><?php echo htmlspecialchars($reg['regionName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <label class="form-label">Region</label>
                        <div style="padding:8px 10px; background:#f5f5f5; border-radius:6px; color:#333"><?php echo htmlspecialchars($authRegionName ?: 'N/A'); ?></div>
                        <input type="hidden" id="editRegionHidden" name="regionID" value="<?php echo (int)$regionID; ?>">
                    <?php endif; ?>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="editAddress" class="form-label" style="color: black;">Address <span style="color: red;">*</span></label>
                    <textarea id="editAddress" name="address" class="form-control" rows="3" required></textarea>
                </div>

                <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-success" style="padding: 12px 30px;">Update Officer</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()" style="padding: 12px 30px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Add CNIC formatting
        $('#cnic, #editCnic').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 5) {
                value = value.slice(0, 5) + '-' + value.slice(5);
            }
            if (value.length > 13) {
                value = value.slice(0, 13) + '-' + value.slice(13);
            }
            $(this).val(value.slice(0, 15));
        });

        // Add Officer Form Submit
        $('#addOfficerForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'backend/saveDistributingOfficer.php',
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
                            loadContent('addDistributingOfficer');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to add officer. Check console for details.',
                        footer: '<small>' + error + '</small>'
                    });
                }
            });
        });

        // Edit Officer Form Submit
        $('#editOfficerForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'backend/updateDistributingOfficer.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            closeEditModal();
                            loadContent('addDistributingOfficer');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update officer. Check console for details.',
                        footer: '<small>' + error + '</small>'
                    });
                }
            });
        });
    });

    function viewOfficer(officer) {
        const isActive = officer.status == 0;
        const detailsHtml = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; color: white;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="font-size: 12px; opacity: 0.9; margin-bottom: 5px; display: block;">FULL NAME</label>
                        <p style="margin: 0; font-size: 18px; font-weight: bold;">${officer.DO_Name || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-size: 12px; opacity: 0.9; margin-bottom: 5px; display: block;">STATUS</label>
                        <p style="margin: 0; font-size: 18px; font-weight: bold;">${isActive ? '✓ Active' : '✗ Inactive'}</p>
                    </div>
                </div>
            </div>

            <div style="display: grid; gap: 15px;">
                <div style="background: #f8f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
                    <label style="font-size: 12px; color: #999; margin-bottom: 5px; display: block;">CNIC</label>
                    <p style="margin: 0; font-size: 16px; font-weight: 600;">${officer.CNIC || 'N/A'}</p>
                </div>

                <div style="background: #f8f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #11998e;">
                    <label style="font-size: 12px; color: #999; margin-bottom: 5px; display: block;">CONTACT NUMBER</label>
                    <p style="margin: 0; font-size: 16px; font-weight: 600;">${officer.contactNumber || 'N/A'}</p>
                </div>

                <div style="background: #f8f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #f093fb;">
                    <label style="font-size: 12px; color: #999; margin-bottom: 5px; display: block;">ADDRESS</label>
                    <p style="margin: 0; font-size: 14px;">${officer.Address || 'N/A'}</p>
                </div>

                <div style="background: #f8f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #ff9800;">
                    <label style="font-size: 12px; color: #999; margin-bottom: 5px; display: block;">ADDED ON</label>
                    <p style="margin: 0; font-size: 14px;">${new Date(officer.createdAt).toLocaleString()}</p>
                </div>
            </div>
        `;
        
        $('#officerDetailsContent').html(detailsHtml);
        $('#viewOfficerModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeViewModal() {
        $('#viewOfficerModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    }

    function openEditModal(officer) {
        $('#editOfficerID').val(officer.DO_ID);
        $('#editName').val(officer.DO_Name);
        $('#editCnic').val(officer.CNIC);
        $('#editContactNumber').val(officer.contactNumber);
        if ($('#editRegion').length) {
            $('#editRegion').val(officer.regionID || '');
        }
        if ($('#editRegionHidden').length) {
            $('#editRegionHidden').val(officer.regionID || '<?php echo (int)$regionID; ?>');
        }
        $('#editAddress').val(officer.Address);
        
        $('#editOfficerModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeEditModal() {
        $('#editOfficerModal').fadeOut(300);
        $('body').css('overflow', 'auto');
        $('#editOfficerForm')[0].reset();
    }

    // Deactivate flow removed — status is now managed differently

    function deleteOfficer(officerID) {
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
                    url: 'backend/deleteDistributingOfficer.php',
                    type: 'POST',
                    data: { officerID: officerID },
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
                                loadContent('addDistributingOfficer');
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
                            text: 'Failed to delete officer'
                        });
                    }
                });
            }
        });
    }

    // Close modals when clicking outside
    $(document).on('click', '#viewOfficerModal, #editOfficerModal', function(e) {
        if (e.target.id === 'viewOfficerModal') {
            closeViewModal();
        } else if (e.target.id === 'editOfficerModal') {
            closeEditModal();
        }
    });
</script>

<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Fetch all regions for dropdown
$regions = [];
$regionsResult = $conn->query("SELECT * FROM regions ORDER BY regionName ASC");
if ($regionsResult) {
    while ($row = $regionsResult->fetch_assoc()) {
        $regions[] = $row;
    }
}

// Fetch all users
$users = [];
$result = $conn->query("
    SELECT 
        u.*,
        r.regionName
    FROM users u
    LEFT JOIN regions r ON u.regionID = r.regionID
    ORDER BY u.created_at DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<div class="container">
    <!-- Add User Form -->
    <div class="form-container">
        <h2>Add New User</h2>
        <form id="addUserForm">
            <!-- Row 1: Full Name, Username, Email -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter full name" required>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email">
                </div>
            </div>

            <!-- Row 2: Password, Phone, Region -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter phone number">
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="superadmin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>

            <!-- Row 3: Role, Dashboard Access -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label for="regionID" class="form-label">Region (select one or more — press & hold Ctrl key)</label>
                    <select id="regionID" name="regionID[]" class="form-control" required multiple style="min-height:100px;">
                        <?php foreach ($regions as $region): ?>
                            <option value="<?php echo $region['regionID']; ?>">
                                <?php echo htmlspecialchars($region['regionName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- <div class="form-group">
                    <label for="is_active" class="form-label">Status</label>
                    <select id="is_active" name="is_active" class="form-control" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div> -->
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Add User</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- Display Users -->
    <div class="packages-table">
        <h3>Existing Users</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Region</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $serial = 1;
                        $today = date('Y-m-d');
                        foreach ($users as $user): 
                            $userDate = date('Y-m-d', strtotime($user['created_at']));
                            $isToday = ($userDate === $today);
                        ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                <td><?php echo htmlspecialchars($user['regionName'] ?? 'N/A'); ?></td>
                                <td>
                                    <span style="padding: 5px 10px; border-radius: 5px; background: <?php echo $user['is_active'] ? '#4caf50' : '#f44336'; ?>; color: white;">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-primary" style="padding: 8px 16px; margin-right: 5px;" onclick="viewUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">View</button>
                                    <?php if ($isToday): ?>
                                        <button class="btn-delete" onclick="deleteUser(<?php echo $user['user_id']; ?>)">Delete</button>
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

<!-- View User Modal -->
<div id="viewUserModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: white; padding: 20px; border-bottom: 2px solid #f0f0f0; border-radius: 16px 16px 0 0; z-index: 10;">
            <h2 style="margin: 0; color: #667eea;">User Details</h2>
            <button onclick="closeUserViewModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <div id="userDetailsContent" style="padding: 20px;">
            <!-- User details will be inserted here -->
        </div>
        <div style="bottom: 0; background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeUserViewModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'backend/saveUser.php',
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
                            loadContent('addUser');
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
                        text: 'Failed to add user'
                    });
                }
            });
        });
    });

    function deleteUser(id) {
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
                    url: 'backend/deleteUser.php',
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
                                loadContent('addUser');
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
                            text: 'Failed to delete user'
                        });
                    }
                });
            }
        });
    }

    function viewUser(user) {
        const detailsHtml = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Full Name</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.full_name || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Username</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.username || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Email</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.email || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Phone</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.phone || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Role</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Region</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.regionName || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Dashboard Access</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.dashboard_access || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${user.is_active == '1' ? 'Active' : 'Inactive'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Created At</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${new Date(user.created_at).toLocaleString()}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; color: white; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Updated At</label>
                        <p style="margin: 0; padding: 12px; background: white; border-radius: 8px; font-weight: 500; color: #333;">${new Date(user.updated_at).toLocaleString()}</p>
                    </div>
                </div>
            </div>
        `;
        
        $('#userDetailsContent').html(detailsHtml);
        $('#viewUserModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeUserViewModal() {
        $('#viewUserModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    }

    // Close modal when clicking outside
    $(document).on('click', '#viewUserModal', function(e) {
        if (e.target.id === 'viewUserModal') {
            closeUserViewModal();
        }
    });

    // Prevent modal close when clicking inside the content
    $(document).on('click', '#viewUserModal .form-container', function(e) {
        e.stopPropagation();
    });
</script>

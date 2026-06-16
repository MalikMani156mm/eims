<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
require 'adminAuth.php';
require 'db.php';

$regionName = '';
if (isset($regionID) && $regionID) {
    $stmtReg = $conn->prepare("SELECT regionName FROM regions WHERE regionID = ? LIMIT 1");
    if ($stmtReg) {
        $stmtReg->bind_param('i', $regionID);
        $stmtReg->execute();
        $resReg = $stmtReg->get_result();
        if ($resReg && $rowReg = $resReg->fetch_assoc()) {
            $regionName = $rowReg['regionName'];
        }
        $stmtReg->close();
    }
}

// Check if user has multiple regions and fetch available regions
$userRegions = [];
$haveMultipleRegions = 0;
$currentRegionID = isset($regionID) ? $regionID : 0;

if (isset($ID)) {
    $checkStmt = $conn->prepare("SELECT haveMultipleRegions FROM users WHERE user_id = ?");
    if ($checkStmt) {
        $checkStmt->bind_param('i', $ID);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $row = $checkRes->fetch_assoc()) {
            $haveMultipleRegions = intval($row['haveMultipleRegions']);
        } else {
            error_log("No user found for user_id: " . $ID);
        }
        $checkStmt->close();
    } else {
        error_log("Prepare failed: " . $conn->error);
    }
} else {
    error_log("user_id is not set in session");
}

// Fetch available regions for this user from user_regions table
if ($haveMultipleRegions === 1) {
    $regionStmt = $conn->prepare("
            SELECT ur.regionID, r.regionName 
            FROM user_regions ur 
            LEFT JOIN regions r ON ur.regionID = r.regionID 
            WHERE ur.user_id = ? 
            ORDER BY r.regionName ASC
        ");
    if ($regionStmt) {
        $regionStmt->bind_param('i', $ID);
        $regionStmt->execute();
        $regionRes = $regionStmt->get_result();
        while ($regRow = $regionRes->fetch_assoc()) {
            $userRegions[] = $regRow;
        }
        $regionStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="assets/css/select2.min.css">
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/select2.min.js"></script>
    <script src="section.js"></script>
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="nav-left">
            <div class="nav-logo-container">
                <button class="burger-menu" onclick="toggleSidebar()" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <img src="logo.png" alt="Logo" class="nav-logo" onerror="this.style.display='none'">
                <div class="nav-text">
                    <h1>ARK Cool Tech Management System</h1>
                    <span class="dashboard-title">Dashboard</span>
                </div>
            </div>
        </div>
        <div class="nav-right">
            <span class="user-info">
                <strong id="userName"><?php echo htmlspecialchars(isset($fullName) && $fullName ? $fullName : (isset($adminName) ? $adminName : 'Admin')); ?></strong>
                <div>
                    <span class="user-role" id="userRole"><?php echo htmlspecialchars(isset($adminRole) && $adminRole ? ucfirst($adminRole) : 'Administrator'); ?></span>
                    <span class="user-role" id="userRole"><?php echo htmlspecialchars(isset($regionName) && $regionName ? ucfirst($regionName) : 'Office'); ?></span>
                </div>
                <!-- <div class="user-region" style="display:block; color: rgba(255,255,255,0.85); font-size:13px; margin-top:2px;">Region: <?php echo htmlspecialchars($regionName ?: 'N/A'); ?></div> -->
            </span>
            <button onclick="openChangePasswordModal()" class="nav-button" style="background-color: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.3s ease;">Change Password</button>
            <?php if ($haveMultipleRegions === 1): ?>
                <button onclick="showRegionSelectionModal()" class="nav-button" style="background-color: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.3s ease;">Change Region</button>
            <?php endif; ?>
            <button onclick="handleLogout()" class="btn-logout">Logout</button>
        </div>
    </nav>


    <!-- Main Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar sidebar-hidden">
            <div class="menu-section">
                <h3>Menu</h3>
                <div class="menu-item active" onclick="loadContent('default')">
                    <span class="icon">🏠</span>
                    <span>Home</span>
                </div>
                <?php if ($adminRole != 'user' ): ?>
                <?php if ($adminRole === 'superadmin' ): ?>
                    <div class="menu-item active" onclick="loadContent('inventory')">
                        <span class="icon">📦</span>
                        <span>Inventory</span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('batchHistory')">
                        <span class="icon">🕒</span>
                        <span>Batch History</span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('searchSerialNumbers')">
                        <span class="icon">🔍</span>
                        <span>Search Serial Numbers</span>
                    </div>
                    <?php endif ?>
                    <div class="menu-item active" onclick="loadContent('gases')">
                        <span class="icon">⛽</span>
                        <span>Gases Logs</span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('vendorLogs')">
                        <span class="icon">📝</span>
                        <span>Vendor Logs</span>
                    </div>
                <?php endif ?>
                <div class="menu-item active" onclick="loadContent('parts')">
                    <span class="icon">📦</span>
                    <span>Parts</span>
                </div>
                <?php if ($adminRole === 'admin'): ?>
                    <div class="menu-item active" onclick="loadContent('assemblingApprovalAdmin')">
                        <span class="icon">✓</span>
                        <span>Assembling Approval</span>
                        <span class="badge-count" id="assemblingApprovalAdminCount" style="display: none; margin-left:8px; background:#d9534f; color:#fff; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; vertical-align:middle;"></span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('dispatchDOApprovalAdmin')">
                        <span class="icon">✓</span>
                        <span>Dispatch Approval</span>
                        <span class="badge-count" id="dispatchDOApprovalAdminCount" style="display: none; margin-left:8px; background:#d9534f; color:#fff; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; vertical-align:middle;"></span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('addGas')">
                        <span class="icon">➕</span>
                        <span>Add Gas</span>
                    </div>
                <?php endif ?>
                <?php if ($adminRole === 'user' || $adminRole === 'admin'): ?>
                    <div class="menu-item active" onclick="loadContent('addParts')">
                        <span class="icon">➕</span>
                        <span>Add Parts</span>
                    </div>
                <?php endif ?>
                <?php if ($adminRole === 'user'): ?>
                    <div class="menu-item active" onclick="loadContent('addProduct')">
                        <span class="icon">📤</span>
                        <span>Issued for Assembling</span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('assemblingLogs')">
                        <span class="icon">📝</span>
                        <span>Assembling Logs</span>
                    </div>
                <?php endif ?>
                <?php if ($adminRole === 'superadmin'): ?>
                    <div class="menu-item active" onclick="loadContent('doApproval')">
                        <span class="icon">✓</span>
                        <span>Warehouse Approval</span>
                        <span class="badge-count" id="doApprovalCount" style="display: none; margin-left:8px; background:#d9534f;
                        color:#fff; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; vertical-align:middle;">
                        </span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('dispatchDOApproval')">
                        <span class="icon">✓</span>
                        <span>Dispatch Approval</span>
                        <span class="badge-count" id="dispatchDOApprovalCount" style="display: none; margin-left:8px; background:#d9534f; color:#fff; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; vertical-align:middle;"></span>
                    </div>
                    <div class="menu-item active" onclick="loadContent('addUser')">
                        <span class="icon">➕</span>
                        <span>Add User</span>
                    </div>
                <?php endif ?>
                
                <?php if ($adminRole === 'admin' || $adminRole === 'superadmin'): ?>
                    <div class="menu-item" onclick="toggleDropdown(this)">
                        <span class="icon">🚚</span>
                        <span>Dispatches Logs</span>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <div class="submenu">
                        <?php if ($adminRole === 'admin'): ?>
                            <div class="submenu-item" onclick="loadContent('dispatchToDO')">
                                <span class="icon">📤</span>
                                <span>Dispatch to Warehous</span>
                            </div>
                        <?php endif; ?>
                        <div class="submenu-item" onclick="loadContent('doDispatchHistory')">
                            <span class="icon">📚</span>
                            <span>Ledger</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('doPending')">
                            <span class="icon">⏳</span>
                            <span>Pendings</span>
                            <span class="badge-count" id="overdueCountDO" style="display: none;"></span>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($adminRole === 'admin'): ?>
                    <div class="menu-item" onclick="toggleDropdown(this)">
                        <span class="icon">⚙️</span>
                        <span>Configure</span>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <div class="submenu">
                        <div class="submenu-item" onclick="loadContent('addBrand')">
                            <span class="icon">➕</span>
                            <span>Add Brands</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addModel')">
                            <span class="icon">➕</span>
                            <span>Add Model</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addSize')">
                            <span class="icon">➕</span>
                            <span>Add Tonnage</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addColor')">
                            <span class="icon">➕</span>
                            <span>Add Color</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addVendor')">
                            <span class="icon">➕</span>
                            <span>Add Vendor</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addDistributingOfficer')">
                            <span class="icon">➕</span>
                            <span>Add Ware House</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="content-area"></main>
    </div>
    <div id="changePasswordModal" class="modal" style="display:none;">
        <div id="updatePasswordCard" class="form-container" style="max-width: 500px;">
            <h2>Change Password</h2>
            <form id="changePasswordForm">
                <div class="mb-3">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <input id="currentPassword" class="form-control" type="password" name="currentPassword" placeholder="Current Password" required>
                </div>

                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input id="newPassword" class="form-control" type="password" name="newPassword" placeholder="New Password" required>
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input id="confirmPassword" class="form-control" type="password" name="confirmPassword" placeholder="Confirm Password" required>
                </div>

                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button class="btn btn-primary" type="submit">Update Password</button>
                    <button class="btn btn-secondary" type="button" onclick="closeChangePasswordModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Region Selection Modal -->
    <div id="regionSelectionModal" class="modal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; overflow:auto;">
        <div style="background:white; border-radius:12px; max-width:500px; margin:10% auto; padding:30px; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
            <h2 style="margin:0 0 20px 0; color:#333; text-align:center;">Select Your Region</h2>
            <p style="color:#666; text-align:center; margin-bottom:25px;">You have access to multiple regions. Please select the region you want to work with:</p>

            <div class="form-group" style="margin-bottom:25px;">
                <label for="availableRegionSelect" style="display:block; margin-bottom:8px; font-weight:600; color:#333;">Available Regions</label>
                <select id="availableRegionSelect" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                    <option value="">-- Select a Region --</option>
                    <?php foreach ($userRegions as $region): ?>
                        <option value="<?php echo $region['regionID']; ?>" <?php echo ($region['regionID'] == $currentRegionID) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($region['regionName']); ?>
                            <?php echo ($region['regionID'] == $currentRegionID) ? ' (Currently Selected)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; gap:12px; justify-content:center;">
                <button onclick="confirmRegionSelection()" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; padding:12px 30px; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px;">Confirm</button>
                <button onclick="closeRegionSelectionModal()" style="background:#f0f0f0; color:#333; padding:12px 30px; border:1px solid #ddd; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px;">Cancel</button>
            </div>
        </div>
    </div>

    <script src="logout.js"></script>
    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const burger = document.querySelector('.burger-menu');
            sidebar.classList.toggle('sidebar-hidden');
            burger.classList.toggle('active');

            // When sidebar is visible (not hidden), burger should be active (X)
            // When sidebar is hidden, burger should not be active (hamburger)
            if (!sidebar.classList.contains('sidebar-hidden')) {
                burger.classList.add('active');
            } else {
                burger.classList.remove('active');
            }
        }

        // Toggle dropdown menu
        function toggleDropdown(element) {
            const submenu = element.nextElementSibling;
            const arrow = element.querySelector('.dropdown-arrow');

            if (submenu && submenu.classList.contains('submenu')) {
                submenu.classList.toggle('show');
                if (arrow) {
                    arrow.style.transform = submenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
                }
            }
        }

        // Menu navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.menu-item:not([onclick*="toggleDropdown"])');
            const submenuItems = document.querySelectorAll('.submenu-item');

            // Handle main menu items
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(mi => mi.classList.remove('active'));
                    submenuItems.forEach(si => si.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Handle submenu items
            submenuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                    menuItems.forEach(mi => mi.classList.remove('active'));
                    submenuItems.forEach(si => si.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Load home content on page load for all roles
            loadContent('default');

            // Load overdue count for sidebar badge
            updateOverdueCount();
            // Load pending DO approval count
            updateDOApprovalCount();
            // Load pending dispatch approval counts
            updateDispatchAdminCount();
            updateDispatchSuperCount();
            updateAssemblingApprovalAdminCount();

            // Refresh overdue count every 5 minutes
            setInterval(updateOverdueCount, 300000);
            // Refresh pending DO approval count every 5 minutes
            setInterval(updateDOApprovalCount, 300000);
            // Refresh pending dispatch approval counts every 5 minutes
            setInterval(updateDispatchAdminCount, 300000);
            setInterval(updateDispatchSuperCount, 300000);
            setInterval(updateAssemblingApprovalAdminCount, 300000);
        });

        function updateOverdueCount() {
            // Update DO overdue count
            $.ajax({
                url: 'backend/getOverdueCount.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.overdueCount > 0) {
                        $('#overdueCountDO').text(response.overdueCount).show();
                    } else {
                        $('#overdueCountDO').hide();
                    }
                }
            });

            // Update Ledger overdue count
            $.ajax({
                url: 'backend/getLedgerOverdueCount.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.overdueCount > 0) {
                        $('#overdueCountLedger').text(response.overdueCount).show();
                    } else {
                        $('#overdueCountLedger').hide();
                    }
                }
            });
        }

        function updateDOApprovalCount() {
            $.ajax({
                url: 'backend/getPendingDOCount.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const badge = $('#doApprovalCount');
                    if (response && response.success && response.count > 0) {
                        badge.text(response.count).show();
                    } else {
                        badge.hide();
                    }
                },
                error: function() {
                    // Fail silently
                }
            });
        }

        function updateDispatchAdminCount() {
            $.ajax({
                url: 'backend/getPendingDispatchAdminCount.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const badge = $('#dispatchDOApprovalAdminCount');
                    if (response && response.success && response.count > 0) {
                        badge.text(response.count).show();
                    } else {
                        badge.hide();
                    }
                },
                error: function() {
                    // silent
                }
            });
        }

        function updateDispatchSuperCount() {
            $.ajax({
                url: 'backend/getPendingDispatchSuperCount.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const badge = $('#dispatchDOApprovalCount');
                    if (response && response.success && response.count > 0) {
                        badge.text(response.count).show();
                    } else {
                        badge.hide();
                    }
                },
                error: function() {
                    // silent
                }
            });
        }

        function updateAssemblingApprovalAdminCount() {
            $.ajax({
                url: 'backend/getPendingAssemblingApprovalCount.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const badge = $('#assemblingApprovalAdminCount');
                    if (response && response.success && response.count > 0) {
                        badge.text(response.count).show();
                    } else {
                        badge.hide();
                    }
                },
                error: function() {
                    // silent
                }
            });
        }

        function openChangePasswordModal() {
            $('#changePasswordModal').show();
        }

        function closeChangePasswordModal() {
            $('#changePasswordModal').hide();
            $('#changePasswordForm')[0].reset();
        }

        // Region Selection Modal Functions
        function showRegionSelectionModal() {
            $('#regionSelectionModal').show();
            $('body').css('overflow', 'hidden');
        }

        function closeRegionSelectionModal() {
            $('#regionSelectionModal').hide();
            $('body').css('overflow', 'auto');
        }

        function confirmRegionSelection() {
            const selectedRegionID = $('#availableRegionSelect').val();

            if (!selectedRegionID) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please select a region',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            // Save the selected region to the backend
            $.ajax({
                url: 'backend/updateUserRegion.php',
                type: 'POST',
                data: {
                    regionID: selectedRegionID
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Store flag in localStorage
                        localStorage.setItem('regionChanged', 'true');
                        closeRegionSelectionModal();
                        // Reload page to reflect region change
                        location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to update region'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Server error while updating region'
                    });
                }
            });
        }

        // Initialize region selection on page load
        $(document).ready(function() {
            // Check if user has multiple regions and localStorage doesn't have the flag
            const haveMultipleRegions = <?php echo $haveMultipleRegions; ?>;
            const regionChanged = localStorage.getItem('regionChanged');

            console.log('haveMultipleRegions:', haveMultipleRegions);
            console.log('regionChanged flag:', regionChanged);

            if (haveMultipleRegions === 1 && regionChanged !== 'true') {
                setTimeout(() => {
                    showRegionSelectionModal();
                }, 500);
            }
        });

        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.post('updatePassword.php', formData, function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    closeChangePasswordModal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            }, 'json');
        });
    </script>
</body>

</html>
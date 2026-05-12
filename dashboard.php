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
                <div class="menu-item active" onclick="loadContent('inventory')">
                    <span class="icon">📦</span>
                    <span>Inventory</span>
                </div>
                <div class="menu-item active" onclick="loadContent('parts')">
                    <span class="icon">📦</span>
                    <span>Parts</span>
                </div>
                <div class="menu-item active" onclick="loadContent('batchHistory')">
                    <span class="icon">🕒</span>
                    <span>Batch History</span>
                </div>
                <div class="menu-item active" onclick="loadContent('searchSerialNumbers')">
                    <span class="icon">🔍</span>
                    <span>Search Serial Numbers</span>
                </div>
                <?php if ($adminRole === 'superadmin'): ?>
                <div class="menu-item active" onclick="loadContent('doApproval')">
                    <span class="icon">✓</span>
                    <span>Add DO Approval</span>
                    <span class="badge-count" id="doApprovalCount" style="display: none; margin-left:8px; background:#d9534f;
                        color:#fff; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; vertical-align:middle;">
                    </span>
                </div>
                <div class="menu-item active" onclick="loadContent('dispatchDOApproval')">
                    <span class="icon">✓</span>
                    <span>Dispatch DO Approval</span>
                    <span class="badge-count" id="dispatchDOApprovalCount" style="display: none; margin-left:8px; background:#d9534f; color:#fff; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; vertical-align:middle;"></span>
                </div>
                <?php endif ?>
                <?php if ($adminRole === 'admin'): ?>
                <div class="menu-item active" onclick="loadContent('dispatchDOApprovalAdmin')">
                    <span class="icon">✓</span>
                    <span>Dispatch DO Approval</span>
                    <span class="badge-count" id="dispatchDOApprovalAdminCount" style="display: none; margin-left:8px; background:#d9534f; color:#fff; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; vertical-align:middle;"></span>
                </div>
                <?php endif ?>
                <div class="menu-item" onclick="toggleDropdown(this)">
                    <span class="icon">🚚</span>
                    <span>Distributing Officers</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div class="submenu">
                    <div class="submenu-item" onclick="loadContent('dispatchToDO')">
                        <span class="icon">📤</span>
                        <span>Dispatch to DO</span>
                    </div>
                    <div class="submenu-item" onclick="loadContent('doDispatchHistory')">
                        <span class="icon">📚</span>
                        <span>DO Dispatch History</span>
                    </div>
                    <div class="submenu-item" onclick="loadContent('doPending')">
                        <span class="icon">⏳</span>
                        <span>DO Pending</span>
                        <span class="badge-count" id="overdueCountDO" style="display: none;"></span>
                    </div>
                </div>
                <!-- <div class="menu-item" onclick="toggleDropdown(this)">
                    <span class="icon">📒</span>
                    <span>Ledgers</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div class="submenu">
                    <div class="submenu-item" onclick="loadContent('dispatchToLedger')">
                        <span class="icon">📤</span>
                        <span>Dispatch to Ledgers</span>
                    </div>
                    <div class="submenu-item" onclick="loadContent('ledgersDispatchHistory')">
                        <span class="icon">📚</span>
                        <span>Ledgers Dispatch History</span>
                    </div>
                    <div class="submenu-item" onclick="loadContent('ledgersPending')">
                        <span class="icon">⏳</span>
                        <span>Ledgers Pending</span>
                        <span class="badge-count" id="overdueCountLedger" style="display: none;"></span>
                    </div>
                </div> -->
                <div class="menu-item" onclick="toggleDropdown(this)">
                    <span class="icon">➕</span>
                    <span>Add New</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div class="submenu">
                    <div class="submenu-item" onclick="loadContent('addProduct')">
                        <!-- <span class="icon">📦</span> -->
                        <span class="icon">➕</span>
                        <span>Add Air Conditioner</span>
                    </div>
                    <?php if ($adminRole === 'admin' || $regionID === '4'): ?>
                        <div class="submenu-item" onclick="loadContent('addParts')">
                            <!-- <span class="icon">📦</span> -->
                            <span class="icon">➕</span>
                            <span>Add Parts</span>
                        </div>
                        <?php endif ?>
                    <?php if ($adminRole === 'superadmin'): ?>
                        <div class="submenu-item" onclick="loadContent('addParts')">
                            <!-- <span class="icon">📦</span> -->
                            <span class="icon">➕</span>
                            <span>Add Parts</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addModel')">
                            <!-- <span class="icon">📦</span> -->
                            <span class="icon">➕</span>
                            <span>Add Model</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addColor')">
                            <!-- <span class="icon">🎨</span> -->
                            <span class="icon">➕</span>
                            <span>Add Color</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addSize')">
                            <!-- <span class="icon">📏</span> -->
                            <span class="icon">➕</span>
                            <span>Add Tonnage</span>
                        </div>
                        <!-- <div class="submenu-item" onclick="loadContent('addCategory')">
                        <span class="icon">📁</span>
                        <span class="icon">➕</span>
                        <span>Add Category</span>
                    </div> -->
                        <div class="submenu-item" onclick="loadContent('addRegion')">
                            <!-- <span class="icon">🌍</span> -->
                            <span class="icon">➕</span>
                            <span>Add Region</span>
                        </div>
                        <div class="submenu-item" onclick="loadContent('addUser')">
                            <!-- <span class="icon">🌍</span> -->
                            <span class="icon">➕</span>
                            <span>Add User</span>
                        </div>
                    <?php endif ?>
                    <div class="submenu-item" onclick="loadContent('addDistributingOfficer')">
                        <!-- <span class="icon">🌍</span> -->
                        <span class="icon">➕</span>
                        <span>Add Distributing Officer</span>
                    </div>
                </div>
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

            // Load default content on page load
            loadContent('default');

            // Load overdue count for sidebar badge
            updateOverdueCount();
            // Load pending DO approval count
            updateDOApprovalCount();
            // Load pending dispatch approval counts
            updateDispatchAdminCount();
            updateDispatchSuperCount();

            // Refresh overdue count every 5 minutes
            setInterval(updateOverdueCount, 300000);
            // Refresh pending DO approval count every 5 minutes
            setInterval(updateDOApprovalCount, 300000);
            // Refresh pending dispatch approval counts every 5 minutes
            setInterval(updateDispatchAdminCount, 300000);
            setInterval(updateDispatchSuperCount, 300000);
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

        function openChangePasswordModal() {
            $('#changePasswordModal').show();
        }

        function closeChangePasswordModal() {
            $('#changePasswordModal').hide();
            $('#changePasswordForm')[0].reset();
        }

        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

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
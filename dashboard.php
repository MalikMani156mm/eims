<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
require 'adminAuth.php';
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="form.css">
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="section.js"></script>
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="nav-left">
            <div class="nav-logo-container">
                <img src="logo.jpeg" alt="Logo" class="nav-logo" onerror="this.style.display='none'">
                <div class="nav-text">
                    <h1>Electronic Inventory Management System</h1>
                    <span class="dashboard-title">Dashboard</span>
                </div>
            </div>
        </div>
        <div class="nav-right">
            <span class="user-info">
                <strong id="userName">Admin</strong>
                <span class="user-role" id="userRole">Administrator</span>
            </span>
            <button onclick="openChangePasswordModal()" class="nav-button" style="background-color: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.3s ease;">Change Password</button>
            <button onclick="handleLogout()" class="btn-logout">Logout</button>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="menu-section">
                <h3>Menu</h3>
                <div class="menu-item active" onclick="loadContent('default')">
                    <span class="icon">🏠</span>
                    <span>Home</span>
                </div>
                <div class="menu-item" onclick="toggleDropdown(this)">
                    <span class="icon">➕</span>
                    <span>Add New</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div class="submenu">
                    <div class="submenu-item" onclick="loadContent('addProduct')">
                        <!-- <span class="icon">📦</span> -->
                        <span class="icon">➕</span>
                        <span>Add Product</span>
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
                        <span>Add Size</span>
                    </div>
                    <div class="submenu-item" onclick="loadContent('addCategory')">
                        <!-- <span class="icon">📁</span> -->
                        <span class="icon">➕</span>
                        <span>Add Category</span>
                    </div>
                    <div class="submenu-item" onclick="loadContent('addRegion')">
                        <!-- <span class="icon">🌍</span> -->
                        <span class="icon">➕</span>
                        <span>Add Region</span>
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
        });

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
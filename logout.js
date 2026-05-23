function handleLogout() {
    Swal.fire({
        title: 'Logout Confirmation',
        text: "Are you sure you want to log out?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Clear the region selection flag on logout
            localStorage.removeItem('regionChanged');
            window.location.href = "logout.php";
        }
    });
}

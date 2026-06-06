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
            localStorage.clear(); // Clear all local storage to ensure clean state
            
            // Perform logout
            console.log('Initiating logout...');
            // Use fetch for better error handling
            fetch('./logout.php', {
                method: 'GET',
                credentials: 'include'  // Include cookies in the request
            })
            .then(response => {
                console.log('Logout response:', response.status);
                // Redirect after logout completes
                window.location.href = "./index.php";
            })
            .catch(error => {
                console.error('Logout error:', error);
                // Force redirect even if fetch fails
                window.location.href = "./index.php";
            });
        }
    });
}

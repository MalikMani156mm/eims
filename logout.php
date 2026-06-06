<?php
// Clear the auth_token cookie - must match the parameters used when setting it in loginBackend.php
// The cookie was set with domain '.arkcooltec.com' so we need to clear it with the same domain

setcookie("auth_token", "", [
    'expires' => time() - 3600,
    'path' => '/',
    //'domain' => '.arkcooltec.com',   Match the domain used when setting the cookie
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Also unset from $_COOKIE superglobal
if (isset($_COOKIE['auth_token'])) {
    unset($_COOKIE['auth_token']);
}

// Session cleanup if used anywhere
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redirect to login page
header("Location: index.php");
exit();
?>
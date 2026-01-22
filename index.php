<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
require 'authCheck.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="assets/css/toastr.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/toastr.min.js"></script>
    <script src="toast.js"></script>
    <div class="background">
    <div class="layout">
        <div class="container">
            <img class="logo" src="logo.jpeg" alt="Logo Unload">
            <div class="heading">Sign In</div>
            <form class="form" action="loginBackend.php" method="POST">
                <input placeholder="Username" id="username" name="username" type="text" class="input" required="" />
                <input placeholder="Password" id="password" name="password" type="password" class="input" autocomplete="off" required="" />
                <input value="Sign In" type="submit" class="login-button" />
            </form>
        </div>
    </div>
    <div class="img"></div></div>
    <?php if (isset($_GET['error'])): ?>
        <script>
            const errorType = "<?php echo $_GET['error']; ?>";
            if (errorType === "invalid_password") {
                toastr.error("❌ Invalid password");
            } else if (errorType === "invalid_username") {
                toastr.error("❌ Invalid username");
            }
        </script>
    <?php endif; ?>

</body>

</html>
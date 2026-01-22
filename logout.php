<?php

setcookie("auth_token", "", time() - 3600);

// Redirect to the login page
header("Location: index.php");
exit();

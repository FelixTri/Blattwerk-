<?php // Logout-Logik
session_start();
session_unset();
session_destroy();
setcookie("user_id", "", time() - 3600, "/");
setcookie("user_hash", "", time() - 3600, "/");
header("Location: ../../Frontend/index.html");
exit;
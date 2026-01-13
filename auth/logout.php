<?php
/**
 * Education Hub - Logout Handler
 */

session_start();

// Destroy all session data
$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// Redirect to login
header("Location: login.php");
exit();
?>

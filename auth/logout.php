<?php
/* 
   ============================================
   LOGOUT HANDLER - auth/logout.php
   ============================================
   Simple logout endpoint that destroys session
   and redirects to login page after logout
*/

/* START SESSION - Required to access $_SESSION globals */
session_start();

/* DESTROY SESSION - Remove all session variables and data, logging out user */
session_destroy();

/* REDIRECT TO LOGIN - Send user back to login page after logout */
header('Location: login.php');

/* STOP EXECUTION - Exit after redirect to prevent further code execution */
exit();
?>

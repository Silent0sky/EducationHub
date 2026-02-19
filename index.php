<?php
/* 
   ============================================
   ENTRY POINT - index.php
   ============================================
   Redirects users based on login status and role
   - Logged in Admin → admin/dashboard.php
   - Logged in User (Student/Teacher) → dashboard.php
   - Not logged in → auth/login.php
*/

/* Include helper functions for authentication and database queries */
require_once 'config/functions.php';

/* CHECK IF USER IS LOGGED IN */
if (isLoggedIn()) {
    /* 
       User has valid session with user_id stored
       Determine which dashboard to show based on role
    */
    if (isAdmin()) {
        /* ADMIN ROLE - Redirect to admin dashboard */
        redirect('admin/dashboard.php');
    } else {
        /* STUDENT/TEACHER ROLE - Redirect to main dashboard */
        redirect('dashboard.php');
    }
} else {
    /* NOT LOGGED IN - Redirect to login page */
    redirect('auth/login.php');
}
?>

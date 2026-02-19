<?php
/* 
   ============================================
   DASHBOARD ROUTER - dashboard.php
   ============================================
   Role-based redirect system:
   - Students → student_dashboard.php
   - Teachers → teacher_dashboard.php
   - Admins → admin/dashboard.php
*/

/* Include helper functions and database connection */
require_once 'config/functions.php';

/* AUTHENTICATION CHECK - Verify user is logged in, redirect if not */
requireLogin();

/* ROLE-BASED REDIRECT - Send user to appropriate dashboard */
/* Safely read role from session and fallback to redirect if missing */
$role = $_SESSION['user_role'] ?? null;
if (!$role) {
    /* Missing role in session — send to login to re-authenticate */
    redirect('auth/login.php');
}

if ($role === 'student') {
    /* Student dashboard */
    require 'student_dashboard.php';
} elseif ($role === 'teacher') {
    /* Teacher dashboard */
    require 'teacher_dashboard.php';
} elseif ($role === 'admin') {
    /* Admin dashboard in separate folder */
    require 'admin/dashboard.php';
} else {
    /* Fallback - Unknown role, redirect to login */
    redirect('auth/login.php');
}
?>

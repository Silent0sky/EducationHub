<?php
/**
 * Education Hub - Landing Page
 * Redirects to login or dashboard based on auth status
 */

require_once 'config/functions.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('dashboard.php');
    }
} else {
    redirect('auth/login.php');
}
?>

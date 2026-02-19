<?php
/* 
   ============================================
   SIDEBAR NAVIGATION - includes/sidebar.php
   ============================================
   Renders role-based navigation sidebar with:
   1. Different nav links for Student, Teacher, Admin roles
   2. Active page highlighting for current location
   3. Dynamic path calculation based on directory depth
   4. Role-specific menu items and destinations
*/

/* GET CURRENT PAGE NAME - For active link visual highlighting */
/* Uses PHP_SELF to get the currently executing script */
$currentPage = basename($_SERVER['PHP_SELF']);

/* GET USER ROLE FROM SESSION - Set during login authentication */
/* Defaults to 'student' if not in session (fallback safety) */
/* Role determines which navigation items are displayed */
$role = $_SESSION['user_role'] ?? 'student';

/* 
   CALCULATE BASE PATH - Adjusts relative URLs based on directory depth
   Different files are in different directories:
   - Root files: /dashboard.php, /quiz.php, /search_notes.php
   - Auth files: /auth/login.php, /auth/register.php
   - Admin files: /admin/dashboard.php, /admin/users.php
   So sidebar needs '../' to go back to root from nested directories
*/
$basePath = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/auth/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/includes/') !== false) {
    /* Current file is in subdirectory - use '../' to reach root */
    $basePath = '../';
}
?>

<!-- SIDEBAR CONTAINER - Fixed left navigation panel with gradient background -->
<aside class="sidebar">

    <!-- ============================================
         LOGO SECTION - Application branding and role indicator
         ============================================ -->
    <div class="sidebar-logo">
        <!-- APPLICATION ICON AND NAME -->
        <span class="logo-icon">📚</span>
        <h2>Education Hub</h2>
        <!-- ROLE BADGE - Display current user's role for visual confirmation -->
        <!-- Shows which role is currently logged in (Student/Teacher/Admin) -->
        <small style="color: var(--text-muted); font-size: 11px; display: block; margin-top: 4px;">
            <?= ucfirst($role) ?> Panel
        </small>
    </div>

    <!-- ============================================
         MAIN NAVIGATION MENU - Links based on user role
         ============================================ -->
    <nav class="sidebar-nav">

        <!-- ============================================
             DASHBOARD LINK - Available to all roles
             ============================================ -->
        <!-- Admin dashboard is in admin/ folder while others are in root -->
        <!-- This handles the different destinations for each role type -->
        <a href="<?= $role === 'admin' ? $basePath . 'admin/dashboard.php' : $basePath . 'dashboard.php' ?>"
           class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">🏠</span>
            <span>Dashboard</span>
        </a>

        <!-- ============================================
             SEARCH NOTES LINK - Available to all roles
             ============================================ -->
        <!-- Allows students to search and download notes -->
        <!-- Allows teachers to search notes from other teachers -->
        <a href="<?= $basePath ?>search_notes.php" class="nav-link <?= $currentPage === 'search_notes.php' ? 'active' : '' ?>">
            <span class="icon">🔍</span>
            <span>Search Notes</span>
        </a>

        <!-- ============================================
             PRACTICE QUIZ LINK - Available to all roles
             ============================================ -->
        <!-- Students take quizzes and get scores -->
        <!-- Teachers can view their created quizzes -->
        <!-- Admins can manage the quiz system -->
        <a href="<?= $basePath ?>quiz.php" class="nav-link <?= $currentPage === 'quiz.php' ? 'active' : '' ?>">
            <span class="icon">🎯</span>
            <span>Practice Quiz</span>
        </a>

        <!-- ============================================
             PERFORMANCE ANALYTICS - Different views per role
             ============================================ -->
        <!-- Students see their own performance --> 
        <!-- Teachers/Admins see all student performance data -->
        <a href="<?= ($role === 'teacher' || $role === 'admin') ? $basePath . 'teacher_performance.php' : $basePath . 'performance.php' ?>"
           class="nav-link <?= ($currentPage === 'performance.php' || $currentPage === 'teacher_performance.php') ? 'active' : '' ?>">
            <span class="icon">📊</span>
            <span><?= ($role === 'teacher' || $role === 'admin') ? 'Student Performance' : 'My Performance' ?></span>
        </a>

        <!-- ============================================
             TEACHER & ADMIN ONLY LINKS
             ============================================ -->
        <?php if ($role === 'teacher' || $role === 'admin'): ?>

            <!-- UPLOAD NOTES LINK - Teachers upload PDF study materials -->
            <!-- Allows content creators to share course materials with students -->
            <a href="<?= $basePath ?>upload_notes.php" class="nav-link <?= $currentPage === 'upload_notes.php' ? 'active' : '' ?>">
                <span class="icon">📤</span>
                <span>Upload Notes</span>
            </a>

            <!-- MANAGE QUESTIONS LINK - Add/edit quiz questions per subject -->
            <!-- Teachers create questions, Admins can manage all questions -->
            <a href="<?= $basePath ?>manage_questions.php" class="nav-link <?= $currentPage === 'manage_questions.php' ? 'active' : '' ?>">
                <span class="icon">➕</span>
                <span>Manage Questions</span>
            </a>

            <!-- MY UPLOADS LINK - Teacher's previously uploaded notes -->
            <!-- View, update, or delete notes the teacher has shared -->
            <a href="<?= $basePath ?>my_uploads.php" class="nav-link <?= $currentPage === 'my_uploads.php' ? 'active' : '' ?>">
                <span class="icon">📄</span>
                <span>My Uploads</span>
            </a>

        <?php endif; ?>

        <!-- ============================================
             ADMIN ONLY LINKS
             ============================================ -->
        <?php if ($role === 'admin'): ?>

            <!-- MANAGE USERS LINK - System-wide user management -->
            <!-- View all registered users (students, teachers, admins) -->
            <!-- Edit user details, reset passwords, deactivate accounts -->
            <a href="<?= $basePath ?>admin/users.php" class="nav-link <?= $currentPage === 'users.php' ? 'active' : '' ?>">
                <span class="icon">👥</span>
                <span>Manage Users</span>
            </a>

            <!-- MANAGE SUBJECTS LINK - Course curriculum management -->
            <!-- Add new subjects organized by year and semester -->
            <!-- Edit existing subject details, assign to departments -->
            <a href="<?= $basePath ?>admin/subjects.php" class="nav-link <?= $currentPage === 'subjects.php' ? 'active' : '' ?>">
                <span class="icon">📚</span>
                <span>Manage Subjects</span>
            </a>

        <?php endif; ?>
    </nav>

    <!-- ============================================
         SIDEBAR FOOTER - Logout action
         ============================================ -->
    <div class="sidebar-footer">
        <!-- LOGOUT LINK - Available to all authenticated users -->
        <!-- Destroys session and returns to login page -->
        <a href="<?= $basePath ?>auth/logout.php" class="nav-link">
            <span class="icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
</aside>

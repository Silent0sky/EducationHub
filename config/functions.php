<?php
/* 
   ============================================
   HELPER FUNCTIONS - config/functions.php
   ============================================
   Utility functions used throughout the application:
   1. Authentication and authorization checks
   2. Access control and redirects
   3. Input sanitization and security
   4. UI components and alerts
   5. User data retrieval and statistics
*/

/* Include database connection class */
require_once __DIR__ . '/database.php';

/* ============================================
   AUTHENTICATION CHECKS
   ============================================
   Determine if user is logged in and what role they have
*/

/* 
   isLoggedIn() - Check if user has valid session
   Returns: true/false
   Usage: if (isLoggedIn()) { show dashboard }
*/
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/* 
   hasRole($role) - Check if user has specific role
   Parameters: $role = 'student', 'teacher', or 'admin'
   Returns: true if logged in AND user_role matches, false otherwise
   Usage: if (hasRole('admin')) { show admin features }
*/
function hasRole($role) {
    if (!isLoggedIn()) return false;
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role);
}

/* ============================================
   ROLE-SPECIFIC CHECKS (Convenience Functions)
   ============================================
   Quick checks for specific roles
*/

/* isAdmin() - Shorthand for hasRole('admin') */
function isAdmin() {
    return hasRole('admin');
}

/* isTeacher() - Shorthand for hasRole('teacher') */
function isTeacher() {
    return hasRole('teacher');
}

/* isStudent() - Shorthand for hasRole('student') */
function isStudent() {
    return hasRole('student');
}

/* ============================================
   PATH & REDIRECT UTILITIES
   ============================================
   Helper functions for navigation and redirects
*/

/* 
   getBasePath() - Calculate relative path to root
   Logic: Check current PHP_SELF to detect directory depth
   Returns: '../' if in subdirectory, '' if in root
   Usage: $url = getBasePath() . 'auth/login.php'
   
   Handles paths from:
   - Root: /dashboard.php → ''
   - Admin: /admin/users.php → '../'
   - Auth: /auth/login.php → '../'
*/
function getBasePath() {
    $scriptPath = $_SERVER['PHP_SELF'];
    if (strpos($scriptPath, '/admin/') !== false || 
        strpos($scriptPath, '/auth/') !== false ||
        strpos($scriptPath, '/includes/') !== false) {
        return '../';
    }
    return '';
}

/* 
   redirect($url) - Send browser to new page
   Parameters: $url = destination URL
   Usage: redirect('dashboard.php') or redirect('auth/login.php')
   Note: Sends Location header and stops execution with exit()
*/
function redirect($url) {
    header("Location: $url");
    exit();
}

/* ============================================
   ACCESS CONTROL & AUTHORIZATION
   ============================================
   Enforce role-based access control
   Redirects unauthorized users to appropriate page
*/

/* 
   requireLogin() - Force user to login if not authenticated
   Redirects to login page if not logged in
   Usage: Call at top of page requiring login
   Example: requireLogin(); // In dashboard.php
*/
function requireLogin() {
    if (!isLoggedIn()) {
        $basePath = getBasePath();
        redirect($basePath . 'auth/login.php');
    }
}

/* 
   requireAdmin() - Force admin-only access
   Requirements: Must be logged in AND have admin role
   Redirects to dashboard with error if not authorized
   Usage: Call at top of admin pages
   Example: requireAdmin(); // In admin/users.php
*/
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $basePath = getBasePath();
        redirect($basePath . 'dashboard.php?error=unauthorized');
    }
}

/* 
   requireTeacher() - Force teacher or admin access
   Requirements: Must be logged in AND (teacher OR admin)
   Used because teachers and admins can both manage content
   Redirects to dashboard with error if student tries to access
   Usage: Call at top of teacher pages
   Example: requireTeacher(); // In upload_notes.php, manage_questions.php
*/
function requireTeacher() {
    requireLogin();
    if (!isTeacher() && !isAdmin()) {
        $basePath = getBasePath();
        redirect($basePath . 'dashboard.php?error=unauthorized');
    }
}

/* ============================================
   INPUT VALIDATION & SECURITY
   ============================================
   Sanitize user input to prevent XSS and SQL injection attacks
*/

/* 
   sanitize($input) - Clean user input for safe use
   
   Security layers applied in order:
   1. trim() - Remove whitespace from start/end
   2. strip_tags() - Remove HTML/PHP tags (prevents XSS)
   3. real_escape_string() - Escape MySQL special chars (prevents SQL injection)
   4. htmlspecialchars() - Convert special chars to HTML entities (safety)
   
   Parameters: $input = untrusted user input from $_POST/$_GET
   Returns: Cleaned string safe for database and HTML display
   Usage: $name = sanitize($_POST['name']);
   
   Example flow:
   Input: "<script>alert('xss')</script>"
   After strip_tags(): "" (script tag removed)
   After htmlspecialchars(): Safe for display
*/
function sanitize($input) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}

/* ============================================
   UI COMPONENT HELPERS
   ============================================
   Generate HTML elements for common patterns
*/

/* 
   showAlert($message, $type) - Display colored alert box
   
   Parameters:
   - $message = Text to display in alert
   - $type = 'success'|'error'|'warning'|'info' (affects color)
   
   Colors:
   - success: Green (#10b981)
   - error: Red (#ef4444)
   - warning: Orange (#f59e0b)
   - info: Blue (#0099ff)
   
   Returns: HTML string for the alert div
   Usage: <?= showAlert('Operation successful!', 'success') ?>
   
   Output: <div class='alert alert-success' style='...'>...</div>
*/
function showAlert($message, $type = 'info') {
    $bgColor = [
        'success' => '#10b981',
        'error' => '#ef4444',
        'warning' => '#f59e0b',
        'info' => '#0099ff'
    ];
    $bg = $bgColor[$type] ?? $bgColor['info'];
    return "<div class='alert alert-$type' style='background: {$bg}20; color: $bg; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid {$bg}40; font-weight: 500;'>$message</div>";
}

/* ============================================
   USER DATA FUNCTIONS
   ============================================
   Retrieve user information from database
*/

/* 
   getCurrentUser() - Get complete user record from database
   
   Process:
   1. Check if user is logged in
   2. Get user_id from session
   3. Query users table for complete record
   
   Returns: Associative array with all user fields:
   - id, name, email, password (hash), role, created_at
   Returns: null if not logged in
   
   Usage: $user = getCurrentUser();
          echo $user['name'];  // Access user data
*/
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    global $conn;
    $userId = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $userId");
    return $result->fetch_assoc();
}

/* 
   formatDate($date) - Convert MySQL timestamp to readable format
   
   Input format: '2025-12-28 14:30:00' (MySQL datetime)
   Output format: 'Dec 28, 2025' (Human-readable)
   
   Usage: <?= formatDate($row['created_at']) ?>
   
   Example: formatDate('2025-03-15 10:30:45') → 'Mar 15, 2025'
*/
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/* 
   getUserStats($userId) - Get aggregate statistics for a user
   
   Calculates and returns array with:
   - total_quizzes: Count of all quizzes taken by user
   - avg_score: Average percentage score across all quizzes
   - total_notes: Count of notes (varies by role)
     * Teachers/Admins: Notes they uploaded
     * Students: All notes available
   - subjects_studied: Count of unique subjects attempted in quizzes
   
   Usage: $stats = getUserStats($userId);
          echo $stats['avg_score'] . '%';  // Display average score
   
   Process:
   1. Query quiz_results for count and average percentage
   2. Query notes based on user role (uploaded vs available)
   3. Query quiz_results for distinct subjects
   4. Return array with all statistics
*/
function getUserStats($userId) {
    global $conn;

    /* Initialize stats array with default values */
    $stats = [
        'total_quizzes' => 0,
        'avg_score' => 0,
        'total_notes' => 0,
        'subjects_studied' => 0
    ];

    /* ============================================
       COUNT QUIZZES & CALCULATE AVERAGE SCORE
       ============================================ */
    $result = $conn->query("SELECT COUNT(*) as count, AVG(percentage) as avg FROM quiz_results WHERE user_id = $userId");
    $row = $result->fetch_assoc();
    $stats['total_quizzes'] = $row['count'];
    $stats['avg_score'] = round($row['avg'] ?? 0, 1);

    /* ============================================
       COUNT NOTES - Varies by user role
       ============================================ */
    $role = $_SESSION['user_role'] ?? 'student';
    if ($role === 'teacher' || $role === 'admin') {
        /* Teachers/Admins see notes they uploaded */
        $result = $conn->query("SELECT COUNT(*) as count FROM notes WHERE uploaded_by = $userId");
    } else {
        /* Students see all available notes */
        $result = $conn->query("SELECT COUNT(*) as count FROM notes");
    }
    $stats['total_notes'] = $result->fetch_assoc()['count'];

    /* ============================================
       COUNT UNIQUE SUBJECTS STUDIED
       ============================================ */
    $result = $conn->query("SELECT COUNT(DISTINCT subject_id) as count FROM quiz_results WHERE user_id = $userId");
    $stats['subjects_studied'] = $result->fetch_assoc()['count'];

    return $stats;
}
?>

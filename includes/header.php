<?php
/* 
   ============================================
   HEADER COMPONENT - includes/header.php
   ============================================
   Reusable header bar included on all authenticated pages
   Displays: page title (left) + user info (right)
   Called by: all main pages (dashboard.php, quiz.php, etc.)
*/

/* FETCH CURRENT USER DATA - Query database for logged-in user's full profile */
$user = getCurrentUser();

/* ============================================
   GENERATE AVATAR INITIALS FROM USER'S NAME
   ============================================ */
/* Extracts first letter from each word in name
   Example: "Raj Kumar" → "RK"
   Used for circular avatar display in header
*/
$initials = '';
if ($user) {
    /* SPLIT NAME BY SPACES - Break full name into separate words */
    $names = explode(' ', $user['name']);
    
    /* LOOP THROUGH WORDS - Extract first letter of each word */
    foreach ($names as $name) {
        /* UPPERCASE FIRST LETTER - Add to initials string */
        $initials .= strtoupper($name[0]);
    }
    
    /* TRUNCATE TO 2 CHARACTERS - Limit initials to 2 letters maximum */
    $initials = substr($initials, 0, 2);
}
?>

<!-- ============================================
     PAGE HEADER BAR COMPONENT
     ============================================ -->
<!-- Layout: Page title on left | User info on right -->
<header class="header">
    <!-- PAGE TITLE - Dynamic title set by each page in $pageTitle variable -->
    <h1><?= $pageTitle ?? 'Dashboard' ?></h1>

    <!-- USER INFO SECTION - Right side with user details and avatar -->
    <div class="header-right">
        <div class="user-info">
            <!-- USER NAME AND ROLE TEXT -->
            <div class="user-details">
                <!-- USER FULL NAME - Sanitized from database -->
                <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
                
                <!-- USER ROLE BADGE - Shows user's role (Student, Teacher, Admin) -->
                <div class="user-role"><?= ucfirst(htmlspecialchars($user['role'] ?? 'student')) ?></div>
            </div>
            
            <!-- USER AVATAR CIRCLE - Gradient background with user's initials -->
            <div class="user-avatar"><?= $initials ?></div>
        </div>
    </div>
</header>

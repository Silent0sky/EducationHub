<?php
/**
 * Education Hub - Header Component
 */

$user = getCurrentUser();
$initials = '';
if ($user) {
    $names = explode(' ', $user['name']);
    foreach ($names as $name) {
        $initials .= strtoupper($name[0]);
    }
    $initials = substr($initials, 0, 2);
}
?>

<header class="header">
    <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
    
    <div class="header-right">
        <div class="user-info">
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
                <div class="user-role"><?= htmlspecialchars($user['role'] ?? 'student') ?></div>
            </div>
            <div class="user-avatar"><?= $initials ?></div>
        </div>
    </div>
</header>

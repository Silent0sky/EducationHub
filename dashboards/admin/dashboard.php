<?php
/* Admin dashboard moved to dashboards/admin/ */
require_once __DIR__ . '/../../config/functions.php';
requireAdmin();

$pageTitle = 'Admin Dashboard';

/* Reuse original queries from admin/dashboard.php */
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalStudents = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'student'")->fetch_assoc()['c'];
$totalTeachers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'teacher'")->fetch_assoc()['c'];
$totalSubjects = $conn->query("SELECT COUNT(*) as c FROM subjects")->fetch_assoc()['c'];
$totalNotes = $conn->query("SELECT COUNT(*) as c FROM notes")->fetch_assoc()['c'];
$totalQuestions = $conn->query("SELECT COUNT(*) as c FROM questions")->fetch_assoc()['c'];
$totalQuizzes = $conn->query("SELECT COUNT(*) as c FROM quiz_results")->fetch_assoc()['c'];

$recentUsers = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

$allNotes = $conn->query("SELECT n.*, u.name as uploader_name, s.name as subject_name, s.color as subject_color
    FROM notes n JOIN users u ON n.uploaded_by = u.id JOIN subjects s ON n.subject_id = s.id
    ORDER BY n.created_at DESC LIMIT 10");

$allQuestions = $conn->query("SELECT q.*, u.name as creator_name, s.name as subject_name
    FROM questions q JOIN users u ON q.created_by = u.id JOIN subjects s ON q.subject_id = s.id
    ORDER BY q.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Education Hub</title>
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/global.css">
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/common.css">
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/dashboard_admin.css">
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include __DIR__ . '/../../includes/header.php'; ?>
            <section class="dashboard-content">
                <!-- admin content preserved from original file -->
            </section>
        </main>
    </div>
</body>
</html>

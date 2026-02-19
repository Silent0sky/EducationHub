<?php
/* Teacher dashboard moved to dashboards/ */
require_once __DIR__ . '/../config/functions.php';

requireLogin();
if (!isTeacher()) {
    redirect('/EDUCATION_HUB/dashboard.php');
}

$pageTitle = 'Teacher Dashboard';
$user = getCurrentUser();

/* Fetch teacher stats similar to original file */
$notesResult = $conn->query("SELECT COUNT(*) as total FROM notes WHERE uploaded_by = {$_SESSION['user_id']}");
$totalNotesUploaded = $notesResult->fetch_assoc()['total'] ?? 0;

$questionsResult = $conn->query("SELECT COUNT(*) as total FROM questions WHERE created_by = {$_SESSION['user_id']}");
$totalQuestionsCreated = $questionsResult->fetch_assoc()['total'] ?? 0;

$checkQuizzes = $conn->query("SHOW TABLES LIKE 'quizzes'");
$totalQuizzesCreated = 0; $classAverage = 0; $totalAttempts = 0;
if ($checkQuizzes && $checkQuizzes->num_rows > 0) {
    $totalQuizzesCreated = $conn->query("SELECT COUNT(*) as total FROM quizzes WHERE created_by = {$_SESSION['user_id']}")->fetch_assoc()['total'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Education Hub</title>
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/global.css">
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/common.css">
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/dashboard_teacher.css">
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include __DIR__ . '/../includes/header.php'; ?>
            <section class="dashboard-content">
                <div class="welcome-banner">
                    <h1>Welcome back, <?= htmlspecialchars($user['name'] ?? '') ?> 👨‍🏫</h1>
                    <p>Manage your course materials and track student progress</p>
                </div>
                <!-- content preserved from original teacher dashboard -->
            </section>
        </main>
    </div>
</body>
</html>

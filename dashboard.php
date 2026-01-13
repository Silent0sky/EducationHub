<?php
/**
 * Education Hub - Student/Teacher Dashboard
 */

require_once 'config/functions.php';
requireLogin();

$pageTitle = 'Dashboard';
$user = getCurrentUser();
$stats = getUserStats($_SESSION['user_id']);

// Get all subjects
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Education Hub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <section class="dashboard-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-value"><?= $stats['total_quizzes'] ?></div>
                        <div class="stat-label">Quizzes Taken</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-value"><?= $stats['avg_score'] ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-value"><?= $stats['total_notes'] ?></div>
                        <div class="stat-label"><?= isTeacher() ? 'Notes Uploaded' : 'Notes Available' ?></div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-icon">📖</div>
                        <div class="stat-value"><?= $stats['subjects_studied'] ?></div>
                        <div class="stat-label">Subjects Studied</div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <a href="search_notes.php" class="btn btn-primary">📝 Search Notes</a>
                        <a href="quiz.php" class="btn btn-secondary">❓ Take Quiz</a>
                        <a href="performance.php" class="btn btn-secondary">📊 View Performance</a>
                        <?php if (isTeacher() || isAdmin()): ?>
                        <a href="upload_notes.php" class="btn btn-success">📤 Upload Notes</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Subjects -->
                <h2 style="margin-bottom: 24px;">Subjects</h2>
                <div class="subjects-grid">
                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                    <div class="subject-card">
                        <div class="subject-header">
                            <div class="subject-icon" style="background: <?= $subject['color'] ?>20; color: <?= $subject['color'] ?>;">
                                📚
                            </div>
                            <h3 class="subject-name"><?= htmlspecialchars($subject['name']) ?></h3>
                        </div>
                        <p class="subject-desc"><?= htmlspecialchars($subject['description']) ?></p>
                        <div class="subject-actions">
                            <a href="search_notes.php?subject=<?= $subject['id'] ?>" class="btn btn-sm btn-secondary">View Notes</a>
                            <a href="quiz.php?subject=<?= $subject['id'] ?>" class="btn btn-sm btn-primary">Take Quiz</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

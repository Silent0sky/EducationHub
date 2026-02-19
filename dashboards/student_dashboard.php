<?php
/* Student dashboard moved to dashboards/ */
require_once __DIR__ . '/../config/functions.php';

requireLogin();
if (!isStudent()) {
    redirect('../dashboard.php');
}

$pageTitle = 'My Dashboard';
$user = getCurrentUser();
$stats = getUserStats($_SESSION['user_id'] ?? null);

$subjects = $conn->query("SELECT * FROM subjects ORDER BY year, semester, name");

/* Get student's downloaded notes */
$downloadedNotes = $conn->query("
    SELECT n.*, s.name as subject_name, s.color as subject_color, s.year, s.semester, dh.downloaded_at
    FROM download_history dh
    JOIN notes n ON dh.note_id = n.id
    JOIN subjects s ON n.subject_id = s.id 
    WHERE dh.user_id = {$_SESSION['user_id']}
    ORDER BY dh.downloaded_at DESC
    LIMIT 10
");

/* Recent quizzes logic (same as before) */
$recentQuizzes = null;
$check = $conn->query("SHOW TABLES LIKE 'quizzes'");
if ($check && $check->num_rows > 0) {
    $colCheck = $conn->query("SHOW COLUMNS FROM quiz_results LIKE 'quiz_id'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $recentQuizzes = $conn->query(
            "SELECT q.id, q.title AS name, s.name as subject_name, qr.score, qr.total_questions, qr.taken_at AS attempt_date
             FROM quiz_results qr
             JOIN quizzes q ON qr.quiz_id = q.id
             JOIN subjects s ON q.subject_id = s.id
             WHERE qr.user_id = {$_SESSION['user_id']}
             ORDER BY qr.taken_at DESC
             LIMIT 5"
        );
    } else {
        $recentQuizzes = $conn->query(
            "SELECT qr.id AS id, CONCAT('Quiz #', qr.id) AS name, COALESCE(s.name, 'General') AS subject_name,
                    qr.score, qr.total_questions, qr.taken_at AS attempt_date
             FROM quiz_results qr
             LEFT JOIN subjects s ON qr.subject_id = s.id
             WHERE qr.user_id = {$_SESSION['user_id']}
             ORDER BY qr.taken_at DESC
             LIMIT 5"
        );
    }
} else {
    $recentQuizzes = $conn->query(
        "SELECT qr.id AS id, CONCAT('Quiz #', qr.id) AS name,
                COALESCE(s.name, 'General') AS subject_name, qr.score, qr.total_questions, qr.taken_at AS attempt_date
         FROM quiz_results qr
         LEFT JOIN subjects s ON qr.subject_id = s.id
         WHERE qr.user_id = {$_SESSION['user_id']}
         ORDER BY qr.taken_at DESC
         LIMIT 5"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Education Hub</title>
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/global.css">
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/common.css">
    <link rel="stylesheet" href="/EDUCATION_HUB/assets/css/dashboard_student.css">
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include __DIR__ . '/../includes/header.php'; ?>
            <section class="dashboard-content">
                <div class="welcome-banner">
                    <h1>Welcome back, <?= htmlspecialchars($user['first_name'] ?? $user['name'] ?? '') ?> 👋</h1>
                    <p>Continue your learning journey and track your progress</p>
                </div>

                <!-- ============================================
                     PERFORMANCE STATISTICS
                     ============================================ -->
                <div class="stats-grid grid">
                    <div class="stat-card tile">
                        <div class="stat-icon icon">📝</div>
                        <div class="stat-value value"><?= $stats['total_quizzes'] ?></div>
                        <div class="stat-label label">Total Quizzes</div>
                    </div>
                    
                    <div class="stat-card success tile">
                        <div class="stat-icon icon">🎯</div>
                        <div class="stat-value value"><?= $stats['avg_score'] ?>%</div>
                        <div class="stat-label label">Overall Accuracy</div>
                    </div>
                    
                    <div class="stat-card warning tile">
                        <div class="stat-icon icon">📖</div>
                        <div class="stat-value value"><?= $stats['subjects_studied'] ?></div>
                        <div class="stat-label label">Subjects Studied</div>
                    </div>
                </div>

                <!-- ============================================
                     DOWNLOADED NOTES
                     ============================================ -->
                <?php if ($downloadedNotes && $downloadedNotes->num_rows > 0): ?>
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">📚 Recently Downloaded Notes</h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Note Title</th>
                                    <th>Subject</th>
                                    <th>Downloaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($note = $downloadedNotes->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="download_notes.php?id=<?= $note['id'] ?>" style="color: var(--primary); text-decoration: none;">
                                            <?= htmlspecialchars($note['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span style="background: <?= $note['subject_color'] ?>20; color: <?= $note['subject_color'] ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                            <?= htmlspecialchars($note['subject_name']) ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($note['downloaded_at']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================
                     RECENT QUIZZES
                     ============================================ -->
                <?php if ($recentQuizzes && $recentQuizzes->num_rows > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🎯 Recent Quiz Attempts</h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>Subject</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($quiz = $recentQuizzes->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="quiz.php?id=<?= $quiz['id'] ?>" style="color: var(--primary); text-decoration: none;">
                                            <?= htmlspecialchars($quiz['name']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span style="background: var(--primary-lighter); color: var(--primary); padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                            <?= htmlspecialchars($quiz['subject_name']) ?>
                                        </span>
                                    </td>
                                    <td><?= $quiz['score'] ?>/<?= $quiz['total_questions'] ?></td>
                                    <td><strong><?= round(($quiz['score'] / $quiz['total_questions']) * 100) ?>%</strong></td>
                                    <td><?= formatDate($quiz['attempt_date']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                    <div class="card">
                        <p style="text-align: center; color: var(--text-muted); padding: 48px;">
                            No quiz attempts yet. <a href="quiz.php" style="color: var(--primary);">Take your first quiz!</a>
                        </p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>

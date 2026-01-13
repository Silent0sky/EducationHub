<?php
/**
 * Education Hub - Performance Page
 */

require_once 'config/functions.php';
requireLogin();

$pageTitle = 'Performance';
$userId = $_SESSION['user_id'];
$stats = getUserStats($userId);

// Get quiz history
$history = $conn->query("
    SELECT qr.*, s.name as subject_name, s.color as subject_color 
    FROM quiz_results qr 
    JOIN subjects s ON qr.subject_id = s.id 
    WHERE qr.user_id = $userId 
    ORDER BY qr.taken_at DESC 
    LIMIT 20
");

// Get performance by subject
$subjectPerformance = $conn->query("
    SELECT s.name, s.color, AVG(qr.percentage) as avg_score, COUNT(*) as attempts
    FROM quiz_results qr 
    JOIN subjects s ON qr.subject_id = s.id 
    WHERE qr.user_id = $userId 
    GROUP BY qr.subject_id 
    ORDER BY avg_score DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance - Education Hub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <section>
                <!-- Stats Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-value"><?= $stats['total_quizzes'] ?></div>
                        <div class="stat-label">Total Quizzes</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-value"><?= $stats['avg_score'] ?>%</div>
                        <div class="stat-label">Overall Accuracy</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-icon">📖</div>
                        <div class="stat-value"><?= $stats['subjects_studied'] ?></div>
                        <div class="stat-label">Subjects Studied</div>
                    </div>
                </div>
                
                <!-- Performance by Subject -->
                <?php if ($subjectPerformance->num_rows > 0): ?>
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">Performance by Subject</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php while ($sp = $subjectPerformance->fetch_assoc()): ?>
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <span style="min-width: 120px; font-weight: 600;"><?= htmlspecialchars($sp['name']) ?></span>
                            <div style="flex: 1; height: 24px; background: var(--surface-light); border-radius: 12px; overflow: hidden;">
                                <div style="width: <?= round($sp['avg_score']) ?>%; height: 100%; background: <?= $sp['color'] ?>; border-radius: 12px; transition: width 0.5s;"></div>
                            </div>
                            <span style="min-width: 60px; text-align: right; font-weight: 600;"><?= round($sp['avg_score'], 1) ?>%</span>
                            <span style="color: var(--text-muted); font-size: 12px;">(<?= $sp['attempts'] ?> attempts)</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quiz History -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quiz History</h3>
                    </div>
                    
                    <?php if ($history->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Subject</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $history->fetch_assoc()): ?>
                                <tr>
                                    <td><?= formatDate($row['taken_at']) ?></td>
                                    <td>
                                        <span style="background: <?= $row['subject_color'] ?>20; color: <?= $row['subject_color'] ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                            <?= htmlspecialchars($row['subject_name']) ?>
                                        </span>
                                    </td>
                                    <td><?= $row['score'] ?>/<?= $row['total_questions'] ?></td>
                                    <td>
                                        <strong><?= $row['percentage'] ?>%</strong>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['percentage'] >= 80) {
                                            echo '<span style="color: var(--success);">✓ Excellent</span>';
                                        } elseif ($row['percentage'] >= 60) {
                                            echo '<span style="color: var(--primary);">Good</span>';
                                        } elseif ($row['percentage'] >= 40) {
                                            echo '<span style="color: var(--warning);">Average</span>';
                                        } else {
                                            echo '<span style="color: var(--danger);">Needs Work</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 48px;">
                        No quiz history yet. <a href="quiz.php" style="color: var(--primary);">Take your first quiz!</a>
                    </p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

<?php
/* 
   ============================================
   STUDENT PERFORMANCE PAGE - performance.php
   ============================================
   Displays student's quiz performance analytics:
   1. Overall statistics (total quizzes, avg score, subjects studied)
   2. Performance breakdown by subject with progress bars
   3. Complete quiz history with scores and dates
   
   Shows personal performance for individual students
   (Teachers/Admins see different view in teacher_performance.php)
*/

/* Include configuration and helper functions */
require_once 'config/functions.php';
/* Verify user is logged in */
requireLogin();

/* PAGE IDENTIFIER FOR HEADER */
$pageTitle = 'My Performance';

/* 
   USER IDENTIFIER - Get current student's ID from session
   Used to fetch performance data for this specific student
*/
$userId = $_SESSION['user_id'];

/* 
   OVERALL STATISTICS - Get aggregated stats for this student
   Function from config/functions.php calculates:
   - Total quizzes taken
   - Average score percentage
   - Number of subjects studied
*/
$stats = getUserStats($userId);

/* 
   ============================================
   FETCH QUIZ HISTORY - Recent quiz attempts
   ============================================
   Get last 20 quiz attempts with subject information
   Includes: score, total questions, percentage, date
   Sorted by most recent first
*/
$history = $conn->query("
    SELECT qr.*, s.name as subject_name, s.color as subject_color 
    FROM quiz_results qr 
    JOIN subjects s ON qr.subject_id = s.id 
    WHERE qr.user_id = $userId 
    ORDER BY qr.taken_at DESC 
    LIMIT 20
");

/* 
   ============================================
   FETCH SUBJECT-WISE PERFORMANCE
   ============================================
   Calculate performance statistics for each subject:
   - Average score (avg) across all attempts
   - Number of attempts
   - Subject name and color for display
   Grouped by subject, ordered by average score (best first)
*/
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
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/performance.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <section>
                <!-- ============================================
                     STATISTICS OVERVIEW CARDS
                     ============================================
                     Display key performance metrics at top of page
                     Shows: Total Quizzes, Overall Accuracy, Subjects Studied
                --> 
                <div class="stats-grid grid">
                    <!-- CARD 1: TOTAL QUIZZES TAKEN -->
                    <div class="stat-card tile">
                        <div class="stat-icon icon">📝</div>
                        <div class="stat-value value"><?= $stats['total_quizzes'] ?></div>
                        <div class="stat-label label">Total Quizzes</div>
                    </div>
                    
                    <!-- CARD 2: OVERALL ACCURACY PERCENTAGE -->
                    <!-- Average score across all quiz attempts -->
                    <div class="stat-card success tile">
                        <div class="stat-icon icon">🎯</div>
                        <div class="stat-value value"><?= $stats['avg_score'] ?>%</div>
                        <div class="stat-label label">Overall Accuracy</div>
                    </div>
                    
                    <!-- CARD 3: NUMBER OF SUBJECTS STUDIED -->
                    <!-- Count of unique subjects with quiz attempts -->
                    <div class="stat-card warning tile">
                        <div class="stat-icon icon">📖</div>
                        <div class="stat-value value"><?= $stats['subjects_studied'] ?></div>
                        <div class="stat-label label">Subjects Studied</div>
                    </div>
                </div>

                <!-- ============================================
                     PERFORMANCE BY SUBJECT
                     ============================================
                     Shows visual progress bars for each subject
                     Displays average score and attempt count per subject
                --> 
                <?php if ($subjectPerformance->num_rows > 0): ?>
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">📊 Performance by Subject</h3>
                    </div>
                    <!-- SUBJECT LIST - One row per subject -->
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php while ($sp = $subjectPerformance->fetch_assoc()): ?>
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <!-- SUBJECT NAME -->
                            <span style="min-width: 160px; font-weight: 600;"><?= htmlspecialchars($sp['name']) ?></span>
                            
                            <!-- PROGRESS BAR - Visual representation of performance -->
                            <!-- Width represents average score percentage -->
                            <div style="flex: 1; height: 24px; background: var(--surface-light); border-radius: 12px; overflow: hidden;">
                                <!-- FILLED PORTION - Subject color matches database -->
                                <div style="width: <?= round($sp['avg_score']) ?>%; height: 100%; background: <?= $sp['color'] ?>; border-radius: 12px; transition: width 0.5s;"></div>
                            </div>
                            
                            <!-- PERCENTAGE TEXT - Average score shown numerically -->
                            <span style="min-width: 60px; text-align: right; font-weight: 600;"><?= round($sp['avg_score'], 1) ?>%</span>
                            
                            <!-- ATTEMPT COUNT - How many times student took quiz on this subject -->
                            <span style="color: var(--text-muted); font-size: 12px;">(<?= $sp['attempts'] ?> attempts)</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================
                     QUIZ HISTORY TABLE
                     ============================================
                     Complete record of all quiz attempts
                     Shows subject, score, date, and performance status
                --> 
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📋 Quiz History</h3>
                    </div>

                    <?php if ($history->num_rows > 0): ?>
                    <!-- HISTORY TABLE - Shows all quiz attempts -->
                    <div class="table-container">
                        <table>
                            <!-- TABLE HEADER - Column labels -->
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Subject</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <!-- TABLE BODY - Each row is one quiz attempt -->
                            <tbody>
                                <?php while ($row = $history->fetch_assoc()): ?>
                                <tr>
                                    <!-- DATE - When quiz was taken -->
                                    <td><?= formatDate($row['taken_at']) ?></td>
                                    
                                    <!-- SUBJECT - Which subject quiz was on -->
                                    <!-- Color-coded badge matching subject -->
                                    <td>
                                        <span style="background: <?= $row['subject_color'] ?>20; color: <?= $row['subject_color'] ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                            <?= htmlspecialchars($row['subject_name']) ?>
                                        </span>
                                    </td>
                                    
                                    <!-- SCORE - Correct answers out of total -->
                                    <td><?= $row['score'] ?>/<?= $row['total_questions'] ?></td>
                                    
                                    <!-- PERCENTAGE - Score percentage -->
                                    <td><strong><?= $row['percentage'] ?>%</strong></td>
                                    
                                    <!-- PERFORMANCE STATUS - Visual indicator of performance -->
                                    <!-- Color-coded based on percentage: Green (80%+), Blue (60-79%), Yellow (40-59%), Red (<40%) -->
                                    <td>
                                        <?php
                                        if ($row['percentage'] >= 80) echo '<span style="color: var(--success); font-weight: 600;">✓ Excellent</span>';
                                        elseif ($row['percentage'] >= 60) echo '<span style="color: var(--primary); font-weight: 600;">Good</span>';
                                        elseif ($row['percentage'] >= 40) echo '<span style="color: var(--warning); font-weight: 600;">Average</span>';
                                        else echo '<span style="color: var(--danger); font-weight: 600;">Needs Work</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <!-- EMPTY STATE - No quiz history yet -->
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

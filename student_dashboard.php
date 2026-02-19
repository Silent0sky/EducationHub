<?php
/* 
   ============================================
   STUDENT DASHBOARD - student_dashboard.php
   ============================================
   Displays student-specific learning interface:
   - Personal quiz statistics and performance metrics
   - List of available subjects for learning
   - Quick access to search notes, take quizzes, and view progress
   - No teacher/admin functionality shown
*/

/* Include helper functions and database connection */
require_once 'config/functions.php';

/* AUTHENTICATION CHECK - Verify user is logged in and is a student */
requireLogin();

/* Verify user is a student (not teacher or admin) */
if (!isStudent()) {
    redirect('dashboard.php');
}

/* SET PAGE TITLE - Displayed in header bar */
$pageTitle = 'My Dashboard';

/* FETCH USER INFORMATION - Get current student's complete profile from database */
$user = getCurrentUser();

/* FETCH USER STATISTICS - Get aggregated stats (quizzes, scores, notes, subjects) for this student */
$stats = getUserStats($_SESSION['user_id']);

/* FETCH ALL SUBJECTS - Query all subjects ordered by year and semester for subject grid */
$subjects = $conn->query("SELECT * FROM subjects ORDER BY year, semester, name");

/* FETCH RECENT QUIZ ATTEMPTS - Get student's last 5 quiz attempts for quick reference */
$recentQuizzes = $conn->query("
    SELECT q.id, q.name, s.name as subject_name, qr.score, qr.total_questions, qr.attempt_date
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    JOIN subjects s ON q.subject_id = s.id
    WHERE qr.student_id = {$_SESSION['user_id']}
    ORDER BY qr.attempt_date DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Education Hub</title>
    <!-- Global CSS - Variables, layout, sidebar, header -->
    <link rel="stylesheet" href="assets/css/global.css">
    <!-- Common CSS - Buttons, forms, cards, tables -->
    <link rel="stylesheet" href="assets/css/common.css">
    <!-- Student Dashboard CSS - Student-specific styling -->
    <link rel="stylesheet" href="assets/css/dashboard_student.css">
</head>
<body>
    <!-- MAIN LAYOUT CONTAINER - Flex row with sidebar on left, content on right -->
    <div class="layout">
        <!-- SIDEBAR NAVIGATION - Student-focused menu (left fixed panel) -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- MAIN CONTENT AREA - Right scrollable area with page content -->
        <main class="main-content">
            <!-- HEADER BAR - Page title and user info (top bar) -->
            <?php include 'includes/header.php'; ?>

            <!-- DASHBOARD CONTENT SECTION -->
            <section class="dashboard-content">

                <!-- ====================================
                     WELCOME MESSAGE SECTION
                     ==================================== -->
                <!-- Personalized greeting to student -->
                <div class="welcome-banner">
                    <h1>Welcome back, <?= htmlspecialchars($user['first_name']) ?> 👋</h1>
                    <p>Continue your learning journey and track your progress</p>
                </div>

                <!-- ====================================
                     STATISTICS CARDS SECTION
                     ==================================== -->
                <!-- Shows student's key learning metrics: quizzes taken, average score, subjects studied, and performance trend -->
                <div class="stats-grid grid">
                    <!-- QUIZZES TAKEN CARD - Count of quizzes this student has completed -->
                    <div class="stat-card tile">
                        <div class="stat-icon icon">📝</div>
                        <div class="stat-value value"><?= $stats['total_quizzes'] ?></div>
                        <div class="stat-label label">Quizzes Taken</div>
                    </div>

                    <!-- AVERAGE SCORE CARD - Overall percentage average across all quiz attempts -->
                    <div class="stat-card success tile">
                        <div class="stat-icon icon">🎯</div>
                        <div class="stat-value value"><?= $stats['avg_score'] ?>%</div>
                        <div class="stat-label label">Average Score</div>
                    </div>

                    <!-- SUBJECTS STUDIED CARD - Count of unique subjects attempted by this student -->
                    <div class="stat-card warning tile">
                        <div class="stat-icon icon">📖</div>
                        <div class="stat-value value"><?= $stats['subjects_studied'] ?></div>
                        <div class="stat-label label">Subjects Studied</div>
                    </div>

                    <!-- NOTES AVAILABLE CARD - Count of all available study notes from teachers -->
                    <div class="stat-card tile">
                        <div class="stat-icon icon">📚</div>
                        <div class="stat-value value"><?= $stats['total_notes'] ?></div>
                        <div class="stat-label label">Study Notes Available</div>
                    </div>
                </div>

                <!-- ====================================
                     QUICK ACTIONS SECTION
                     ==================================== -->
                <!-- Fast navigation buttons to key student features -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">⚡ Quick Actions</h3>
                    </div>
                    <!-- ACTION BUTTONS - Layout in flex row with wrap -->
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <!-- SEARCH NOTES BUTTON - Search and filter available study notes -->
                        <a href="search_notes.php" class="btn btn-primary">📝 Search Notes</a>
                        
                        <!-- TAKE QUIZ BUTTON - Browse available quizzes and test knowledge -->
                        <a href="quiz.php" class="btn btn-secondary">❓ Take Quiz</a>
                        
                        <!-- VIEW PERFORMANCE BUTTON - View detailed performance analytics and progress -->
                        <a href="performance.php" class="btn btn-secondary">📊 View Performance</a>

                        <!-- DOWNLOAD NOTES BUTTON - Access previously downloaded notes -->
                        <a href="download_notes.php" class="btn btn-secondary">⬇️ My Downloads</a>
                    </div>
                </div>

                <!-- ====================================
                     RECENT QUIZ ATTEMPTS SECTION
                     ==================================== -->
                <!-- Shows student's latest quiz attempts for quick reference on recent activities -->
                <?php if ($recentQuizzes && $recentQuizzes->num_rows > 0): ?>
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">📊 Recent Quiz Attempts</h3>
                    </div>
                    <!-- RECENT QUIZZES TABLE - Latest attempts ordered by date -->
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Quiz Name</th>
                                    <th>Subject</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- LOOP THROUGH RECENT QUIZ ATTEMPTS -->
                                <?php while ($quiz = $recentQuizzes->fetch_assoc()): 
                                    $percentage = round(($quiz['score'] / $quiz['total_questions']) * 100);
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($quiz['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($quiz['subject_name']) ?></td>
                                    <td>
                                        <span class="score-badge <?= $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') ?>">
                                            <?= $percentage ?>%
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($quiz['attempt_date'])) ?></td>
                                    <td>
                                        <a href="performance.php" class="btn btn-sm btn-secondary">View Details</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ====================================
                     SUBJECTS GRID SECTION
                     ==================================== -->
                <!-- Responsive grid showing all academic subjects available for learning -->
                <h2 style="margin-bottom: 24px;">📚 Available Subjects</h2>
                <div class="subjects-grid">
                    <!-- LOOP THROUGH ALL SUBJECTS -->
                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                    
                    <!-- SUBJECT CARD - Individual card for each subject with color-coded icon -->
                    <div class="subject-card">
                        <!-- SUBJECT HEADER - Icon and name -->
                        <div class="subject-header">
                            <!-- COLORED ICON BOX - Uses subject's color stored in database -->
                            <div class="subject-icon" style="background: <?= $subject['color'] ?>20; color: <?= $subject['color'] ?>;">
                                📚
                            </div>
                            <!-- SUBJECT NAME HEADING -->
                            <h3 class="subject-name"><?= htmlspecialchars($subject['name']) ?></h3>
                        </div>
                        
                        <!-- YEAR AND SEMESTER BADGE - Academic year info -->
                        <p class="subject-desc"><?= $subject['year'] ?> - Semester <?= $subject['semester'] ?></p>
                        
                        <!-- SUBJECT DESCRIPTION TEXT - Brief description of subject -->
                        <p class="subject-desc"><?= htmlspecialchars($subject['description']) ?></p>
                        
                        <!-- ACTION BUTTONS - Navigate to study materials and quizzes for this subject -->
                        <div class="subject-actions">
                            <!-- VIEW NOTES BUTTON - Filter and view study notes for this subject -->
                            <a href="search_notes.php?subject=<?= $subject['id'] ?>" class="btn btn-sm btn-secondary">📖 View Notes</a>
                            
                            <!-- TAKE QUIZ BUTTON - Filter and attempt quizzes for this subject -->
                            <a href="quiz.php?subject=<?= $subject['id'] ?>" class="btn btn-sm btn-primary">🎯 Take Quiz</a>
                        </div>
                    </div>
                    
                    <?php endwhile; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
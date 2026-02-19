<?php
/* 
   ============================================
   TEACHER DASHBOARD - teacher_dashboard.php
   ============================================
   Displays teacher-specific education management interface:
   - Statistics on uploaded notes and created questions
   - Class performance analytics
   - Quick access to upload materials and manage quizzes
   - Student engagement metrics
   - Classroom subjects and recent activities
*/

/* Include helper functions and database connection */
require_once 'config/functions.php';

/* AUTHENTICATION CHECK - Verify user is logged in and is a teacher */
requireLogin();

/* Verify user is a teacher (not regular student) */
if (!isTeacher()) {
    redirect('dashboard.php');
}

/* SET PAGE TITLE - Displayed in header bar */
$pageTitle = 'Teacher Dashboard';

/* FETCH USER INFORMATION - Get current teacher's complete profile from database */
$user = getCurrentUser();

/* FETCH NOTES UPLOADED STATISTICS - Count of notes uploaded by this teacher */
$notesResult = $conn->query("SELECT COUNT(*) as total FROM notes WHERE uploaded_by = {$_SESSION['user_id']}");
$notesData = $notesResult->fetch_assoc();
$totalNotesUploaded = $notesData['total'];

/* FETCH QUESTIONS CREATED STATISTICS - Count of quiz questions created by this teacher */
$questionsResult = $conn->query("SELECT COUNT(*) as total FROM questions WHERE created_by = {$_SESSION['user_id']}");
$questionsData = $questionsResult->fetch_assoc();
$totalQuestionsCreated = $questionsData['total'];

/* FETCH QUIZZES CREATED - Count of quizzes created by this teacher */
$quizzesResult = $conn->query("SELECT COUNT(*) as total FROM quizzes WHERE created_by = {$_SESSION['user_id']}");
$quizzesData = $quizzesResult->fetch_assoc();
$totalQuizzesCreated = $quizzesData['total'];

/* FETCH CLASS PERFORMANCE DATA - Average scores across all student attempts */
$performanceResult = $conn->query("
    SELECT AVG(qr.score / q.total_questions * 100) as average_score
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    WHERE q.created_by = {$_SESSION['user_id']}
");
$performanceData = $performanceResult->fetch_assoc();
$classAverage = $performanceData['average_score'] ? round($performanceData['average_score']) : 0;

/* FETCH TOTAL STUDENT ATTEMPTS - Count of quiz attempts by all students on teacher's quizzes */
$attemptsResult = $conn->query("
    SELECT COUNT(*) as total
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    WHERE q.created_by = {$_SESSION['user_id']}
");
$attemptsData = $attemptsResult->fetch_assoc();
$totalAttempts = $attemptsData['total'];

/* FETCH SUBJECTS TAUGHT - Get list of subjects this teacher teaches/manages */
$subjects = $conn->query("SELECT DISTINCT s.* FROM subjects s WHERE EXISTS (
    SELECT 1 FROM notes n WHERE n.subject_id = s.id AND n.uploaded_by = {$_SESSION['user_id']}
    UNION
    SELECT 1 FROM quizzes q WHERE q.subject_id = s.id AND q.created_by = {$_SESSION['user_id']}
) ORDER BY s.year, s.semester, s.name");

/* FETCH RECENT UPLOADS - Get teacher's latest 5 uploaded notes */
$recentUploads = $conn->query("
    SELECT n.id, n.title, s.name as subject_name, n.upload_date, n.file_size
    FROM notes n
    JOIN subjects s ON n.subject_id = s.id
    WHERE n.uploaded_by = {$_SESSION['user_id']}
    ORDER BY n.upload_date DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Education Hub</title>
    <!-- Global CSS - Variables, layout, sidebar, header -->
    <link rel="stylesheet" href="assets/css/global.css">
    <!-- Common CSS - Buttons, forms, cards, tables -->
    <link rel="stylesheet" href="assets/css/common.css">
    <!-- Teacher Dashboard CSS - Teacher-specific styling -->
    <link rel="stylesheet" href="assets/css/dashboard_teacher.css">
</head>
<body>
    <!-- MAIN LAYOUT CONTAINER - Flex row with sidebar on left, content on right -->
    <div class="layout">
        <!-- SIDEBAR NAVIGATION - Teacher-focused menu (left fixed panel) -->
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
                <!-- Personalized greeting to teacher -->
                <div class="welcome-banner">
                    <h1>Welcome back, Prof. <?= htmlspecialchars($user['last_name']) ?> 👨‍🏫</h1>
                    <p>Manage your course materials and track student progress</p>
                </div>

                <!-- ====================================
                     STATISTICS CARDS SECTION
                     ==================================== -->
                <!-- Shows teacher's key teaching metrics: notes uploaded, questions created, quizzes, and class performance -->
                <div class="stats-grid grid">
                    <!-- NOTES UPLOADED CARD - Count of study materials uploaded by this teacher -->
                    <div class="stat-card tile">
                        <div class="stat-icon icon">📤</div>
                        <div class="stat-value value"><?= $totalNotesUploaded ?></div>
                        <div class="stat-label label">Notes Uploaded</div>
                    </div>

                    <!-- QUESTIONS CREATED CARD - Count of quiz questions authored by this teacher -->
                    <div class="stat-card success tile">
                        <div class="stat-icon icon">❓</div>
                        <div class="stat-value value"><?= $totalQuestionsCreated ?></div>
                        <div class="stat-label label">Questions Created</div>
                    </div>

                    <!-- QUIZZES CREATED CARD - Count of quizzes authored by this teacher -->
                    <div class="stat-card warning tile">
                        <div class="stat-icon icon">📝</div>
                        <div class="stat-value value"><?= $totalQuizzesCreated ?></div>
                        <div class="stat-label label">Quizzes Created</div>
                    </div>

                    <!-- CLASS AVERAGE SCORE CARD - Average score of all students on teacher's quizzes -->
                    <div class="stat-card tile">
                        <div class="stat-icon icon">📊</div>
                        <div class="stat-value value"><?= $classAverage ?>%</div>
                        <div class="stat-label label">Class Average</div>
                    </div>
                </div>

                <!-- ====================================
                     MINI QUICK METRICS (Compact Version)
                     ==================================== -->
                <!-- Smaller stat cards showing student engagement -->
                <div class="mini-stats quick-metrics" style="margin-bottom: 32px;">
                    <!-- MINI STUDENT ATTEMPTS CARD -->
                    <div class="stat-card mini tile tile-mini">
                        <div class="stat-icon icon">👨‍🎓</div>
                        <div>
                            <div class="stat-value value"><?= $totalAttempts ?></div>
                            <div class="stat-label label">Student Attempts</div>
                        </div>
                    </div>

                    <!-- MINI MATERIALS CARD -->
                    <div class="stat-card mini tile tile-mini">
                        <div class="stat-icon icon">📚</div>
                        <div>
                            <div class="stat-value value"><?= $totalNotesUploaded ?></div>
                            <div class="stat-label label">Materials</div>
                        </div>
                    </div>

                    <!-- MINI QUESTIONS CARD -->
                    <div class="stat-card mini tile tile-mini">
                        <div class="stat-icon icon">🎯</div>
                        <div>
                            <div class="stat-value value"><?= $totalQuestionsCreated ?></div>
                            <div class="stat-label label">Questions</div>
                        </div>
                    </div>
                </div>

                <!-- ====================================
                     QUICK ACTIONS SECTION
                     ==================================== -->
                <!-- Fast navigation buttons to key teacher management features -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">⚡ Quick Actions</h3>
                    </div>
                    <!-- ACTION BUTTONS - Layout in flex row with wrap -->
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <!-- UPLOAD NOTES BUTTON - Add new study materials for students -->
                        <a href="upload_notes.php" class="btn btn-primary">📤 Upload Notes</a>
                        
                        <!-- ADD QUESTIONS BUTTON - Create quiz questions for assessments -->
                        <a href="manage_questions.php" class="btn btn-success">➕ Add Questions</a>
                        
                        <!-- MANAGE UPLOADS BUTTON - Edit/delete previously uploaded notes -->
                        <a href="my_uploads.php" class="btn btn-secondary">📝 Manage Uploads</a>
                        
                        <!-- VIEW PERFORMANCE BUTTON - Analyze student performance on quizzes -->
                        <a href="teacher_performance.php" class="btn btn-secondary">📊 Class Performance</a>
                    </div>
                </div>

                <!-- ====================================
                     RECENT UPLOADS SECTION
                     ==================================== -->
                <!-- Shows teacher's latest uploaded materials -->
                <?php if ($recentUploads && $recentUploads->num_rows > 0): ?>
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">📤 Recent Uploads</h3>
                    </div>
                    <!-- RECENT UPLOADS TABLE - Latest materials ordered by date -->
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Size</th>
                                    <th>Upload Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- LOOP THROUGH RECENT UPLOADS -->
                                <?php while ($upload = $recentUploads->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($upload['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($upload['subject_name']) ?></td>
                                    <td><?= round($upload['file_size'] / 1024, 2) ?> KB</td>
                                    <td><?= date('M d, Y', strtotime($upload['upload_date'])) ?></td>
                                    <td>
                                        <a href="my_uploads.php" class="btn btn-sm btn-secondary">Manage</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ====================================
                     MY SUBJECTS SECTION
                     ==================================== -->
                <!-- Displays subjects where teacher has uploaded materials or created quizzes -->
                <?php if ($subjects && $subjects->num_rows > 0): ?>
                <h2 style="margin-bottom: 24px;">👨‍🏫 My Subjects</h2>
                <div class="subjects-grid">
                    <!-- LOOP THROUGH TEACHER'S SUBJECTS -->
                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                    
                    <!-- SUBJECT CARD - Individual card for each subject taught by teacher -->
                    <div class="subject-card">
                        <!-- SUBJECT HEADER - Icon and name -->
                        <div class="subject-header">
                            <!-- COLORED ICON BOX - Uses subject's color stored in database -->
                            <div class="subject-icon" style="background: <?= $subject['color'] ?>20; color: <?= $subject['color'] ?>;">
                                👨‍🏫
                            </div>
                            <!-- SUBJECT NAME HEADING -->
                            <h3 class="subject-name"><?= htmlspecialchars($subject['name']) ?></h3>
                        </div>
                        
                        <!-- YEAR AND SEMESTER BADGE - Academic year info -->
                        <p class="subject-desc"><?= $subject['year'] ?> - Semester <?= $subject['semester'] ?></p>
                        
                        <!-- SUBJECT DESCRIPTION TEXT - Brief description of subject -->
                        <p class="subject-desc"><?= htmlspecialchars($subject['description']) ?></p>
                        
                        <!-- ACTION BUTTONS - Navigate to manage materials and quizzes for this subject -->
                        <div class="subject-actions">
                            <!-- UPLOAD MATERIALS BUTTON - Add study notes for this subject -->
                            <a href="upload_notes.php?subject=<?= $subject['id'] ?>" class="btn btn-sm btn-primary">📤 Upload</a>
                            
                            <!-- ADD QUESTIONS BUTTON - Create quiz questions for this subject -->
                            <a href="manage_questions.php?subject=<?= $subject['id'] ?>" class="btn btn-sm btn-success">➕ Questions</a>
                        </div>
                    </div>
                    
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <!-- MESSAGE IF NO SUBJECTS - Display when teacher hasn't uploaded anything yet -->
                <div class="card">
                    <p style="text-align: center; color: #666;">
                        No subjects yet. <a href="upload_notes.php">Upload your first material</a> to get started! 📝
                    </p>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
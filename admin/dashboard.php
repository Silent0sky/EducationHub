<?php
/* Admin dashboard - platform stats and admin management tiles */

require_once '../config/functions.php';
requireAdmin(); // Only admins can access

$pageTitle = 'Admin Dashboard';

/* === Count queries for stat cards === */
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalStudents = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'student'")->fetch_assoc()['c'];
$totalTeachers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'teacher'")->fetch_assoc()['c'];
$totalSubjects = $conn->query("SELECT COUNT(*) as c FROM subjects")->fetch_assoc()['c'];
$totalNotes = $conn->query("SELECT COUNT(*) as c FROM notes")->fetch_assoc()['c'];
$totalQuestions = $conn->query("SELECT COUNT(*) as c FROM questions")->fetch_assoc()['c'];
$totalQuizzes = $conn->query("SELECT COUNT(*) as c FROM quiz_results")->fetch_assoc()['c'];

/* Recent users (last 5 registered) */
$recentUsers = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

/* All uploaded notes with uploader name and subject */
$allNotes = $conn->query("
    SELECT n.*, u.name as uploader_name, s.name as subject_name, s.color as subject_color
    FROM notes n
    JOIN users u ON n.uploaded_by = u.id
    JOIN subjects s ON n.subject_id = s.id
    ORDER BY n.created_at DESC
    LIMIT 10
");

/* All questions with creator name and subject */
$allQuestions = $conn->query("
    SELECT q.*, u.name as creator_name, s.name as subject_name
    FROM questions q
    JOIN users u ON q.created_by = u.id
    JOIN subjects s ON q.subject_id = s.id
    ORDER BY q.created_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Education Hub</title>
    <!-- Global CSS - Variables, layout, sidebar, header -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <!-- Common CSS - Buttons, forms, cards, tables -->
    <link rel="stylesheet" href="../assets/css/common.css">
    <!-- Admin Dashboard CSS - Admin-specific styling -->
    <link rel="stylesheet" href="../assets/css/dashboard_admin.css">
</head>
<body>
    <div class="layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include '../includes/header.php'; ?>

            <section class="dashboard-content">
                <!-- === SYSTEM OVERVIEW (3-Column Admin Tiles) === -->
                <!-- Platform overview with Students, Teachers, Subjects counts -->
                <div class="admin-tiles-grid">
                    <!-- STUDENTS TILE - Count of all student users in system -->
                    <div class="admin-tile admin-tile-students">
                        <div class="admin-tile-icon">🎓</div>
                        <div class="admin-tile-content">
                            <div class="admin-tile-value"><?= $totalStudents ?></div>
                            <div class="admin-tile-label">Students</div>
                        </div>
                    </div>

                    <!-- TEACHERS TILE - Count of all teacher users in system -->
                    <div class="admin-tile admin-tile-teachers">
                        <div class="admin-tile-icon">👨‍🏫</div>
                        <div class="admin-tile-content">
                            <div class="admin-tile-value"><?= $totalTeachers ?></div>
                            <div class="admin-tile-label">Teachers</div>
                        </div>
                    </div>

                    <!-- SUBJECTS TILE - Count of all academic subjects -->
                    <div class="admin-tile admin-tile-subjects">
                        <div class="admin-tile-icon">📚</div>
                        <div class="admin-tile-content">
                            <div class="admin-tile-value"><?= $totalSubjects ?></div>
                            <div class="admin-tile-label">Subjects</div>
                        </div>
                    </div>
                </div>

                <!-- === DETAILED STATISTICS (Full-Width Cards) === -->
                <!-- Additional platform metrics below overview tiles -->
                <h3 class="admin-stats-heading">Platform Statistics</h3>
                <div class="admin-stats">
                    <!-- TOTAL USERS CARD - Count of all registered users -->
                    <div class="stat-card" style="position: relative;">
                        <div class="admin-stats-icon" style="font-size: 40px;">👥</div>
                        <div class="admin-stats-content">
                            <div class="admin-stats-value"><?= $totalUsers ?></div>
                            <div class="admin-stats-label">Total Users</div>
                        </div>
                    </div>

                    <!-- NOTES CARD - Total notes uploaded to system -->
                    <div class="stat-card" style="position: relative;">
                        <div class="admin-stats-icon" style="font-size: 40px;">📝</div>
                        <div class="admin-stats-content">
                            <div class="admin-stats-value"><?= $totalNotes ?></div>
                            <div class="admin-stats-label">Notes Uploaded</div>
                        </div>
                    </div>

                    <!-- QUESTIONS CARD - Total quiz questions in system -->
                    <div class="stat-card" style="position: relative;">
                        <div class="admin-stats-icon" style="font-size: 40px;">❓</div>
                        <div class="admin-stats-content">
                            <div class="admin-stats-value"><?= $totalQuestions ?></div>
                            <div class="admin-stats-label">Quiz Questions</div>
                        </div>
                    </div>

                    <!-- QUIZ ATTEMPTS CARD - Total quiz attempts by all students -->
                    <div class="stat-card" style="position: relative;">
                        <div class="admin-stats-icon" style="font-size: 40px;">📊</div>
                        <div class="admin-stats-content">
                            <div class="admin-stats-value"><?= $totalQuizzes ?></div>
                            <div class="admin-stats-label">Quiz Attempts</div>
                        </div>
                    </div>
                </div>

                <!-- === QUICK ACTIONS === -->
                <!-- Fast navigation buttons for common admin tasks -->
                <div class="card" style="margin-bottom: 32px; margin-top: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">⚡ Admin Actions</h3>
                    </div>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <a href="users.php" class="btn btn-primary">👥 Manage Users</a>
                        <a href="subjects.php" class="btn btn-secondary">📚 Manage Subjects</a>
                        <a href="../teacher_performance.php" class="btn btn-secondary">📊 Student Performance</a>
                        <a href="../upload_notes.php" class="btn btn-secondary">📤 Upload Notes</a>
                        <a href="../manage_questions.php" class="btn btn-secondary">➕ Add Questions</a>
                    </div>
                </div>

                <!-- === RECENT USERS TABLE === -->
                <!-- Show latest registered users -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">👥 Recent Users</h3>
                        <a href="users.php" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- LOOP THROUGH RECENT USERS -->
                                <?php while ($user = $recentUsers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="role-badge <?= strtolower($user['role']) ?>">
                                            <?= $user['role'] ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($user['created_at']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- === ALL UPLOADED NOTES === -->
                <!-- Admin can see all notes uploaded across the platform -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">📝 All Uploaded Notes</h3>
                    </div>
                    <?php if ($allNotes->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Uploaded By</th>
                                    <th>Downloads</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($note = $allNotes->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($note['title']) ?></strong></td>
                                    <td>
                                        <span style="background: <?= $note['subject_color'] ?>20; color: <?= $note['subject_color'] ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                            <?= htmlspecialchars($note['subject_name']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($note['uploader_name']) ?></td>
                                    <td><strong>⬇️ <?= $note['downloads'] ?></strong></td>
                                    <td><?= formatDate($note['created_at']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 24px;">No notes uploaded yet.</p>
                    <?php endif; ?>
                </div>

                <!-- === ALL QUIZ QUESTIONS === -->
                <!-- Admin can see all quiz questions from all teachers -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">❓ All Quiz Questions</h3>
                    </div>
                    <?php if ($allQuestions->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Subject</th>
                                    <th>Created By</th>
                                    <th>Correct</th>
                                    <th>Difficulty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($q = $allQuestions->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars(substr($q['question_text'], 0, 60)) ?>...</td>
                                    <td><?= htmlspecialchars($q['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($q['creator_name']) ?></td>
                                    <td><strong style="color: var(--success);"><?= $q['correct_answer'] ?></strong></td>
                                    <td style="text-transform: capitalize;"><?= $q['difficulty'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 24px;">No questions added yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

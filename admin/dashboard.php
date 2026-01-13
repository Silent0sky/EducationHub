<?php
/**
 * Education Hub - Admin Dashboard
 */

require_once '../config/functions.php';
requireAdmin();

$pageTitle = 'Admin Dashboard';

// Get counts
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalStudents = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'student'")->fetch_assoc()['c'];
$totalTeachers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'teacher'")->fetch_assoc()['c'];
$totalSubjects = $conn->query("SELECT COUNT(*) as c FROM subjects")->fetch_assoc()['c'];
$totalNotes = $conn->query("SELECT COUNT(*) as c FROM notes")->fetch_assoc()['c'];
$totalQuestions = $conn->query("SELECT COUNT(*) as c FROM questions")->fetch_assoc()['c'];
$totalQuizzes = $conn->query("SELECT COUNT(*) as c FROM quiz_results")->fetch_assoc()['c'];

// Recent users
$recentUsers = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Education Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../includes/header.php'; ?>
            
            <section>
                <!-- Admin Stats -->
                <div class="admin-stats">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value"><?= $totalUsers ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🎓</div>
                        <div class="stat-value"><?= $totalStudents ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon">👨‍🏫</div>
                        <div class="stat-value"><?= $totalTeachers ?></div>
                        <div class="stat-label">Teachers</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-icon">📚</div>
                        <div class="stat-value"><?= $totalSubjects ?></div>
                        <div class="stat-label">Subjects</div>
                    </div>
                </div>
                
                <div class="stats-grid" style="margin-bottom: 32px;">
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-value"><?= $totalNotes ?></div>
                        <div class="stat-label">Notes Uploaded</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">❓</div>
                        <div class="stat-value"><?= $totalQuestions ?></div>
                        <div class="stat-label">Quiz Questions</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-value"><?= $totalQuizzes ?></div>
                        <div class="stat-label">Quizzes Taken</div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header">
                        <h3 class="card-title">Admin Actions</h3>
                    </div>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <a href="users.php" class="btn btn-primary">👥 Manage Users</a>
                        <a href="subjects.php" class="btn btn-secondary">📚 Manage Subjects</a>
                        <a href="../upload_notes.php" class="btn btn-secondary">📤 Upload Notes</a>
                        <a href="../manage_questions.php" class="btn btn-secondary">➕ Add Questions</a>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Users</h3>
                        <a href="users.php" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $recentUsers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span style="text-transform: capitalize; 
                                            color: <?= $user['role'] === 'admin' ? 'var(--danger)' : ($user['role'] === 'teacher' ? 'var(--success)' : 'var(--primary)') ?>;">
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
            </section>
        </main>
    </div>
</body>
</html>

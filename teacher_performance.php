<?php
/* Teacher view of student performance - analytics and progress tracking */

require_once 'config/functions.php';
requireLogin();

/* Only teachers and admins can view student performance */
if (!isTeacher() && !isAdmin()) {
    redirect('dashboard.php');
}

$pageTitle = 'Student Performance';

/* --- Get Semester Filter --- */
/* Reads ?semester=2 from URL, defaults to 0 (all semesters) */
$semesterFilter = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;

/* --- Build WHERE clause for semester filter --- */
$whereClause = '';
if ($semesterFilter > 0) {
    $whereClause = " AND s.semester = $semesterFilter";
}

/* --- Query: Get each student's quiz stats --- */
/* LEFT JOIN ensures students with 0 quizzes still appear */
$students = $conn->query("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.year,
        COUNT(qr.id) as total_quizzes,
        ROUND(AVG(qr.percentage), 1) as avg_accuracy,
        MAX(qr.taken_at) as last_active
    FROM users u
    LEFT JOIN quiz_results qr ON u.id = qr.user_id
    LEFT JOIN subjects s ON qr.subject_id = s.id
    WHERE u.role = 'student' $whereClause
    GROUP BY u.id
    ORDER BY avg_accuracy DESC
");

/* --- Calculate Summary Stats --- */
$totalStudents = 0;
$totalAccuracy = 0;
$excellentCount = 0;
$studentRows = [];

/* Loop through results to calculate summary */
while ($row = $students->fetch_assoc()) {
    $studentRows[] = $row;
    $totalStudents++;
    $acc = $row['avg_accuracy'] ?? 0;
    $totalAccuracy += $acc;
    if ($acc >= 80) $excellentCount++;
}
$avgAccuracy = $totalStudents > 0 ? round($totalAccuracy / $totalStudents, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance - Education Hub</title>
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
                <!-- === Summary Stats Cards === -->
                <div class="stats-grid grid">
                    <div class="stat-card tile">
                        <div class="stat-icon icon">👥</div>
                        <div class="stat-value value"><?= $totalStudents ?></div>
                        <div class="stat-label label">Total Students</div>
                    </div>
                    <div class="stat-card success tile">
                        <div class="stat-icon icon">🎯</div>
                        <div class="stat-value value"><?= $avgAccuracy ?>%</div>
                        <div class="stat-label label">Avg Accuracy</div>
                    </div>
                    <div class="stat-card warning tile">
                        <div class="stat-icon icon">🌟</div>
                        <div class="stat-value value"><?= $excellentCount ?></div>
                        <div class="stat-label label">Top Performers</div>
                    </div>
                </div>

                <!-- === Semester Filter Dropdown === -->
                <!-- Auto-submits when selection changes (onchange) -->
                <div class="card" style="margin-bottom: 24px;">
                    <div class="card-header">
                        <h3 class="card-title">📊 Filter by Semester</h3>
                    </div>
                    <form method="GET" style="display: flex; gap: 12px; align-items: center;">
                        <select name="semester" class="form-input" style="max-width: 200px; padding: 10px; border-radius: 8px; background: var(--surface-light); color: var(--text); border: 1px solid var(--border);" onchange="this.form.submit()">
                            <option value="0">All Semesters</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= $semesterFilter === $i ? 'selected' : '' ?>>
                                Semester <?= $i ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>

                <!-- === Student Performance Table === -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">👥 Student Performance (<?= $totalStudents ?>)</h3>
                    </div>

                    <?php if ($totalStudents > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Year</th>
                                    <th>Quizzes</th>
                                    <th>Accuracy</th>
                                    <th>Status</th>
                                    <th>Last Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 1; foreach ($studentRows as $row): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['year'] ?? '-') ?></td>
                                    <td><?= $row['total_quizzes'] ?></td>
                                    <td><strong><?= $row['avg_accuracy'] ?? 0 ?>%</strong></td>
                                    <td>
                                        <?php
                                        /* Color-coded status based on accuracy percentage */
                                        $acc = $row['avg_accuracy'] ?? 0;
                                        if ($acc >= 80) echo '<span style="color: var(--success); font-weight: 600;">✓ Excellent</span>';
                                        elseif ($acc >= 60) echo '<span style="color: var(--primary); font-weight: 600;">Good</span>';
                                        elseif ($acc >= 40) echo '<span style="color: var(--warning); font-weight: 600;">Average</span>';
                                        else echo '<span style="color: var(--danger); font-weight: 600;">Needs Work</span>';
                                        ?>
                                    </td>
                                    <td><?= $row['last_active'] ? formatDate($row['last_active']) : 'Never' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 48px;">
                        No students found for this semester.
                    </p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

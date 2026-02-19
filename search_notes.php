<?php
/* Search and download notes filtered by year, semester, subject, and keyword */

require_once 'config/functions.php';
requireLogin();

$pageTitle = 'Search Notes';

/* Get all subjects for the dropdown filter */
$subjects = $conn->query("SELECT * FROM subjects ORDER BY year, semester, name");

/* --- Read filter values from URL (without htmlspecialchars encoding) --- */
$searchQuery = trim($_GET['search'] ?? '');
$subjectFilter = (int)($_GET['subject'] ?? 0);
$yearFilter = trim($_GET['year'] ?? '');
$semesterFilter = (int)($_GET['semester'] ?? 0);

/* --- Build dynamic SQL query using prepared statements --- */
$sql = "SELECT n.*, s.name as subject_name, s.color as subject_color, s.year, s.semester, u.name as uploader_name 
        FROM notes n 
        JOIN subjects s ON n.subject_id = s.id 
        JOIN users u ON n.uploaded_by = u.id 
        WHERE 1=1";

$params = [];
$types = "";

/* Add WHERE conditions based on active filters */
if ($searchQuery) {
    $sql .= " AND (n.title LIKE ? OR n.content LIKE ?)";
    $searchWildcard = "%$searchQuery%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= "ss";
}
if ($subjectFilter) {
    $sql .= " AND n.subject_id = ?";
    $params[] = $subjectFilter;
    $types .= "i";
}
if ($yearFilter) {
    $sql .= " AND s.year = ?";
    $params[] = $yearFilter;
    $types .= "s";
}
if ($semesterFilter) {
    $sql .= " AND s.semester = ?";
    $params[] = $semesterFilter;
    $types .= "i";
}

$sql .= " ORDER BY s.year, s.semester, n.created_at DESC";

/* Execute prepared statement */
$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $notes = $stmt->get_result();
} else {
    $notes = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Notes - Education Hub</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/notes.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <section>
                <!-- === Hero Section === -->
                <div class="notes-hero">
                    <h1>📚 Study Materials</h1>
                    <p>Find notes by year, semester, or subject</p>
                </div>

                <!-- === Year Tabs (FY / SY / TY) === -->
                <!-- Each tab links back to this page with year parameter -->
                <div class="year-tabs">
                    <a href="?<?php echo http_build_query(array_filter(['search' => $searchQuery, 'subject' => $subjectFilter])); ?>" class="year-tab <?= empty($yearFilter) ? 'active' : '' ?>">All Years</a>
                    <a href="?<?php echo http_build_query(array_filter(['year' => 'FY', 'search' => $searchQuery, 'subject' => $subjectFilter])); ?>" class="year-tab <?= $yearFilter === 'FY' ? 'active' : '' ?>">
                        <span class="year-badge fy">FY</span> First Year
                    </a>
                    <a href="?<?php echo http_build_query(array_filter(['year' => 'SY', 'search' => $searchQuery, 'subject' => $subjectFilter])); ?>" class="year-tab <?= $yearFilter === 'SY' ? 'active' : '' ?>">
                        <span class="year-badge sy">SY</span> Second Year
                    </a>
                    <a href="?<?php echo http_build_query(array_filter(['year' => 'TY', 'search' => $searchQuery, 'subject' => $subjectFilter])); ?>" class="year-tab <?= $yearFilter === 'TY' ? 'active' : '' ?>">
                        <span class="year-badge ty">TY</span> Third Year
                    </a>
                </div>

                <!-- === Semester Tabs (shown only when year is selected) === -->
                <?php if ($yearFilter): ?>
                <div class="semester-tabs">
                    <?php 
                    /* Map each year to its semesters */
                    $semesters = ['FY' => [1, 2], 'SY' => [3, 4], 'TY' => [5, 6]];
                    $availableSems = $semesters[$yearFilter] ?? [];
                    ?>
                    <a href="?<?php echo http_build_query(array_filter(['year' => $yearFilter, 'search' => $searchQuery, 'subject' => $subjectFilter])); ?>" class="semester-tab <?= !$semesterFilter ? 'active' : '' ?>">All Semesters</a>
                    <?php foreach ($availableSems as $sem): ?>
                    <a href="?<?php echo http_build_query(array_filter(['year' => $yearFilter, 'semester' => $sem, 'search' => $searchQuery, 'subject' => $subjectFilter])); ?>" 
                       class="semester-tab <?= $semesterFilter == $sem ? 'active' : '' ?>">
                        Semester <?= $sem ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- === Search Bar with Subject Filter === -->
                <form method="GET" class="search-bar modern-search">
                    <!-- Preserve year/semester filters when searching -->
                    <input type="hidden" name="year" value="<?= htmlspecialchars($yearFilter) ?>">
                    <input type="hidden" name="semester" value="<?= $semesterFilter ?>">

                    <!-- Search input with icon -->
                    <div class="search-input-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search notes by title or keyword..." 
                               value="<?= htmlspecialchars($searchQuery) ?>">
                    </div>

                    <!-- Subject dropdown filter -->
                    <select name="subject" class="filter-select modern-select">
                        <option value="">All Subjects</option>
                        <?php 
                        $subjects->data_seek(0); // Reset pointer to loop again
                        while ($subject = $subjects->fetch_assoc()): 
                            /* Only show subjects matching current year/semester filter */
                            if ($yearFilter && $subject['year'] !== $yearFilter) continue;
                            if ($semesterFilter && $subject['semester'] != $semesterFilter) continue;
                        ?>
                        <option value="<?= $subject['id'] ?>" <?= $subjectFilter == $subject['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subject['name']) ?> (Sem <?= $subject['semester'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>

                    <button type="submit" class="btn btn-primary btn-search">🔍 Search</button>
                </form>

                <!-- === Notes Results Grid === -->
                <div class="notes-grid">
                    <?php if ($notes->num_rows > 0): ?>
                        <?php while ($note = $notes->fetch_assoc()): ?>
                        <div class="note-card modern-card">
                            <!-- Note header: subject badge + semester -->
                            <div class="note-header">
                                <span class="subject-badge" style="background: <?= $note['subject_color'] ?>20; color: <?= $note['subject_color'] ?>;">
                                    <?= htmlspecialchars($note['subject_name']) ?>
                                </span>
                                <span class="semester-badge"><?= $note['year'] ?> - Sem <?= $note['semester'] ?></span>
                            </div>
                            <!-- Note title -->
                            <h3 class="note-title"><?= htmlspecialchars($note['title']) ?></h3>
                            <!-- Metadata: uploader, date, downloads -->
                            <div class="note-meta">
                                <span>📤 <?= htmlspecialchars($note['uploader_name']) ?></span>
                                <span>📅 <?= formatDate($note['created_at']) ?></span>
                                <span>⬇️ <?= $note['downloads'] ?> downloads</span>
                            </div>
                            <!-- Content preview (first 100 chars) -->
                            <p class="note-content"><?= htmlspecialchars(substr($note['content'], 0, 100)) ?>...</p>
                            <!-- Download button -->
                            <a href="download_notes.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-primary btn-download">
                                📥 Download
                            </a>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Empty state when no notes match filters -->
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <h3>No notes found</h3>
                            <p>Try adjusting your filters or search query</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

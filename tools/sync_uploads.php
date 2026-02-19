<?php
require_once __DIR__ . '/../config/functions.php';

// Admin-only sync
requireLogin();
if (!hasRole('admin')) {
    http_response_code(403);
    echo "Forbidden - admin only";
    exit;
}

$uploadDir = __DIR__ . '/../uploads/notes';
if (!is_dir($uploadDir)) {
    echo "Upload folder not found: uploads/notes";
    exit;
}

$files = array_values(array_filter(scandir($uploadDir), function($f){
    return $f !== '.' && $f !== '..' && !is_dir(__DIR__ . '/../uploads/notes/' . $f);
}));

$inserted = 0;
$skipped = 0;
$errors = [];
$added = [];

foreach ($files as $file) {
    $relativePath = 'uploads/notes/' . $file;
    // check if already in DB
    $stmt = $conn->prepare("SELECT id FROM notes WHERE file_path = ? LIMIT 1");
    $stmt->bind_param('s', $relativePath);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $skipped++;
        continue;
    }

    // derive title from filename
    $title = pathinfo($file, PATHINFO_FILENAME);

    // default subject and uploader
    $subjectId = 1;
    $uploadedBy = $_SESSION['user_id'] ?? 1;

    $ins = $conn->prepare("INSERT INTO notes (title, content, file_path, subject_id, uploaded_by, downloads) VALUES (?, NULL, ?, ?, ?, 0)");
    $ins->bind_param('siii', $title, $relativePath, $subjectId, $uploadedBy);
    if ($ins->execute()) {
        $inserted++;
        $added[] = ['id' => $conn->insert_id, 'title' => $title, 'path' => $relativePath];
    } else {
        $errors[] = $conn->error;
    }
}

// Output summary
?><!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Sync Uploads</title></head><body>
<h2>Sync uploads/notes</h2>
<p>Found files: <?= count($files) ?></p>
<p>Inserted: <?= $inserted ?> — Skipped (already present): <?= $skipped ?></p>
<?php if (!empty($added)): ?>
<h3>Added notes</h3>
<ul>
<?php foreach ($added as $a): ?>
    <li><a href="/EDUCATION_HUB/download_notes.php?id=<?= $a['id'] ?>"><?= htmlspecialchars($a['title']) ?></a> — <a href="/EDUCATION_HUB/<?= htmlspecialchars($a['path']) ?>" target="_blank">file</a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<h3>Errors</h3>
<ul>
<?php foreach ($errors as $e): ?>
    <li><?= htmlspecialchars($e) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<p><a href="../admin/dashboard.php">Back to Admin Dashboard</a></p>
</body></html>

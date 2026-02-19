<?php
/* File download handler - serves note files for download */

require_once 'config/functions.php';
requireLogin();

/* Get note ID from URL query parameter */
$noteId = (int)($_GET['id'] ?? 0);

/* If no ID provided, redirect back to search page */
if (!$noteId) {
    redirect('search_notes.php');
}

/* Query note details from database (prepared statement for safety) */
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

/* If note doesn't exist, redirect with error */
if (!$note) {
    redirect('search_notes.php?error=not_found');
}

/* Increment download counter */
$conn->query("UPDATE notes SET downloads = downloads + 1 WHERE id = $noteId");

/* Record download in history */
$userId = $_SESSION['user_id'];
$historyStmt = $conn->prepare("INSERT INTO download_history (user_id, note_id, downloaded_at) VALUES (?, ?, NOW())");
$historyStmt->bind_param("ii", $userId, $noteId);
$historyStmt->execute();
$historyStmt->close();

/* Serve the file for download */
if ($note['file_path'] && file_exists($note['file_path'])) {
    /* Physical file exists → serve it directly */
    $filePath = $note['file_path'];
    $fileName = basename($filePath);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));

    readfile($filePath); // Output file contents
    exit();
} else {
    /* No physical file → generate text file from content field */
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . sanitize($note['title']) . '.txt"');

    echo "Title: " . $note['title'] . "\n";
    echo "================================\n\n";
    echo $note['content'];
    exit();
}
?>

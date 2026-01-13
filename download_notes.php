<?php
/**
 * Education Hub - Download Note Handler
 */

require_once 'config/functions.php';
requireLogin();

$noteId = (int)($_GET['id'] ?? 0);

if (!$noteId) {
    redirect('search_notes.php');
}

// Get note details
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

if (!$note) {
    redirect('search_notes.php?error=not_found');
}

// Update download count
$conn->query("UPDATE notes SET downloads = downloads + 1 WHERE id = $noteId");

// If file exists, download it
if ($note['file_path'] && file_exists($note['file_path'])) {
    $filePath = $note['file_path'];
    $fileName = basename($filePath);
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    
    readfile($filePath);
    exit();
} else {
    // Generate text file with note content
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . sanitize($note['title']) . '.txt"');
    
    echo "Title: " . $note['title'] . "\n";
    echo "================================\n\n";
    echo $note['content'];
    exit();
}
?>

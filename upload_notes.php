<?php
/**
 * Education Hub - Upload Notes (Teachers Only)
 */

require_once 'config/functions.php';
requireTeacher();

$pageTitle = 'Upload Notes';
$success = '';
$error = '';

// Get subjects
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $uploadedBy = $_SESSION['user_id'];
    
    if (empty($title) || empty($subjectId)) {
        $error = 'Please fill in all required fields';
    } else {
        $filePath = null;
        
        // Handle file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/notes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['file']['name']);
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                $error = 'Failed to upload file';
            }
        }
        
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO notes (title, content, file_path, subject_id, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $title, $content, $filePath, $subjectId, $uploadedBy);
            
            if ($stmt->execute()) {
                $success = 'Note uploaded successfully!';
            } else {
                $error = 'Failed to save note';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Notes - Education Hub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <section>
                <div class="card" style="max-width: 600px;">
                    <?php if ($error): ?>
                        <?= showAlert($error, 'error') ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <?= showAlert($success, 'success') ?>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" placeholder="Enter note title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject_id">Subject *</label>
                            <select id="subject_id" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php while ($subject = $subjects->fetch_assoc()): ?>
                                <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Description/Content</label>
                            <textarea id="content" name="content" rows="5" placeholder="Enter note description or content..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="file">Upload File (PDF, DOC, etc.)</label>
                            <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">📤 Upload Note</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

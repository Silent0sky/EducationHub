<?php
/* 
   ============================================
   TEACHER NOTE UPLOAD PAGE - upload_notes.php
   ============================================
   Allows teachers to upload course materials:
   1. Validates form input (title, subject, file)
   2. Handles drag-and-drop file upload
   3. Stores file in uploads/notes directory
   4. Records upload in database with metadata
   
   Access: Teachers only (requireTeacher() check)
*/

/* Include configuration and helper functions */
require_once 'config/functions.php';
/* Verify user is a teacher before allowing access */
requireTeacher();

/* PAGE IDENTIFIER FOR HEADER */
$pageTitle = 'Upload Notes';
/* Will store success/error messages from form submission */
$success = '';
$error = '';

/* 
   FETCH ALL SUBJECTS - For dropdown selector
   Used to let teacher choose which subject to upload notes for
   Subjects include year and semester information
*/
$subjects = $conn->query("SELECT * FROM subjects ORDER BY year, semester, name");

/* 
   ============================================
   HANDLE FORM SUBMISSION (POST REQUEST)
   ============================================
   Process when teacher submits the upload form
   Validates inputs and saves note to database
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* 
       EXTRACT AND SANITIZE FORM INPUT
       - title: Note name for identification
       - content: Description or content of the notes
       - subject_id: Which subject this note belongs to
       - uploadedBy: Current teacher's ID from session
    */
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $uploadedBy = $_SESSION['user_id'];

    /* 
       VALIDATION - Check required fields
       Title and subject are mandatory
    */
    if (empty($title) || empty($subjectId)) {
        $error = 'Please fill in all required fields';
    } else {
        /* Initialize file path as null (optional file upload) */
        $filePath = null;

        /* 
           ============================================
           HANDLE FILE UPLOAD - Optional file attachment
           ============================================
           Process only if a file was successfully selected and uploaded
        */
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            /* TARGET DIRECTORY - Where uploaded files are stored */
            $uploadDir = 'uploads/notes/';
            /* CREATE DIRECTORY - If it doesn't exist, create it */
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            /* 
               GENERATE UNIQUE FILENAME
               Reasons:
               1. Prevent name collisions if multiple uploads have same filename
               2. Timestamps ensure uniqueness (time() returns seconds since epoch)
               3. Original name preserved for user reference
            */
            $fileName = time() . '_' . basename($_FILES['file']['name']);
            $filePath = $uploadDir . $fileName;

            /* 
               MOVE FILE - From PHP temporary location to permanent storage
               move_uploaded_file() is secure: only works with uploaded files
               If fails, set error message and skip database insertion
            */
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                $error = 'Failed to upload file';
            }
        }

        /* 
           ============================================
           DATABASE INSERTION - Save note record
           ============================================
           Only proceeds if no errors encountered
        */
        if (empty($error)) {
            /* 
               PREPARED STATEMENT - Prevent SQL injection
               Safely insert values into database with type binding
               Columns: title, content, file_path, subject_id, uploaded_by
            */
            $stmt = $conn->prepare("INSERT INTO notes (title, content, file_path, subject_id, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            /* 
               BIND PARAMETERS - Map values to placeholders
               "sssii" means: string, string, string, integer, integer
               Corresponding to: title, content, file_path, subjectId, uploadedBy
            */
            $stmt->bind_param("sssii", $title, $content, $filePath, $subjectId, $uploadedBy);

            /* EXECUTE INSERTION - Save to database */
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
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/notes.css">
    <link rel="stylesheet" href="assets/css/upload_notes.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <section>
                <!-- Hero banner -->
                <div class="upload-hero">
                    <h1>📤 Upload Notes</h1>
                    <p>Share study materials with students</p>
                </div>

                <div class="upload-container">
                    <!-- Alert messages -->
                    <?php if ($error): ?><?= showAlert($error, 'error') ?><?php endif; ?>
                    <?php if ($success): ?><?= showAlert($success, 'success') ?><?php endif; ?>

                    <!-- Upload form with enctype for file uploads -->
                    <form method="POST" enctype="multipart/form-data" class="upload-form">
                        <div class="form-grid">
                            <!-- Note title input -->
                            <div class="form-group full-width">
                                <label for="title">📝 Title *</label>
                                <input type="text" id="title" name="title" placeholder="Enter note title" required>
                            </div>

                            <!-- Year selector buttons (FY/SY/TY) -->
                            <div class="form-group">
                                <label>📅 Year</label>
                                <div class="year-selector">
                                    <button type="button" class="year-btn active" data-year="FY">FY</button>
                                    <button type="button" class="year-btn" data-year="SY">SY</button>
                                    <button type="button" class="year-btn" data-year="TY">TY</button>
                                </div>
                            </div>

                            <!-- Semester selector buttons (dynamic based on year) -->
                            <div class="form-group">
                                <label>📚 Semester</label>
                                <div class="semester-selector">
                                    <button type="button" class="sem-btn active" data-sem="1">Sem 1</button>
                                    <button type="button" class="sem-btn" data-sem="2">Sem 2</button>
                                </div>
                            </div>

                            <!-- Subject dropdown (filtered by JS based on year/semester) -->
                            <div class="form-group full-width">
                                <label for="subject_id">📖 Subject *</label>
                                <select id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                                    <option value="<?= $subject['id'] ?>" 
                                            data-year="<?= $subject['year'] ?>" 
                                            data-sem="<?= $subject['semester'] ?>">
                                        <?= htmlspecialchars($subject['name']) ?> (<?= $subject['year'] ?> - Sem <?= $subject['semester'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Description/content textarea -->
                            <div class="form-group full-width">
                                <label for="content">📋 Description/Content</label>
                                <textarea id="content" name="content" rows="5" placeholder="Enter note description or content..."></textarea>
                            </div>

                            <!-- File upload area with drag & drop -->
                            <div class="form-group full-width">
                                <label>📁 Upload File (PDF, DOC, etc.)</label>
                                <div class="file-upload-area" id="dropzone">
                                    <div class="upload-icon">📄</div>
                                    <p>Drag & drop your file here or <span class="browse-link">browse</span></p>
                                    <span class="file-types">Supports: PDF, DOC, DOCX, TXT, PPT, PPTX</span>
                                    <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx" hidden>
                                    <div class="selected-file" id="selectedFile"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-upload">📤 Upload Note</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <!-- ============================================
         JAVASCRIPT: FORM LOGIC + DRAG & DROP
         ============================================
         Handles:
         1. Year/Semester filtering for subject selector
         2. Drag-and-drop file upload
         3. File selection display
    -->
    <script>
        /* ============================================
           YEAR AND SEMESTER FILTER LOGIC
           ============================================
           Dynamically show/hide semester buttons based on year
           Filter subject options by selected year+semester
        */
        
        /* DOM ELEMENT REFERENCES - Cache selectors for performance */
        const yearBtns = document.querySelectorAll('.year-btn');
        const semBtns = document.querySelectorAll('.sem-btn');
        const subjectSelect = document.getElementById('subject_id');
        const options = subjectSelect.querySelectorAll('option');

        /* SEMESTER MAPPING BY YEAR */
        /* Determines which semesters are available for each academic year */
        const semestersByYear = { 'FY': [1, 2], 'SY': [3, 4], 'TY': [5, 6] };
        
        /* DEFAULT SELECTIONS - FY Year, Semester 1 */
        let selectedYear = 'FY';
        let selectedSem = 1;

        /* 
           UPDATE SEMESTER BUTTONS - When year changes, show correct semesters
           Example: FY shows Sem 1, Sem 2; SY shows Sem 3, Sem 4
        */
        function updateSemesterButtons() {
            /* Get the semesters for currently selected year */
            const sems = semestersByYear[selectedYear];
            
            /* Update each semester button with correct label and data */
            semBtns.forEach((btn, index) => {
                btn.textContent = 'Sem ' + sems[index];
                btn.dataset.sem = sems[index];
            });
            
            /* Reset to first semester of new year */
            selectedSem = sems[0];
            semBtns[0].classList.add('active');
            semBtns[1].classList.remove('active');
            
            /* Update visible subject options */
            filterSubjects();
        }

        /* 
           FILTER SUBJECT DROPDOWN - Show only subjects for selected year/semester
           Hide options that don't match the combination
        */
        function filterSubjects() {
            options.forEach(opt => {
                /* Always show the placeholder option */
                if (!opt.value) { opt.style.display = 'block'; return; }
                
                /* Show if subject's year and semester match selection */
                const show = opt.dataset.year === selectedYear && parseInt(opt.dataset.sem) === selectedSem;
                opt.style.display = show ? 'block' : 'none';
            });
            
            /* Clear any previously selected subject */
            subjectSelect.value = '';
        }

        /* ============================================
           YEAR BUTTON CLICK HANDLERS
           ============================================
           Toggle active state and update filtering
        */
        yearBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                /* Remove active class from all year buttons */
                yearBtns.forEach(b => b.classList.remove('active'));
                /* Add active class to clicked button */
                btn.classList.add('active');
                /* Update selected year and refilter */
                selectedYear = btn.dataset.year;
                updateSemesterButtons();
            });
        });

        /* ============================================
           SEMESTER BUTTON CLICK HANDLERS
           ============================================
        */
        semBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                /* Remove active class from all semester buttons */
                semBtns.forEach(b => b.classList.remove('active'));
                /* Add active class to clicked button */
                btn.classList.add('active');
                /* Parse semester number and refilter subjects */
                selectedSem = parseInt(btn.dataset.sem);
                filterSubjects();
            });
        });

        /* ============================================
           DRAG & DROP FILE UPLOAD
           ============================================
           Provides improved UX for file selection
           Allows drag-and-drop or click-to-browse
        */
        
        /* DOM ELEMENTS FOR FILE UPLOAD */
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file');
        const selectedFile = document.getElementById('selectedFile');

        /* CLICK DROPZONE - Opens file picker dialog */
        dropzone.addEventListener('click', () => fileInput.click());

        /* DRAG OVER - Add visual feedback when dragging over dropzone */
        dropzone.addEventListener('dragover', (e) => { 
            e.preventDefault(); 
            dropzone.classList.add('dragover'); 
        });

        /* DRAG LEAVE - Remove visual feedback when leaving dropzone */
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));

        /* DROP - Handle dropped files */
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            /* Assign dropped files to input element */
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showSelectedFile(e.dataTransfer.files[0]);
            }
        });

        /* FILE INPUT CHANGE - Show selected file when user picks via dialog */
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) showSelectedFile(fileInput.files[0]);
        });

        /* 
           DISPLAY SELECTED FILE - Show filename to user
           Gives visual confirmation of selected file before upload
        */
        function showSelectedFile(file) {
            selectedFile.innerHTML = `<span>📎 ${file.name}</span>`;
            selectedFile.style.display = 'block';
        }

        /* INITIALIZE - Filter subjects on page load */
        filterSubjects();
    </script>
</body>
</html>

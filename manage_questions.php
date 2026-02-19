<?php
/* 
   ============================================
   MANAGE QUIZ QUESTIONS - manage_questions.php
   ============================================
   Allows teachers to create and manage quiz questions:
   1. Add new multiple-choice questions with 4 options
   2. Specify difficulty level (Easy, Medium, Hard)
   3. Set correct answer from the 4 options
   4. View recently created questions
   
   Access: Teachers only (requireTeacher() check)
*/

/* Include configuration and helper functions */
require_once 'config/functions.php';
/* Only teachers can create quiz questions */
requireTeacher();

/* PAGE IDENTIFIER FOR HEADER */
$pageTitle = 'Manage Questions';
/* Will store success/error messages from form submission */
$success = '';
$error = '';

/* 
   FETCH ALL SUBJECTS - For dropdown selector in question form
   Teachers select which subject the question belongs to
   Subjects are sorted by year and semester
*/
$subjects = $conn->query("SELECT * FROM subjects ORDER BY year, semester, name");

/* 
   ============================================
   HANDLE FORM SUBMISSION (POST REQUEST)
   ============================================
   Process when teacher submits a new question
   Validates all inputs and saves to database
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* 
       EXTRACT AND SANITIZE FORM INPUT
       - question_text: The question being asked
       - subject_id: Which subject this question relates to
       - option_a/b/c/d: The four answer choices
       - correct_answer: Which option is correct (A, B, C, or D)
       - difficulty: Question difficulty level
       - createdBy: Current teacher's ID from session
    */
    $questionText = sanitize($_POST['question_text'] ?? '');
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $optionA = sanitize($_POST['option_a'] ?? '');
    $optionB = sanitize($_POST['option_b'] ?? '');
    $optionC = sanitize($_POST['option_c'] ?? '');
    $optionD = sanitize($_POST['option_d'] ?? '');
    /* Convert to uppercase to standardize (A, B, C, D) */
    $correctAnswer = strtoupper(sanitize($_POST['correct_answer'] ?? ''));
    /* Default to medium difficulty if not specified */
    $difficulty = sanitize($_POST['difficulty'] ?? 'medium');
    /* Teacher who created this question */
    $createdBy = $_SESSION['user_id'];

    /* 
       VALIDATION - ALL FIELDS REQUIRED
       Cannot create question without any of these fields
    */
    if (empty($questionText) || empty($subjectId) || empty($optionA) || 
        empty($optionB) || empty($optionC) || empty($optionD) || empty($correctAnswer)) {
        $error = 'Please fill in all required fields';
    }
    /* VALIDATE CORRECT ANSWER - Must be A, B, C, or D */
    elseif (!in_array($correctAnswer, ['A', 'B', 'C', 'D'])) {
        $error = 'Invalid correct answer';
    } else {
        /* 
           ============================================
           DATABASE INSERTION - Save question record
           ============================================
           Prepared statement prevents SQL injection
           Stores all question data with metadata
        */
        $stmt = $conn->prepare("INSERT INTO questions 
                          (subject_id, question_text, option_a, option_b, 
                           option_c, option_d, correct_answer, difficulty, created_by) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        /* 
           BIND PARAMETERS - Type mapping:
           "isssssssi" = integer, string, string, string, string, string, string, string, integer
           Maps to: subjectId, questionText, optionA, optionB, optionC, optionD, 
                   correctAnswer, difficulty, createdBy
        */
        $stmt->bind_param("isssssssi", $subjectId, $questionText, $optionA, $optionB, 
                         $optionC, $optionD, $correctAnswer, $difficulty, $createdBy);

        /* EXECUTE INSERTION - Save to database */
        if ($stmt->execute()) {
            $success = 'Question added successfully!';
        } else {
            $error = 'Failed to add question';
        }
        $stmt->close();
    }
}

/* 
   ============================================
   FETCH TEACHER'S RECENT QUESTIONS
   ============================================
   Get questions created by current teacher
   Limit to 20 most recent for display
   Joined with subjects table for name display
*/
$userId = $_SESSION['user_id'];
$myQuestions = $conn->query("
    SELECT q.*, s.name as subject_name 
    FROM questions q 
    JOIN subjects s ON q.subject_id = s.id 
    WHERE q.created_by = $userId 
    ORDER BY q.created_at DESC 
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Education Hub</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <section>
                <!-- ============================================
                     ADD NEW QUESTION FORM
                     ============================================
                     Allows teacher to create new quiz questions
                     with 4 multiple choice options
                --> 
                <div class="card" style="margin-bottom: 32px;">
                    <h3 style="margin-bottom: 24px;">➕ Add New Question</h3>

                    <!-- ALERT MESSAGES - Show errors or success -->
                    <?php if ($error): ?><?= showAlert($error, 'error') ?><?php endif; ?>
                    <?php if ($success): ?><?= showAlert($success, 'success') ?><?php endif; ?>

                    <!-- QUESTION FORM - Submits via POST to same page -->
                    <form method="POST">
                        <!-- SUBJECT AND DIFFICULTY (side-by-side layout) -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <!-- SUBJECT SELECTOR - Which subject this question belongs to -->
                            <div class="form-group">
                                <label for="subject_id">📖 Subject *</label>
                                <select id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php 
                                    /* Reset result pointer to beginning of subjects query */
                                    $subjects->data_seek(0);
                                    /* Loop through subjects for dropdown options */
                                    while ($subject = $subjects->fetch_assoc()): 
                                    ?>
                                    <option value="<?= $subject['id'] ?>">
                                        <?= htmlspecialchars($subject['name']) ?> (<?= $subject['year'] ?> Sem <?= $subject['semester'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <!-- DIFFICULTY LEVEL - Easy/Medium/Hard -->
                            <!-- Helps organize questions and provide varied quiz difficulty -->
                            <div class="form-group">
                                <label for="difficulty">📊 Difficulty</label>
                                <select id="difficulty" name="difficulty">
                                    <option value="easy">Easy</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                        </div>

                        <!-- QUESTION TEXT - The main inquiry -->
                        <div class="form-group">
                            <label for="question_text">❓ Question *</label>
                            <textarea id="question_text" name="question_text" rows="3" 
                                     placeholder="Enter the question..." required></textarea>
                        </div>

                        <!-- FOUR MULTIPLE CHOICE OPTIONS (2x2 grid layout) -->
                        <!-- Students will choose one of these as their answer -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <!-- OPTION A -->
                            <div class="form-group">
                                <label for="option_a">🅰️ Option A *</label>
                                <input type="text" id="option_a" name="option_a" 
                                      placeholder="Option A" required>
                            </div>
                            
                            <!-- OPTION B -->
                            <div class="form-group">
                                <label for="option_b">🅱️ Option B *</label>
                                <input type="text" id="option_b" name="option_b" 
                                      placeholder="Option B" required>
                            </div>
                            
                            <!-- OPTION C -->
                            <div class="form-group">
                                <label for="option_c">©️ Option C *</label>
                                <input type="text" id="option_c" name="option_c" 
                                      placeholder="Option C" required>
                            </div>
                            
                            <!-- OPTION D -->
                            <div class="form-group">
                                <label for="option_d">🅳 Option D *</label>
                                <input type="text" id="option_d" name="option_d" 
                                      placeholder="Option D" required>
                            </div>
                        </div>

                        <!-- CORRECT ANSWER SELECTOR -->
                        <!-- Teacher marks which of the 4 options is correct -->
                        <div class="form-group">
                            <label for="correct_answer">✅ Correct Answer *</label>
                            <select id="correct_answer" name="correct_answer" required>
                                <option value="">Select Correct Answer</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>

                        <!-- SUBMIT BUTTON - Save question to database -->
                        <button type="submit" class="btn btn-primary">➕ Add Question</button>
                    </form>
                </div>

                <!-- ============================================
                     RECENT QUESTIONS TABLE
                     ============================================
                     Shows the 20 most recent questions created by this teacher
                     Allows quick review of previously created questions
                -->
                <div class="card">
                    <h3 style="margin-bottom: 24px;">📋 My Recent Questions</h3>

                    <?php if ($myQuestions->num_rows > 0): ?>
                    <!-- TABLE VIEW - Shows question details in structured format -->
                    <div class="table-container">
                        <table>
                            <!-- TABLE HEADER - Column titles -->
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Subject</th>
                                    <th>Difficulty</th>
                                    <th>Correct</th>
                                </tr>
                            </thead>
                            <!-- TABLE BODY - Each row is one question -->
                            <tbody>
                                <?php while ($q = $myQuestions->fetch_assoc()): ?>
                                <tr>
                                    <!-- QUESTION TEXT (truncated to 60 chars for display) -->
                                    <td><?= htmlspecialchars(substr($q['question_text'], 0, 60)) ?>...</td>
                                    <!-- SUBJECT NAME - Which subject this question is for -->
                                    <td><?= htmlspecialchars($q['subject_name']) ?></td>
                                    <!-- DIFFICULTY LEVEL - Shows Easy/Medium/Hard -->
                                    <td style="text-transform: capitalize;"><?= $q['difficulty'] ?></td>
                                    <!-- CORRECT ANSWER - Which option is correct (A/B/C/D) -->
                                    <td><strong style="color: var(--success);"><?= $q['correct_answer'] ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <!-- EMPTY STATE - No questions created yet -->
                    <p style="text-align: center; color: var(--text-muted);">No questions added yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

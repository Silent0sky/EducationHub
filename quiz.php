<?php
/* 
   ============================================
   QUIZ INTERFACE - quiz.php
   ============================================
   Multi-state quiz system with three main states:
   1. Subject Selection - Choose which quiz to take
   2. Quiz Taking - Answer questions (10 random questions per quiz)
   3. Results Display - View score, percentage, detailed answer review
   
   Features:
   - Filter by year and semester before taking quiz
   - Randomized 10 questions per attempt
   - Real-time progress tracking
   - Score calculation and database saving
   - Detailed answer review with correct answers shown
*/

/* Include configuration and helper functions */
require_once 'config/functions.php';
/* Verify user is logged in before accessing quiz */
requireLogin();

/* PAGE TITLE FOR HEADER */
$pageTitle = 'Take Quiz';

/* 
   EXTRACT FILTER PARAMETERS FROM URL
   - subject: Selected subject ID for quiz
   - year: Filter subjects by academic year (FY/SY/TY)
   - semester: Filter by semester number (1-6)
*/
$subjectId = (int)($_GET['subject'] ?? 0);
$yearFilter = sanitize($_GET['year'] ?? '');
$semesterFilter = (int)($_GET['semester'] ?? 0);
/* submitted: Flag from POST indicating quiz form was submitted */
$submitted = isset($_POST['submit_quiz']);

/* 
   ============================================
   STATE 1: FETCH ALL SUBJECTS FOR SELECTION
   ============================================
   Get subjects with question counts for display
   Used in subject selection interface
*/
$subjectsSql = "SELECT s.*, 
                (SELECT COUNT(*) FROM questions WHERE subject_id = s.id) as question_count 
                FROM subjects s WHERE 1=1";
/* Apply year filter if specified */
if ($yearFilter) $subjectsSql .= " AND s.year = '$yearFilter'";
/* Apply semester filter if specified */
if ($semesterFilter) $subjectsSql .= " AND s.semester = $semesterFilter";
/* Sort by year and semester for better organization */
$subjectsSql .= " ORDER BY s.year, s.semester, s.name";
$subjects = $conn->query($subjectsSql);

/* 
   ============================================
   STATE 3: HANDLE QUIZ SUBMISSION & SCORING
   ============================================
   This block executes when:
   - User submits the quiz form (POST request)
   - All answers array exists
   - subject_id is provided
*/
if ($submitted && isset($_POST['answers']) && isset($_POST['subject_id'])) {
    /* Extract submitted data */
    $subjectId = (int)$_POST['subject_id'];
    $answers = $_POST['answers'];

    /* 
       FETCH CORRECT ANSWERS - Get all questions for this subject
       Need to know correct answers to compare with user responses
    */
    $questions = $conn->query("SELECT * FROM questions WHERE subject_id = $subjectId ORDER BY id");
    $totalQuestions = $questions->num_rows;
    $correctAnswers = 0;

    /* 
       COMPARE ANSWERS - Iterate through each question
       Check if user's answer matches the correct_answer from database
       Count total correct answers
    */
    $results = [];
    while ($q = $questions->fetch_assoc()) {
        /* Get the user's answer for this question (empty if not answered) */
        $userAnswer = $answers[$q['id']] ?? '';
        /* Compare user answer with correct answer (case-insensitive) */
        $isCorrect = strtoupper($userAnswer) === $q['correct_answer'];
        /* Increment counter if answer is correct */
        if ($isCorrect) $correctAnswers++;

        /* Store result for review page display later */
        $results[] = [
            'question' => $q,
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect
        ];
    }

    /* 
       CALCULATE PERCENTAGE SCORE
       Formula: (Correct Answers / Total Questions) * 100
       Rounded to 1 decimal place
    */
    $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0;

    /* 
       SAVE QUIZ RESULT TO DATABASE
       Store score record for performance tracking
       Used to show performance history to user/teacher
    */
    $userId = $_SESSION['user_id'];
    /* Prepared statement to prevent SQL injection */
    $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, subject_id, score, total_questions, percentage) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiid", $userId, $subjectId, $correctAnswers, $totalQuestions, $percentage);
    $stmt->execute();

    /* Get subject name for results display */
    $subjectResult = $conn->query("SELECT name FROM subjects WHERE id = $subjectId")->fetch_assoc();
    $subjectName = $subjectResult['name'] ?? 'Quiz';
}

/* 
   ============================================
   STATE 2: LOAD QUESTIONS FOR SELECTED SUBJECT
   ============================================
   This executes when:
   - subjectId is set from URL parameter
   - Form has NOT been submitted yet
   Loads 10 random questions from the selected subject
*/
$questions = null;
$subjectName = '';
if ($subjectId && !$submitted) {
    /* 
       FETCH RANDOM QUESTIONS
       SELECT: All question fields for quiz display
       WHERE: Match the selected subject
       ORDER BY RAND(): Randomize question order
       LIMIT 10: Show 10 questions per quiz attempt
    */
    $questions = $conn->query("SELECT * FROM questions WHERE subject_id = $subjectId ORDER BY RAND() LIMIT 10");
    /* Get subject name for display in header */
    $subjectResult = $conn->query("SELECT name FROM subjects WHERE id = $subjectId")->fetch_assoc();
    $subjectName = $subjectResult['name'] ?? 'Quiz';
}

/* 
   ICON MAPPING - Emoji icons for each subject category
   Maps subject icon codes to Unicode emojis for visual display
*/
$subjectIcons = [
    'code' => '💻', 'book-open' => '📖', 'briefcase' => '💼', 'monitor' => '🖥️',
    'database' => '🗄️', 'calculator' => '🧮', 'message-circle' => '💬', 'globe' => '🌐',
    'layers' => '📚', 'settings' => '⚙️', 'network' => '🌐', 'brain' => '🧠',
    'cloud' => '☁️', 'shield' => '🛡️', 'folder' => '📁'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Education Hub</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/quiz.css">
</head>
<body>
    <!-- MAIN LAYOUT - Sidebar + Main Content -->
    <div class="layout">
        <!-- SIDEBAR NAVIGATION - Imported from includes -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- MAIN CONTENT AREA - Right side of layout -->
        <main class="main-content">
            <!-- PAGE HEADER - Logo and user info -->
            <?php include 'includes/header.php'; ?>

            <!-- MAIN CONTENT SECTION - Dynamic based on quiz state -->
            <section>
                <?php if ($submitted && isset($results)): ?>
                <!-- 
                ============================================
                STATE 3: QUIZ RESULTS PAGE
                ============================================
                Displayed after user submits quiz
                Shows:
                - Overall score and percentage
                - Performance emoji/message
                - Detailed answer review
                -->
                <div class="quiz-results-container">
                    <div class="results-hero">
                        <!-- EMOJI DISPLAY - Changes based on score -->
                        <!-- Performance feedback: 🏆 (90%+), 🎉 (70-89%), 👍 (50-69%), 💪 (<50%) -->
                        <div class="results-emoji">
                            <?php 
                            if ($percentage >= 90) echo '🏆';
                            elseif ($percentage >= 70) echo '🎉';
                            elseif ($percentage >= 50) echo '👍';
                            else echo '💪';
                            ?>
                        </div>
                        
                        <!-- CIRCULAR SCORE DISPLAY - CSS conic-gradient progress circle -->
                        <!-- Shows percentage visually with animated circle -->
                        <div class="score-circle" style="--score: <?= $percentage ?>">
                            <div class="score-circle-bg"></div>
                            <div class="score-circle-inner">
                                <span class="score-value"><?= $percentage ?>%</span>
                                <span class="score-label">Score</span>
                            </div>
                        </div>
                        
                        <!-- PERFORMANCE MESSAGE - Dynamic based on score range -->
                        <h2 class="results-title">
                            <?php 
                            if ($percentage >= 90) echo '🌟 Excellent!';
                            elseif ($percentage >= 70) echo '🎉 Great Job!';
                            elseif ($percentage >= 50) echo '👍 Good Effort!';
                            else echo '💪 Keep Practicing!';
                            ?>
                        </h2>
                        
                        <!-- SUBJECT NAME - Which quiz was taken -->
                        <p class="results-subtitle"><?= htmlspecialchars($subjectName) ?></p>
                        
                        <!-- STATISTICS DISPLAY -->
                        <!-- Shows correct/wrong/total questions breakdown -->
                        <div class="results-stats">
                            <div class="result-stat">
                                <div class="result-stat-value value"><?= $correctAnswers ?></div>
                                <div class="result-stat-label label">✅ Correct</div>
                            </div>
                            <div class="result-stat">
                                <div class="result-stat-value value"><?= $totalQuestions - $correctAnswers ?></div>
                                <div class="result-stat-label label">❌ Wrong</div>
                            </div>
                            <div class="result-stat">
                                <div class="result-stat-value value"><?= $totalQuestions ?></div>
                                <div class="result-stat-label label">📝 Total</div>
                            </div>
                        </div>
                        
                        <!-- ACTION BUTTON - Take another quiz -->
                        <a href="quiz.php" class="btn btn-primary btn-lg">🎯 Take Another Quiz</a>
                    </div>

                    <!-- 
                    ============================================
                    ANSWER REVIEW SECTION
                    ============================================
                    Shows detailed review of each question:
                    - Question text
                    - User's answer vs Correct answer
                    - Visual indicator (green for correct, red for wrong)
                    -->
                    <h3 class="review-title">📝 Review Your Answers</h3>
                    <div class="review-list">
                        <?php foreach ($results as $i => $r): ?>
                        <!-- REVIEW CARD - One card per question -->
                        <div class="review-card <?= $r['is_correct'] ? 'correct' : 'wrong' ?>">
                            <!-- CARD HEADER - Question number and result status -->
                            <div class="review-header">
                                <span class="review-number">Q<?= $i + 1 ?></span>
                                <span class="review-status"><?= $r['is_correct'] ? '✅ Correct' : '❌ Wrong' ?></span>
                            </div>
                            
                            <!-- QUESTION TEXT - The quiz question asked -->
                            <p class="review-question"><?= htmlspecialchars($r['question']['question_text']) ?></p>
                            
                            <!-- ANSWER COMPARISON -->
                            <div class="review-answers">
                                <!-- Only show user's answer if it was wrong -->
                                <?php if (!$r['is_correct']): ?>
                                <div class="your-answer">Your answer: <?= $r['user_answer'] ?: 'Not answered' ?></div>
                                <?php endif; ?>
                                <!-- Always show correct answer for learning -->
                                <div class="correct-answer">Correct: <?= $r['question']['correct_answer'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php elseif ($questions && $questions->num_rows > 0): ?>
                <!-- 
                ============================================
                STATE 2: QUIZ TAKING PAGE
                ============================================
                Displayed when user has selected a subject
                Shows 10 random questions with multiple choice options
                Progress bar tracks answered questions
                -->
                <div class="quiz-header">
                    <!-- BACK LINK - Return to subject selection -->
                    <a href="quiz.php" class="back-link">← Back to Subjects</a>
                    <!-- QUIZ TITLE - Subject name being tested on -->
                    <h2>📝 <?= htmlspecialchars($subjectName) ?></h2>
                </div>

                <!-- QUIZ FORM - Submits answers to same page (POST) -->
                <form method="POST" id="quizForm">
                    <!-- HIDDEN FIELD - Subject ID needed for evaluation -->
                    <input type="hidden" name="subject_id" value="<?= $subjectId ?>">

                    <!-- 
                    ============================================
                    PROGRESS TRACKING SECTION
                    ============================================
                    Visual feedback showing:
                    - How many questions answered
                    - Total questions in quiz
                    - Progress bar fill percentage
                    -->
                    <div class="quiz-progress-container">
                        <div class="quiz-progress-header">
                            <span class="quiz-progress-title">Progress</span>
                            <span class="quiz-progress-count"><span id="answered">0</span> / <?= $questions->num_rows ?></span>
                        </div>
                        <!-- PROGRESS BAR - Fills as questions are answered -->
                        <!-- Updated via JavaScript on each radio button change -->
                        <div class="quiz-progress-bar">
                            <div class="quiz-progress-fill" id="progressFill" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- 
                    ============================================
                    QUESTION CARDS - One per question
                    ============================================
                    Each card contains:
                    - Question number badge
                    - Question text
                    - 4 multiple choice options (A, B, C, D)
                    -->
                    <?php $qNum = 0; while ($q = $questions->fetch_assoc()): $qNum++; ?>
                    <div class="quiz-question-card">
                        <!-- QUESTION HEADER - Question number display -->
                        <div class="question-header">
                            <span class="question-badge">Question <?= $qNum ?></span>
                        </div>
                        
                        <!-- QUESTION TEXT - The inquiry being asked -->
                        <p class="quiz-question-text"><?= htmlspecialchars($q['question_text']) ?></p>
                        
                        <!-- OPTIONS GRID - 4-option multiple choice layout -->
                        <div class="quiz-options-grid">
                            <?php foreach (['A', 'B', 'C', 'D'] as $opt): 
                                /* Build option field name (option_a, option_b, etc.) */
                                $optKey = 'option_' . strtolower($opt);
                            ?>
                            <!-- OPTION LABEL - Radio button with styled display -->
                            <label class="quiz-option">
                                <!-- RADIO INPUT - Tracks selected answer for this question -->
                                <!-- name array stores answers by question ID -->
                                <!-- onchange updates progress bar when option selected -->
                                <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt ?>" onchange="updateProgress()">
                                <!-- OPTION INDICATOR - Label shows A/B/C/D -->
                                <span class="option-indicator"><?= $opt ?></span>
                                <!-- OPTION TEXT - The actual answer choice text -->
                                <span class="option-text"><?= htmlspecialchars($q[$optKey]) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <!-- 
                    ============================================
                    SUBMIT SECTION - Quiz submission button
                    ============================================ -->
                    <div class="quiz-submit-section">
                        <!-- SUBMIT BUTTON - Send answers for evaluation -->
                        <!-- Triggers POST request with submit_quiz flag -->
                        <button type="submit" name="submit_quiz" class="quiz-submit-btn">✨ Submit Quiz</button>
                    </div>
                </form>

                <!-- 
                ============================================
                PROGRESS BAR JAVASCRIPT
                ============================================
                Updates progress bar in real-time as user selects answers
                Shows visual feedback of quiz completion
                -->
                <script>
                    const totalQuestions = <?= $qNum ?>;
                    
                    /* updateProgress: Called when user selects an answer */
                    function updateProgress() {
                        /* Count how many radio buttons are currently checked */
                        const answered = document.querySelectorAll('input[type="radio"]:checked').length;
                        /* Update the answered counter display */
                        document.getElementById('answered').textContent = answered;
                        /* Calculate and update progress bar width percentage */
                        document.getElementById('progressFill').style.width = (answered / totalQuestions * 100) + '%';
                    }
                </script>

                <?php else: ?>
                <!-- 
                ============================================
                STATE 1: SUBJECT SELECTION PAGE
                ============================================
                Initial state when no subject selected
                Allows user to filter and choose quiz subject
                -->
                <div class="quiz-hero">
                    <!-- PAGE TITLE -->
                    <h1>🎯 Take a Quiz</h1>
                    <p>Test your knowledge and track your progress</p>
                </div>

                <!-- 
                ============================================
                YEAR FILTER TABS
                ============================================
                Filter subjects by academic year:
                - All Years (show all)
                - FY (First Year)
                - SY (Second Year)
                - TY (Third Year)
                -->
                <div class="year-tabs">
                    <a href="?year=" class="year-tab <?= empty($yearFilter) ? 'active' : '' ?>">All Years</a>
                    <a href="?year=FY" class="year-tab <?= $yearFilter === 'FY' ? 'active' : '' ?>"><span class="year-badge fy">FY</span> First Year</a>
                    <a href="?year=SY" class="year-tab <?= $yearFilter === 'SY' ? 'active' : '' ?>"><span class="year-badge sy">SY</span> Second Year</a>
                    <a href="?year=TY" class="year-tab <?= $yearFilter === 'TY' ? 'active' : '' ?>"><span class="year-badge ty">TY</span> Third Year</a>
                </div>

                <!-- 
                ============================================
                SEMESTER FILTER TABS
                ============================================
                Shows semester options for selected year
                - FY: Semesters 1 & 2
                - SY: Semesters 3 & 4
                - TY: Semesters 5 & 6
                Only displayed when year is selected
                -->
                <?php if ($yearFilter): ?>
                <div class="semester-tabs">
                    <?php $semesters = ['FY' => [1, 2], 'SY' => [3, 4], 'TY' => [5, 6]]; $availableSems = $semesters[$yearFilter] ?? []; ?>
                    <a href="?year=<?= $yearFilter ?>" class="semester-tab <?= !$semesterFilter ? 'active' : '' ?>">All Semesters</a>
                    <?php foreach ($availableSems as $sem): ?>
                    <a href="?year=<?= $yearFilter ?>&semester=<?= $sem ?>" class="semester-tab <?= $semesterFilter == $sem ? 'active' : '' ?>">Semester <?= $sem ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- 
                ============================================
                SUBJECT CARDS GRID
                ============================================
                Displays available subjects as clickable cards
                Shows:
                - Subject icon (emoji)
                - Year and semester info
                - Subject name
                - Number of questions available
                - "No questions yet" for disabled subjects
                -->
                <div class="subject-selection-grid">
                    <?php if ($subjects->num_rows > 0): ?>
                        <?php while ($subject = $subjects->fetch_assoc()): 
                            /* Get icon emoji for this subject */
                            $icon = $subjectIcons[$subject['icon']] ?? '📚';
                            /* Check if questions exist for this subject (enables/disables card) */
                            $hasQuestions = $subject['question_count'] > 0;
                        ?>
                        <!-- SUBJECT CARD - Clickable to start quiz or disabled if no questions -->
                        <a href="<?= $hasQuestions ? 'quiz.php?subject=' . $subject['id'] : '#' ?>" 
                           class="quiz-subject-card <?= !$hasQuestions ? 'disabled' : '' ?>"
                           style="--card-gradient: <?= $subject['color'] ?>">
                            <!-- ICON DISPLAY - Color-coded by subject -->
                            <div class="subject-card-icon" style="background: <?= $subject['color'] ?>20; color: <?= $subject['color'] ?>;"><?= $icon ?></div>
                            <!-- YEAR AND SEMESTER INFO -->
                            <div class="subject-card-year"><?= $subject['year'] ?> - Sem <?= $subject['semester'] ?></div>
                            <!-- SUBJECT NAME/TITLE -->
                            <h3 class="subject-card-title"><?= htmlspecialchars($subject['name']) ?></h3>
                            <!-- STATISTICS - Question count badge -->
                            <div class="subject-card-stats"><span class="stat-badge">📝 <?= $subject['question_count'] ?> Questions</span></div>
                            <!-- VISUAL INDICATOR -->
                            <?php if ($hasQuestions): ?>
                            <!-- CLICKABLE ARROW - Indicates this subject is available -->
                            <div class="subject-card-arrow">→</div>
                            <?php else: ?>
                            <!-- DISABLED BADGE - No questions available yet -->
                            <div class="no-questions-badge">No questions yet</div>
                            <?php endif; ?>
                        </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- EMPTY STATE - No subjects found for selected filters -->
                        <div class="empty-state"><div class="empty-icon">📭</div><h3>No subjects found</h3><p>Try selecting a different year or semester</p></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>

<?php
/**
 * Education Hub - Quiz Page
 */

require_once 'config/functions.php';
requireLogin();

$pageTitle = 'Take Quiz';
$subjectId = (int)($_GET['subject'] ?? 0);
$submitted = isset($_POST['submit_quiz']);

// Get subjects for selection
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");

// Handle quiz submission
if ($submitted && isset($_POST['answers']) && isset($_POST['subject_id'])) {
    $subjectId = (int)$_POST['subject_id'];
    $answers = $_POST['answers'];
    
    // Get questions and calculate score
    $questions = $conn->query("SELECT * FROM questions WHERE subject_id = $subjectId ORDER BY id");
    $totalQuestions = $questions->num_rows;
    $correctAnswers = 0;
    
    $results = [];
    while ($q = $questions->fetch_assoc()) {
        $userAnswer = $answers[$q['id']] ?? '';
        $isCorrect = strtoupper($userAnswer) === $q['correct_answer'];
        if ($isCorrect) $correctAnswers++;
        
        $results[] = [
            'question' => $q,
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect
        ];
    }
    
    $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0;
    
    // Save result
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, subject_id, score, total_questions, percentage) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiid", $userId, $subjectId, $correctAnswers, $totalQuestions, $percentage);
    $stmt->execute();
}

// Get questions for selected subject
$questions = null;
if ($subjectId && !$submitted) {
    $questions = $conn->query("SELECT * FROM questions WHERE subject_id = $subjectId ORDER BY RAND() LIMIT 10");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Education Hub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <section class="quiz-container">
                <?php if ($submitted): ?>
                    <!-- Quiz Results -->
                    <div class="card result-card">
                        <div class="result-score"><?= $percentage ?>%</div>
                        <h2 class="result-message">
                            <?php
                            if ($percentage >= 80) echo "🎉 Excellent!";
                            elseif ($percentage >= 60) echo "👍 Good Job!";
                            elseif ($percentage >= 40) echo "📚 Keep Practicing!";
                            else echo "💪 Don't Give Up!";
                            ?>
                        </h2>
                        <p class="result-details">
                            You scored <?= $correctAnswers ?> out of <?= $totalQuestions ?> questions correctly
                        </p>
                        <div style="display: flex; gap: 16px; justify-content: center;">
                            <a href="quiz.php" class="btn btn-primary">Take Another Quiz</a>
                            <a href="performance.php" class="btn btn-secondary">View Performance</a>
                        </div>
                    </div>
                    
                    <!-- Review Answers -->
                    <h3 style="margin: 32px 0 24px;">Review Your Answers</h3>
                    <?php foreach ($results as $index => $result): ?>
                    <div class="question-card">
                        <div class="question-number">Question <?= $index + 1 ?></div>
                        <div class="question-text"><?= htmlspecialchars($result['question']['question_text']) ?></div>
                        <div class="options-list">
                            <?php
                            $options = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
                            foreach ($options as $letter => $field):
                                $isUserAnswer = strtoupper($result['user_answer']) === $letter;
                                $isCorrect = $result['question']['correct_answer'] === $letter;
                                $class = '';
                                if ($isCorrect) $class = 'correct';
                                elseif ($isUserAnswer && !$isCorrect) $class = 'wrong';
                            ?>
                            <div class="option <?= $class ?>">
                                <strong><?= $letter ?>.</strong>
                                <?= htmlspecialchars($result['question'][$field]) ?>
                                <?php if ($isCorrect): ?> ✓<?php endif; ?>
                                <?php if ($isUserAnswer && !$isCorrect): ?> ✗<?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                <?php elseif ($subjectId && $questions && $questions->num_rows > 0): ?>
                    <!-- Quiz Questions -->
                    <form method="POST">
                        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                        
                        <?php $qNum = 0; while ($q = $questions->fetch_assoc()): $qNum++; ?>
                        <div class="question-card">
                            <div class="question-number">Question <?= $qNum ?> of <?= $questions->num_rows ?></div>
                            <div class="question-text"><?= htmlspecialchars($q['question_text']) ?></div>
                            <div class="options-list">
                                <?php
                                $options = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
                                foreach ($options as $letter => $field):
                                ?>
                                <label class="option">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $letter ?>" required>
                                    <strong><?= $letter ?>.</strong>
                                    <?= htmlspecialchars($q[$field]) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        
                        <button type="submit" name="submit_quiz" class="btn btn-primary btn-block">
                            Submit Quiz
                        </button>
                    </form>
                    
                <?php else: ?>
                    <!-- Subject Selection -->
                    <div class="card">
                        <h2 style="margin-bottom: 24px;">Select a Subject to Start Quiz</h2>
                        <div class="subjects-grid">
                            <?php while ($subject = $subjects->fetch_assoc()): 
                                // Count questions
                                $qCount = $conn->query("SELECT COUNT(*) as c FROM questions WHERE subject_id = " . $subject['id'])->fetch_assoc()['c'];
                            ?>
                            <a href="quiz.php?subject=<?= $subject['id'] ?>" class="subject-card" style="text-decoration: none; color: inherit;">
                                <div class="subject-header">
                                    <div class="subject-icon" style="background: <?= $subject['color'] ?>20; color: <?= $subject['color'] ?>;">
                                        📚
                                    </div>
                                    <h3 class="subject-name"><?= htmlspecialchars($subject['name']) ?></h3>
                                </div>
                                <p class="subject-desc"><?= $qCount ?> questions available</p>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>

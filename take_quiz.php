<?php
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header("Location: dashboard.php");
    exit;
}

// Check if quiz already attempted
$stmt = $pdo->prepare("SELECT COUNT(*) as attempted FROM user_responses WHERE user_id = ? AND quiz_id = ?");
$stmt->execute([$_SESSION['user_id'], $quiz_id]);
if ($stmt->fetchColumn() > 0) {
    header("Location: quiz_result.php?quiz_id=$quiz_id");
    exit;
}

// Fetch all questions
$stmt = $pdo->prepare("SELECT id, question_text FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
$total_questions = count($questions);

if ($total_questions === 0) {
    $error = "This quiz has no questions.";
}

// Initialize session answers array
if (!isset($_SESSION['quiz_answers'][$quiz_id])) {
    $_SESSION['quiz_answers'][$quiz_id] = [];
}

$question_index = isset($_GET['question_index']) ? (int)$_GET['question_index'] : 0;
if ($question_index < 0 || $question_index >= $total_questions) {
    $question_index = 0;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['answer_id']) && isset($_POST['question_id'])) {
        $question_id = (int)$_POST['question_id'];
        $answer_id = (int)$_POST['answer_id'];

        // Verify question belongs to quiz
        $stmt = $pdo->prepare("SELECT id FROM questions WHERE id = ? AND quiz_id = ?");
        $stmt->execute([$question_id, $quiz_id]);
        if ($stmt->fetch()) {
            // Store answer in session
            $_SESSION['quiz_answers'][$quiz_id][$question_id] = $answer_id;
        }
    }

    if (isset($_POST['submit_quiz'])) {
        if (count($_SESSION['quiz_answers'][$quiz_id]) < $total_questions) {
            $error = "Please answer all questions.";
        } else {
            $score = 0;
            foreach ($_SESSION['quiz_answers'][$quiz_id] as $question_id => $answer_id) {
                $stmt = $pdo->prepare("SELECT is_correct FROM answers WHERE id = ? AND question_id = ?");
                $stmt->execute([$answer_id, $question_id]);
                $is_correct = $stmt->fetchColumn();

                if ($is_correct) {
                    $score++;
                }

                $stmt = $pdo->prepare("INSERT INTO user_responses (user_id, question_id, answer_id, quiz_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $question_id, $answer_id, $quiz_id]);
            }

            $stmt = $pdo->prepare("INSERT INTO results (user_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$_SESSION['user_id'], $quiz_id, $score, $total_questions]);
                unset($_SESSION['quiz_answers'][$quiz_id]);
                header("Location: quiz_result.php?quiz_id=$quiz_id");
                exit;
            } catch (PDOException $e) {
                $error = "Failed to save results: " . $e->getMessage();
            }
        }
    } else {
        // Navigate to next question
        $next_index = $question_index + 1;
        if ($next_index < $total_questions) {
            header("Location: take_quiz.php?quiz_id=$quiz_id&question_index=$next_index");
            exit;
        }
    }
}

// Fetch current question and answers
if ($total_questions > 0) {
    $current_question = $questions[$question_index];
    $stmt = $pdo->prepare("SELECT id, answer_text FROM answers WHERE question_id = ? ORDER BY id");
    $stmt->execute([$current_question['id']]);
    $answers = $stmt->fetchAll();
}
?>

<h2><?php echo htmlspecialchars($quiz['title']); ?> (Question <?php echo $question_index + 1; ?> of <?php echo $total_questions; ?>)</h2>
<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>
<?php if ($total_questions === 0): ?>
    <p>No questions available for this quiz.</p>
    <a href="dashboard.php">Back to Dashboard</a>
<?php else: ?>
    <form method="POST">
        <div class="question">
            <h4><?php echo htmlspecialchars($current_question['question_text']); ?></h4>
            <?php foreach ($answers as $answer): ?>
                <label>
                    <input type="radio" name="answer_id" value="<?php echo $answer['id']; ?>" <?php echo isset($_SESSION['quiz_answers'][$quiz_id][$current_question['id']]) && $_SESSION['quiz_answers'][$quiz_id][$current_question['id']] == $answer['id'] ? 'checked' : ''; ?> required>
                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                </label><br>
            <?php endforeach; ?>
            <input type="hidden" name="question_id" value="<?php echo $current_question['id']; ?>">
        </div>
        <div class="navigation">
            <?php if ($question_index > 0): ?>
                <button type="button" onclick="window.location.href='take_quiz.php?quiz_id=<?php echo $quiz_id; ?>&question_index=<?php echo $question_index - 1; ?>'">Previous</button>
            <?php endif; ?>
            <?php if ($question_index < $total_questions - 1): ?>
                <button type="submit">Next</button>
            <?php else: ?>
                <button type="submit" name="submit_quiz" value="1">Submit Quiz</button>
            <?php endif; ?>
        </div>
    </form>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
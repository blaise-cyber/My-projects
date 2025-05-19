<?php
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch total score and total questions
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(score), 0) as total_score, COALESCE(SUM(total_questions), 0) as total_questions
    FROM results
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$total_result = $stmt->fetch();
$total_score = $total_result['total_score'];
$total_questions = $total_result['total_questions'];

// Fetch available quizzes (not attempted)
$stmt = $pdo->prepare("
    SELECT q.id, q.title, q.description
    FROM quizzes q
    LEFT JOIN results r ON q.id = r.quiz_id AND r.user_id = ?
    WHERE r.id IS NULL
");
$stmt->execute([$_SESSION['user_id']]);
$available_quizzes = $stmt->fetchAll();

// Fetch completed quizzes
$stmt = $pdo->prepare("
    SELECT q.id, q.title, q.description, r.score, r.total_questions
    FROM quizzes q
    JOIN results r ON q.id = r.quiz_id
    WHERE r.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$completed_quizzes = $stmt->fetchAll();
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?>!</h2>

<h3>Total Score</h3>
<p>
    <?php if ($total_questions > 0): ?>
        Total Score: <?php echo $total_score; ?> / <?php echo $total_questions; ?>
    <?php else: ?>
        You haven't completed any quizzes yet.
    <?php endif; ?>
</p>

<h3>Available Quizzes</h3>
<div class="quiz-list">
    <?php if (empty($available_quizzes)): ?>
        <p>No quizzes available.</p>
    <?php else: ?>
        <?php foreach ($available_quizzes as $quiz): ?>
            <div>
                <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                <a href="take_quiz.php?quiz_id=<?php echo $quiz['id']; ?>">Take Quiz</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<h3>Task Done</h3>
<div class="quiz-list">
    <?php if (empty($completed_quizzes)): ?>
        <p>No quizzes completed.</p>
    <?php else: ?>
        <?php foreach ($completed_quizzes as $quiz): ?>
            <div>
                <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                <p>Score: <?php echo $quiz['score']; ?> / <?php echo $quiz['total_questions']; ?></p>
                <a href="quiz_result.php?quiz_id=<?php echo $quiz['id']; ?>">View Results</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
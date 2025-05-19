<?php
require_once 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, title, description FROM quizzes");
$quizzes = $stmt->fetchAll();
?>

<h2>Admin Panel</h2>
<a href="manage_quiz.php">Create New Quiz</a>
<h3>Manage Quizzes</h3>
<div class="quiz-list">
    <?php if (empty($quizzes)): ?>
        <p>No quizzes available.</p>
    <?php else: ?>
        <?php foreach ($quizzes as $quiz): ?>
            <div>
                <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                <p><?php echo htmlspecialchars($quiz['description'] ?: 'No description'); ?></p>
                <a href="manage_quiz.php?id=<?php echo $quiz['id']; ?>">Edit</a> |
                <a href="manage_questions.php?quiz_id=<?php echo $quiz['id']; ?>">Manage Questions</a> |
                <a href="manage_quiz.php?delete=<?php echo $quiz['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
<?php
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET['quiz_id'];
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT score, total_questions FROM results WHERE user_id = ? AND quiz_id = ?");
$stmt->execute([$_SESSION['user_id'], $quiz_id]);
$result = $stmt->fetch();

if (!$result) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT q.id as question_id, q.question_text, a.id as answer_id, a.answer_text, a.is_correct, ur.answer_id as user_answer_id
    FROM questions q
    LEFT JOIN answers a ON q.id = a.question_id
    LEFT JOIN user_responses ur ON q.id = ur.question_id AND ur.user_id = ? AND ur.quiz_id = ?
    WHERE q.quiz_id = ?
    ORDER BY q.id, a.id
");
$stmt->execute([$_SESSION['user_id'], $quiz_id, $quiz_id]);
$results = $stmt->fetchAll();

$questions = [];
foreach ($results as $row) {
    if (!isset($questions[$row['question_id']])) {
        $questions[$row['question_id']] = [
            'question_text' => $row['question_text'],
            'answers' => [],
            'user_answer_id' => $row['user_answer_id']
        ];
    }
    $questions[$row['question_id']]['answers'][] = [
        'id' => $row['answer_id'],
        'text' => $row['answer_text'],
        'is_correct' => $row['is_correct']
    ];
}
?>

<h2>Quiz Results: <?php echo htmlspecialchars($quiz['title']); ?></h2>
<p>Your Score: <?php echo $result['score']; ?> / <?php echo $result['total_questions']; ?></p>
<h3>Answer Breakdown</h3>
<?php foreach ($questions as $q): ?>
    <div class="question">
        <h4><?php echo htmlspecialchars($q['question_text']); ?></h4>
        <?php foreach ($q['answers'] as $a): ?>
            <p>
                <?php echo htmlspecialchars($a['text']); ?>
                <?php if ($a['id'] == $q['user_answer_id']): ?>
                    (Your Answer)
                    <?php if ($a['is_correct']): ?>
                        <span style="color: green;">✓ Correct</span>
                    <?php else: ?>
                        <span style="color: red;">✗ Incorrect</span>
                    <?php endif; ?>
                <?php elseif ($a['is_correct']): ?>
                    <span style="color: green;">(Correct Answer)</span>
                <?php endif; ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
<a href="dashboard.php">Back to Dashboard</a>

<?php require_once 'footer.php'; ?>
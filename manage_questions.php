<?php
require_once 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET['quiz_id'];
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header("Location: admin_index.php");
    exit;
}

if (isset($_GET['delete'])) {
    $question_id = (int)$_GET['delete'];

    try {
        $pdo->beginTransaction();

        // Step 1: Get all answer IDs for the question
        $stmt = $pdo->prepare("SELECT id FROM answers WHERE question_id = ?");
        $stmt->execute([$question_id]);
        $answer_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Step 2: Delete user_responses tied to these answer IDs
        if (!empty($answer_ids)) {
            $placeholders = implode(',', array_fill(0, count($answer_ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM user_responses WHERE answer_id IN ($placeholders)");
            $stmt->execute($answer_ids);
        }

        // Step 3: Delete the question (answers will be deleted automatically due to ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$question_id]);

        $pdo->commit();
        header("Location: manage_questions.php?quiz_id=$quiz_id");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to delete question: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = sanitize($_POST['question_text']);
    $answers = $_POST['answers'];
    $correct_answer = (int)$_POST['correct_answer'];

    if (empty($question_text) || count(array_filter($answers)) < 2) {
        $error = "Question text and at least 2 answers are required.";
    } else {
        if (isset($_POST['question_id'])) {
            $stmt = $pdo->prepare("UPDATE questions SET question_text = ? WHERE id = ?");
            $stmt->execute([$question_text, $_POST['question_id']]);
            $question_id = $_POST['question_id'];

            $stmt = $pdo->prepare("DELETE FROM answers WHERE question_id = ?");
            $stmt->execute([$question_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)");
            $stmt->execute([$quiz_id, $question_text]);
            $question_id = $pdo->lastInsertId();
        }

        foreach ($answers as $index => $answer_text) {
            if (!empty($answer_text)) {
                $answer_text = sanitize($answer_text);
                $is_correct = ($index == $correct_answer) ? 1 : 0;
                $stmt = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                $stmt->execute([$question_id, $answer_text, $is_correct]);
            }
        }
        header("Location: manage_questions.php?quiz_id=$quiz_id");
        exit;
    }
}

$question = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $question = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM answers WHERE question_id = ?");
    $stmt->execute([$_GET['edit']]);
    $question['answers'] = $stmt->fetchAll();
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
?>

<h2>Manage Questions for <?php echo htmlspecialchars($quiz['title']); ?></h2>
<h3>Add/Edit Question</h3>
<?php if (isset($error)): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<form method="POST">
    <?php if ($question): ?>
        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
    <?php endif; ?>
    <label>Question Text</label>
    <textarea name="question_text" required><?php echo $question ? htmlspecialchars($question['question_text']) : ''; ?></textarea>
    <label>Answers (select the correct one)</label>
    <?php for ($i = 0; $i < 4; $i++): ?>
        <div>
            <input type="text" name="answers[]" value="<?php echo $question && isset($question['answers'][$i]) ? htmlspecialchars($question['answers'][$i]['answer_text']) : ''; ?>" <?php echo $i < 2 ? 'required' : ''; ?>>
            <input type="radio" name="correct_answer" value="<?php echo $i; ?>" <?php echo $question && isset($question['answers'][$i]) && $question['answers'][$i]['is_correct'] ? 'checked' : ($i == 0 && !$question ? 'checked' : ''); ?>>
        </div>
    <?php endfor; ?>
    <button type="submit">Save Question</button>
</form>

<h3>Questions</h3>
<div class="question-list">
    <?php if (empty($questions)): ?>
        <p>No questions available.</p>
    <?php else: ?>
        <?php foreach ($questions as $q): ?>
            <div>
                <p><?php echo htmlspecialchars($q['question_text']); ?></p>
                <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>&edit=<?php echo $q['id']; ?>">Edit</a> |
                <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>&delete=<?php echo $q['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
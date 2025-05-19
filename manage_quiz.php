<?php
require_once 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$quiz = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $quiz = $stmt->fetch();
}

if (isset($_GET['delete'])) {
    $quiz_id = $_GET['delete'];
    // Delete dependent records in user_responses
    $stmt = $pdo->prepare("DELETE FROM user_responses WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    // Delete dependent records in results
    $stmt = $pdo->prepare("DELETE FROM results WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    // Now delete the quiz (questions and answers will be deleted automatically due to ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$quiz_id]);
    header("Location: admin_index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        if (isset($_POST['id'])) {
            $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $description, $_POST['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO quizzes (title, description) VALUES (?, ?)");
            $stmt->execute([$title, $description]);
        }
        header("Location: admin_index.php");
        exit;
    }
}
?>

<h2><?php echo $quiz ? 'Edit Quiz' : 'Create Quiz'; ?></h2>
<?php if (isset($error)): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>
<form method="POST">
    <?php if ($quiz): ?>
        <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
    <?php endif; ?>
    <label>Title</label>
    <input type="text" name="title" value="<?php echo $quiz ? htmlspecialchars($quiz['title']) : ''; ?>" required>
    <label>Description</label>
    <textarea name="description"><?php echo $quiz ? htmlspecialchars($quiz['description']) : ''; ?></textarea>
    <button type="submit">Save</button>
</form>

<?php require_once 'footer.php'; ?>
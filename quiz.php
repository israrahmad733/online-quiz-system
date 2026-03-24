<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get all published quizzes
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE is_published = TRUE");
$stmt->execute();
$quizzes = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<h1>Wellcome <?php echo $_SESSION['username'];?></h1>
<h2 class="mb-4">Choose your Quiz</h2>

<?php if(count($quizzes) > 0): ?>
    <div class="row">
        <?php foreach($quizzes as $quiz): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($quiz['description']); ?></p>
                        <p class="text-muted">Time limit: <?php echo $quiz['time_limit']; ?> minutes</p>
                    </div>
                    <div class="card-footer">
                        <a href="take_quaiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Start Quiz</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">No quizzes available at the moment.</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
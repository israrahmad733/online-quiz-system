<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $quiz_id = intval($_GET['delete']);
    
    try {
        $conn->beginTransaction();
        
        // Delete related records
        $stmt = $conn->prepare("DELETE FROM user_answers WHERE result_id IN (SELECT id FROM results WHERE quiz_id = ?)");
        $stmt->execute([$quiz_id]);
        
        $stmt = $conn->prepare("DELETE FROM results WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);
        
        $stmt = $conn->prepare("DELETE FROM options WHERE question_id IN (SELECT id FROM questions WHERE quiz_id = ?)");
        $stmt->execute([$quiz_id]);
        
        $stmt = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);
        
        $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt->execute([$quiz_id]);
        
        $conn->commit();
        $_SESSION['success'] = "Quiz deleted successfully!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error deleting quiz: " . $e->getMessage();
    }
}

// Toggle publish status
if (isset($_GET['toggle_publish'])) {
    $quiz_id = intval($_GET['toggle_publish']);
    
    $stmt = $conn->prepare("UPDATE quizzes SET is_published = NOT is_published WHERE id = ?");
    $stmt->execute([$quiz_id]);
    $_SESSION['success'] = "Publish status updated!";
}

// Get all quizzes
$search = $_GET['search'] ?? '';
$query = "SELECT q.*, COUNT(qu.id) as question_count 
          FROM quizzes q 
          LEFT JOIN questions qu ON q.id = qu.quiz_id 
          WHERE q.title LIKE ? 
          GROUP BY q.id 
          ORDER BY q.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute(["%$search%"]);
$quizzes = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Manage Quizzes</h2>
    
    <?php include '../includes/messages.php'; ?>
    
    <!-- Search and Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search by title..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quizzes Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Questions</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizzes as $quiz): ?>
                        <tr>
                            <td><?= htmlspecialchars($quiz['title']) ?></td>
                            <td><?= $quiz['question_count'] ?></td>
                            <td>
                                <span class="badge <?= $quiz['is_published'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $quiz['is_published'] ? 'Published' : 'Draft' ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($quiz['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="edit_quiz">
                                       <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?toggle_publish=<?= $quiz['id'] ?>" 
                                       class="btn btn-sm <?= $quiz['is_published'] ? 'btn-warning' : 'btn-success' ?>"
                                       title="publish_quiz">
                                       <i class="fas <?= $quiz['is_published'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                    </a>
                                    <a href="delete_quiz.php?id=<?= $quiz['id'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure? This cannot be undone!')" title="delete_quiz">
                                       <i class="fas fa-trash"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $quiz['id'] ?>)" 
                                            class="btn btn-sm btn-outline-danger">
                                    </button> 
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($quizzes)): ?>
                <div class="alert alert-info">No quizzes found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(quizId) {
    if (confirm('Are you sure you want to delete this quiz? All related data will be permanently removed!')) {
        window.location = ?delete=${quizId};
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
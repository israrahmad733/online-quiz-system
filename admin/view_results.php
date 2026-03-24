<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

// Handle result deletion
if (isset($_GET['delete'])) {
    $result_id = intval($_GET['delete']);
    
    try {
        $conn->beginTransaction();
        
        // Delete user answers first
        $stmt = $conn->prepare("DELETE FROM user_answers WHERE result_id = ?");
        $stmt->execute([$result_id]);
        
        // Delete result
        $stmt = $conn->prepare("DELETE FROM results WHERE id = ?");
        $stmt->execute([$result_id]);
        
        $conn->commit();
        $_SESSION['success'] = "Result deleted successfully!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error deleting result: " . $e->getMessage();
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$user_filter = $_GET['user'] ?? '';
$quiz_filter = $_GET['quiz'] ?? '';

// Base query
$query = "SELECT r.*, u.username, q.title AS quiz_title 
          FROM results r
          JOIN users u ON r.user_id = u.id
          JOIN quizzes q ON r.quiz_id = q.id
          WHERE 1=1";

$params = [];

// Add filters
if (!empty($search)) {
    $query .= " AND (u.username LIKE ? OR q.title LIKE ?)";
    array_push($params, "%$search%", "%$search%");
}

if (!empty($user_filter)) {
    $query .= " AND u.username = ?";
    $params[] = $user_filter;
}

if (!empty($quiz_filter)) {
    $query .= " AND q.title = ?";
    $params[] = $quiz_filter;
}

$query .= " ORDER BY r.completed_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Get unique users and quizzes for filters
$users = $conn->query("SELECT DISTINCT username FROM users")->fetchAll();
$quizzes = $conn->query("SELECT DISTINCT title FROM quizzes")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Quiz Results Overview</h2>
    
    <?php include '../includes/messages.php'; ?>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search user or quiz..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="user" class="form-select">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['username'] ?>" <?= $user_filter === $user['username'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="quiz" class="form-select">
                            <option value="">All Quizzes</option>
                            <?php foreach ($quizzes as $quiz): ?>
                                <option value="<?= $quiz['title'] ?>" <?= $quiz_filter === $quiz['title'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($quiz['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Date Taken</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['username']) ?></td>
                            <td><?= htmlspecialchars($result['quiz_title']) ?></td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar <?= ($result['score']/$result['total_questions'] >= 0.7) ? 'bg-success' : 'bg-warning' ?>" 
                                         role="progressbar" 
                                         style="width: <?= ($result['score']/$result['total_questions'])*100 ?>%">
                                        <?= $result['score'] ?> / <?= $result['total_questions'] ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= date('M j, Y g:i A', strtotime($result['completed_at'])) ?>
                            </td>
                            <td>
                                <?= gmdate("H:i:s", strtotime($result['completed_at']) - strtotime($result['started_at'])) ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="../results.php?id=<?= $result['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="View Details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $result['id'] ?>)" 
                                            class="btn btn-sm btn-outline-danger" 
                                            title="Delete Result">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($results)): ?>
                    <div class="alert alert-info">No results found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(resultId) {
    if (confirm('Are you sure you want to delete this result record?')) {
        window.location = ?delete=${resultId};
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
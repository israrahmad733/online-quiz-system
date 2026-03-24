<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

// Get stats
$stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch()['total_users'];

$stmt = $conn->query("SELECT COUNT(*) as total_quizzes FROM quizzes");
$total_quizzes = $stmt->fetch()['total_quizzes'];

$stmt = $conn->query("SELECT COUNT(*) as total_results FROM results");
$total_results = $stmt->fetch()['total_results'];

require_once '../includes/header.php';
?>

<div class="admin-dashboard">
    <h2 class="mb-4">Admin Dashboard</h2>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo $total_users; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Quizzes</h5>
                    <p class="card-text display-4"><?php echo $total_quizzes; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Quiz Attempts</h5>
                    <p class="card-text display-4"><?php echo $total_results; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2 d-md-block">
                <a href="add_new_quiz.php" class="btn btn-primary me-md-2">Add New Quiz</a>
                <a href="manage_quizzes.php" class="btn btn-secondary me-md-2">Manage Quizzes</a>
                <a href="view_results.php" class="btn btn-success">View All Results</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
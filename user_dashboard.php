<?php
//session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Redirect if not logged in or if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] == 1) {
    header("Location: login.php");
    exit();
}

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
        
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">User Dashboard</h4>
                </div>
                <div class="card-body">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Member Since:</strong> <?php echo date('d-M-Y', strtotime($user['created_at'])); ?></p>

                    <hr>

                    <h5>Quick Links</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><a href="take_quaiz.php">Start a Quiz</a></li>
<!--                         <li class="list-group-item"><a href="view_results.php">View My Results</a></li>
                        <li class="list-group-item"><a href="edit_profile.php">Edit My Profile</a></li> -->
                        <li class="list-group-item"><a href="logout.php" class="text-danger">Logout</a></li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Redirect logged-in users to quizzes page
if (isset($_SESSION['user_id'])) {
    header("Location: quiz.php");
    exit();
}

require_once 'includes/header.php';
?>

<div class="container text-center mt-5">
    <!-- Hero Section -->
    <div class="hero-section p-5 bg-light rounded-3">
        <h1 class="display-4 mb-4">Welcome to QuizMaster!</h1>
        <p class="lead mb-4">Test your knowledge across various topics and compete with others.</p>
        
        <!-- Call to Action Buttons -->
        <div class="d-grid gap-3 d-md-block">
            <a href="login.php" class="btn btn-primary btn-lg px-4 me-md-2">Login</a>
            <a href="register.php" class="btn btn-outline-secondary btn-lg px-4">Register</a>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">📚 Multiple Categories</h5>
                    <p class="card-text">Choose from technology, science, history, and more!</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">⏱ Real-time Results</h5>
                    <p class="card-text">Instant scoring with detailed answer explanations.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">🏆 Leaderboards</h5>
                    <p class="card-text">Compete with others and track your progress.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
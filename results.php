<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get result details
$stmt = $conn->prepare("SELECT r.*, q.title as quiz_title 
                        FROM results r 
                        JOIN quizzes q ON r.quiz_id = q.id 
                        WHERE r.id = ? AND r.user_id = ?");
$stmt->execute([$result_id, $_SESSION['user_id']]);
$result = $stmt->fetch();

if(!$result) {
    header("Location: quiz.php");
    exit();
}

// Get user answers with question details
$stmt = $conn->prepare("SELECT ua.*, q.question_text, q.question_type, q.points 
                        FROM user_answers ua 
                        JOIN questions q ON ua.question_id = q.id 
                        WHERE ua.result_id = ?");
$stmt->execute([$result_id]);
$answers = $stmt->fetchAll();

// Get options for multiple choice questions
foreach($answers as &$answer) {
    if($answer['selected_option_id']) {
        $stmt = $conn->prepare("SELECT option_text FROM options WHERE id = ?");
        $stmt->execute([$answer['selected_option_id']]);
        $option = $stmt->fetch();
        $answer['selected_option_text'] = $option ? $option['option_text'] : 'Not available';
    }
}
unset($answer); // Break the reference

require_once 'includes/header.php';
?>

<div class="results-container">
    <h2 class="mb-4">Quiz Results: <?php echo htmlspecialchars($result['quiz_title']); ?></h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Score:</strong> <?php echo $result['score']; ?> out of <?php echo $result['total_questions']; ?></p>
                    <p><strong>Percentage:</strong> <?php echo round(($result['score'] / $result['total_questions']) * 100); ?>%</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Started:</strong> <?php echo date('M j, Y g:i A', strtotime($result['started_at'])); ?></p>
                    <p><strong>Completed:</strong> <?php echo date('M j, Y g:i A', strtotime($result['completed_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <h4 class="mb-3">Question Details</h4>
    
    <?php foreach($answers as $index => $answer): ?>
        <div class="card mb-3 <?php echo $answer['is_correct'] ? 'border-success' : 'border-danger'; ?>">
            <div class="card-header <?php echo $answer['is_correct'] ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                Question <?php echo $index + 1; ?> - 
                <?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                (<?php echo $answer['is_correct'] ? $answer['points'] : 0; ?> / <?php echo $answer['points']; ?> points)
            </div>
            <div class="card-body">
                <p class="card-text"><strong>Question:</strong> <?php echo htmlspecialchars($answer['question_text']); ?></p>
                
                <?php if($answer['question_type'] == 'multiple-choice'): ?>
                    <p><strong>Your answer:</strong> <?php echo htmlspecialchars($answer['selected_option_text'] ?? 'Not answered'); ?></p>
                    <?php if(!$answer['is_correct']): ?>
                        <p><strong>Correct answer:</strong> 
                            <?php 
                                $stmt = $conn->prepare("SELECT option_text FROM options WHERE question_id = ? AND is_correct = TRUE LIMIT 1");
                                $stmt->execute([$answer['question_id']]);
                                $correct_option = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if($correct_option) {
                                    echo htmlspecialchars($correct_option['option_text']);
                                } else {
                                    echo "Correct answer not available";
                                }
                            ?>
                        </p>
                    <?php endif; ?>
                <?php elseif($answer['question_type'] == 'true-false'): ?>
                    <p><strong>Your answer:</strong> <?php echo htmlspecialchars($answer['answer_text'] ?? 'Not answered'); ?></p>
                    <?php if(!$answer['is_correct']): ?>
                        <p><strong>Correct answer:</strong> 
                            <?php 
                                // Assuming true-false questions store correct answer in questions table
                                $stmt = $conn->prepare("SELECT correct_answer FROM questions WHERE id = ?");
                                $stmt->execute([$answer['question_id']]);
                                $correct_answer = $stmt->fetchColumn();
                                echo htmlspecialchars($correct_answer ?: 'Not available');
                            ?>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><strong>Your answer:</strong> <?php echo htmlspecialchars($answer['answer_text'] ?? 'Not answered'); ?></p>
                    <?php if(!$answer['is_correct']): ?>
                        <p class="text-muted">Note: Short answer questions require manual grading.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="mt-4">
        <a href="quiz.php" class="btn btn-primary">Back to Quizzes</a>
        <!-- <a href="quiz_start.php?id=<?php echo $result['quiz_id']; ?>" class="btn btn-secondary">Retake Quiz</a> -->
    </div>
</div>
<button onclick="window.print()">Print this page</button>
<?php require_once 'includes/footer.php'; ?>
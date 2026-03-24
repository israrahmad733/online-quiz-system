<?php
// require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get quiz ID
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND is_published = TRUE");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if(!$quiz) {
    header("Location: quiz.php");
    exit();
}

// Get questions for this quiz
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

// Get options for each question
foreach($questions as &$question) {
    $stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ?");
    $stmt->execute([$question['id']]);
    $question['options'] = $stmt->fetchAll();
}
unset($question); // Break the reference

// Start timer in session if not already started
if(!isset($_SESSION['quiz_start_time'][$quiz_id])) {
    $_SESSION['quiz_start_time'][$quiz_id] = time();
    $_SESSION['quiz_time_limit'][$quiz_id] = $quiz['time_limit'] * 60; // Convert to seconds
}

// Calculate remaining time
$elapsed_time = time() - $_SESSION['quiz_start_time'][$quiz_id];
$remaining_time = max(0, $_SESSION['quiz_time_limit'][$quiz_id] - $elapsed_time);

require_once 'includes/header.php';
?>

<div class="quiz-container">
    <h2 class="mb-4"><?php echo htmlspecialchars($quiz['title']); ?></h2>
    <div class="timer alert alert-info mb-4">
        Time remaining: <span id="time"><?php echo gmdate("i:s", $remaining_time); ?></span>
    </div>
    
    <form id="quizForm" action="submit_quiz.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
        
        <?php foreach($questions as $index => $question): ?>
            <div class="card mb-4 question" data-question-id="<?php echo $question['id']; ?>">
                <div class="card-header">
                    <h5>Question <?php echo $index + 1; ?></h5>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                    
                    <?php if($question['question_type'] == 'multiple-choice'): ?>
                        <div class="options">
                            <?php foreach($question['options'] as $option): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="question_<?php echo $question['id']; ?>" 
                                           id="option_<?php echo $option['id']; ?>" 
                                           value="<?php echo $option['id']; ?>">
                                    <label class="form-check-label" for="option_<?php echo $option['id']; ?>">
                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif($question['question_type'] == 'true-false'): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                   name="question_<?php echo $question['id']; ?>" 
                                   id="true_<?php echo $question['id']; ?>" 
                                   value="true">
                            <label class="form-check-label" for="true_<?php echo $question['id']; ?>">True</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                   name="question_<?php echo $question['id']; ?>" 
                                   id="false_<?php echo $question['id']; ?>" 
                                   value="false">
                            <label class="form-check-label" for="false_<?php echo $question['id']; ?>">False</label>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <textarea class="form-control" 
                                      name="question_<?php echo $question['id']; ?>" 
                                      rows="3"></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" class="btn btn-primary btn-lg">Submit Quiz</button>
    </form>
</div>

<script>
// Timer countdown
let timeRemaining = <?php echo $remaining_time; ?>;
const timerElement = document.getElementById('time');

function updateTimer() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    timerElement.textContent = ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')};
    
    if(timeRemaining <= 0) {
        document.getElementById('quizForm').submit();
    } else {
        timeRemaining--;
        setTimeout(updateTimer, 1000);
    }
}

updateTimer();
</script>

<?php require_once 'includes/footer.php'; ?>
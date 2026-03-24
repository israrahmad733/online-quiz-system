<?php
// require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    
    // Verify quiz exists and is published
    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND is_published = TRUE");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch();
    
    if(!$quiz) {
        header("Location: quiz.php");
        exit();
    }
    
    // Get all questions for this quiz
    $stmt = $conn->prepare("SELECT id, question_type FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $questions = $stmt->fetchAll();
    
    $total_questions = count($questions);
    $score = 0;
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Create result record
        $stmt = $conn->prepare("INSERT INTO results (user_id, quiz_id, score, total_questions, started_at, completed_at) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $start_time = $_SESSION['quiz_start_time'][$quiz_id];
        $stmt->execute([
            $_SESSION['user_id'],
            $quiz_id,
            $score,
            $total_questions,
            date('Y-m-d H:i:s', $start_time),
            date('Y-m-d H:i:s')
        ]);
        $result_id = $conn->lastInsertId();
        
        // Process each question
        foreach($questions as $question) {
            $question_id = $question['id'];
            $is_correct = false;
            $selected_option_id = null;
            $answer_text = null;
            
            // Check if answer was submitted
            $answer_key = 'question_' . $question_id;
            if(isset($_POST[$answer_key])) {
                $answer = $_POST[$answer_key];
                
                if($question['question_type'] == 'multiple-choice') {
                    $selected_option_id = intval($answer);
                    
                    // Check if option is correct
                    $stmt = $conn->prepare("SELECT is_correct FROM options WHERE id = ?");
                    $stmt->execute([$selected_option_id]);
                    $option = $stmt->fetch();
                    
                    if($option && $option['is_correct']) {
                        $is_correct = true;
                        $score++;
                    }
                } elseif($question['question_type'] == 'true-false') {
                    $answer_text = $answer == 'true' ? 'True' : 'False';
                    
                    // For simplicity, assume correct answer is 'True' (adjust as needed)
                    if($answer == 'true') {
                        $is_correct = true;
                        $score++;
                    }
                } else {
                    $answer_text = trim($answer);
                    // For short answer, manual grading would be needed
                    $is_correct = false; // Default to false for manual review
                }
            }
            
            // Record user answer
            $stmt = $conn->prepare("INSERT INTO user_answers 
                                   (result_id, question_id, selected_option_id, answer_text, is_correct) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $result_id,
                $question_id,
                $selected_option_id,
                $answer_text,
                $is_correct
            ]);
        }
        
        // Update result with final score
        $stmt = $conn->prepare("UPDATE results SET score = ? WHERE id = ?");
        $stmt->execute([$score, $result_id]);
        
        // Commit transaction
        $conn->commit();
        
        // Clear quiz timer from session
        unset($_SESSION['quiz_start_time'][$quiz_id]);
        unset($_SESSION['quiz_time_limit'][$quiz_id]);
        
        // Redirect to results page
        header("Location: results.php?id=" . $result_id);
        exit();
    } catch(Exception $e) {
        // Rollback on error
        $conn->rollBack();
        $_SESSION['error'] = "An error occurred while submitting your quiz. Please try again.";
        header("Location: quiz.php");
        exit();
    }
} else {
    header("Location: quiz.php");
    exit();
}
?>
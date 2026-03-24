<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $quiz_id = intval($_GET['id']);
    
    try {
        $conn->beginTransaction();

        // Delete related user answers
        $stmt = $conn->prepare("
            DELETE FROM user_answers 
            WHERE result_id IN (
                SELECT id FROM results WHERE quiz_id = ?
            )
        ");
        $stmt->execute([$quiz_id]);

        // Delete results
        $stmt = $conn->prepare("DELETE FROM results WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);

        // Delete options
        $stmt = $conn->prepare("
            DELETE FROM options 
            WHERE question_id IN (
                SELECT id FROM questions WHERE quiz_id = ?
            )
        ");
        $stmt->execute([$quiz_id]);

        // Delete questions
        $stmt = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);

        // Finally delete the quiz
        $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt->execute([$quiz_id]);

        $conn->commit();
        $_SESSION['success'] = "Quiz deleted successfully!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error deleting quiz: " . $e->getMessage();
    }
}

// Redirect back to manage quizzes page
header("Location: manage_quizzes.php");
exit();
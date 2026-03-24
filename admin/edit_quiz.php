<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing quiz data
$quiz = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$quiz->execute([$quiz_id]);
$quiz = $quiz->fetch();

// Fetch questions with options
$questions = $conn->prepare("
    SELECT q.*, o.id AS option_id, o.option_text, o.is_correct 
    FROM questions q
    LEFT JOIN options o ON q.id = o.question_id
    WHERE q.quiz_id = ?
    ORDER BY q.id
");
$questions->execute([$quiz_id]);
$questions = $questions->fetchAll();

// Organize questions with options
$organized = [];
foreach ($questions as $q) {
    if (!isset($organized[$q['id']])) {
        $organized[$q['id']] = [
            'text' => $q['question_text'],
            'type' => $q['question_type'],
            'points' => $q['points'],
            'options' => []
        ];
    }
    if ($q['option_id']) {
        $organized[$q['id']]['options'][$q['option_id']] = [
            'text' => $q['option_text'],
            'correct' => $q['is_correct']
        ];
    }
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Update quiz
        $stmt = $conn->prepare("UPDATE quizzes SET 
            title = ?, 
            description = ?, 
            time_limit = ?, 
            is_published = ? 
            WHERE id = ?");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['time_limit'],
            isset($_POST['is_published']) ? 1 : 0,
            $quiz_id
        ]);

        // Delete removed questions
        $existing_questions = array_keys($organized);
        $new_questions = array_filter(array_keys($_POST['questions'] ?? []), 'is_numeric');
        $to_delete = array_diff($existing_questions, $new_questions);

        if (!empty($to_delete)) {
            $placeholders = implode(',', array_fill(0, count($to_delete), '?'));
            $conn->prepare("DELETE FROM questions WHERE id IN ($placeholders)")->execute($to_delete);
        }

        // Update or insert questions and options
        foreach ($_POST['questions'] as $qid => $question) {
            if (empty($question['text'])) continue;

            if (is_numeric($qid)) {
                // Update question
                $stmt = $conn->prepare("UPDATE questions SET 
                    question_text = ?, 
                    question_type = ?, 
                    points = ? 
                    WHERE id = ?");
                $stmt->execute([
                    $question['text'],
                    $question['type'],
                    $question['points'],
                    $qid
                ]);
            } else {
                // Insert question
                $stmt = $conn->prepare("INSERT INTO questions 
                    (quiz_id, question_text, question_type, points) 
                    VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $quiz_id,
                    $question['text'],
                    $question['type'],
                    $question['points']
                ]);
                $qid = $conn->lastInsertId();
            }

            // Handle multiple-choice options
            if ($question['type'] === 'multiple-choice') {
                $existing_options = isset($organized[$qid]['options']) ? array_keys($organized[$qid]['options']) : [];
                $new_options = array_keys($question['options'] ?? []);
                $to_delete = array_diff($existing_options, $new_options);

                if (!empty($to_delete)) {
                    $placeholders = implode(',', array_fill(0, count($to_delete), '?'));
                    $conn->prepare("DELETE FROM options WHERE id IN ($placeholders)")->execute($to_delete);
                }

                foreach ($question['options'] as $oid => $option) {
                    if (empty($option['text'])) continue;
                    $is_correct = ($question['correct'] == $oid) ? 1 : 0;

                    if (is_numeric($oid)) {
                        $stmt = $conn->prepare("UPDATE options SET 
                            option_text = ?, 
                            is_correct = ? 
                            WHERE id = ?");
                        $stmt->execute([
                            $option['text'],
                            $is_correct,
                            $oid
                        ]);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO options 
                            (question_id, option_text, is_correct) 
                            VALUES (?, ?, ?)");
                        $stmt->execute([
                            $qid,
                            $option['text'],
                            $is_correct
                        ]);
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Quiz updated successfully!";
        header("Location: manage_quizzes.php");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error updating quiz: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>


<div class="container mt-4">
    <h2>Edit Quiz: <?= htmlspecialchars($quiz['title']) ?></h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="quizForm">
        <!-- Quiz Details -->
        <div class="card mb-4">
            <div class="card-header">Quiz Information</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($quiz['title']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= 
                        htmlspecialchars($quiz['description']) ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Time Limit (minutes)</label>
                        <input type="number" name="time_limit" class="form-control" 
                               value="<?= $quiz['time_limit'] ?>" min="1" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" 
                                   name="is_published" id="is_published" <?= 
                                   $quiz['is_published'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_published">Published</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div id="questionsContainer">
            <?php $q_index = 0; ?>
            <?php foreach ($organized as $qid => $question): ?>
            <div class="question card mb-4" data-question-id="<?= $qid ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Question #<?= ++$q_index ?></span>
                    <button type="button" class="btn btn-danger btn-sm remove-question">Remove</button>
                </div>
                <div class="card-body">
                    <input type="hidden" name="questions[<?= $qid ?>][exists]" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea name="questions[<?= $qid ?>][text]" 
                                  class="form-control" rows="2" required><?= 
                                  htmlspecialchars($question['text']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Question Type</label>
                            <select name="questions[<?= $qid ?>][type]" 
                                    class="form-select question-type" required>
                                <option value="multiple-choice" <?= 
                                    $question['type'] === 'multiple-choice' ? 'selected' : '' ?>>Multiple Choice</option>
                                <option value="true-false" <?= 
                                    $question['type'] === 'true-false' ? 'selected' : '' ?>>True/False</option>
                                <option value="short-answer" <?= 
                                    $question['type'] === 'short-answer' ? 'selected' : '' ?>>Short Answer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" name="questions[<?= $qid ?>][points]" 
                                   class="form-control" value="<?= $question['points'] ?>" min="1" required>
                        </div>
                    </div>

                    <?php if ($question['type'] === 'multiple-choice'): ?>
                    <div class="options-container">
                        <?php $o_index = 0; ?>
                        <?php foreach ($question['options'] as $oid => $option): ?>
                        <div class="option mb-2">
                            <input type="hidden" name="questions[<?= $qid ?>][options][<?= $oid ?>][exists]" value="1">
                            <div class="input-group">
                                <input type="text" 
                                       name="questions[<?= $qid ?>][options][<?= $oid ?>][text]" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($option['text']) ?>" 
                                       placeholder="Option text" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="questions[<?= $qid ?>][correct]" 
                                           value="<?= $oid ?>" <?= 
                                           $option['correct'] ? 'checked' : '' ?>>
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">×</button>
                            </div>
                        </div>
                        <?php $o_index++; endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm add-option mt-2">Add Option</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="d-grid gap-2 d-md-block mt-3">
            <button type="button" class="btn btn-success" id="addQuestion">Add Question</button>
            <button type="submit" class="btn btn-primary">Update Quiz</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let questionCounter = 1;
    const questionsContainer = document.getElementById('questionsContainer');

    // Add new question
    document.getElementById('addQuestion').addEventListener('click', function() {
        const newIndex = questionCounter++;
        const newQuestion = document.querySelector('.question').cloneNode(true);
        
        // Update all data attributes and names
        newQuestion.setAttribute('data-question-index', newIndex);
        newQuestion.innerHTML = newQuestion.innerHTML
            .replace(/questions\[0\]/g, `questions[${newIndex}]`)
            .replace(/Question #1/g, `Question #${newIndex + 1}`);

        // Reset values
        newQuestion.querySelector('textarea').value = '';
        newQuestion.querySelector('input[type="number"]').value = 1;
        newQuestion.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
        newQuestion.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
        
        // Clear existing options except first
        const optionsContainer = newQuestion.querySelector('.options-container');
        while (optionsContainer.children.length > 1) {
            optionsContainer.lastChild.remove();
        }
        
        questionsContainer.appendChild(newQuestion);
    });

    // Handle dynamic elements using event delegation
    questionsContainer.addEventListener('click', function(e) {
        // Remove question
        if (e.target.classList.contains('remove-question')) {
            e.target.closest('.question').remove();
            updateQuestionNumbers();
        }
        
        // Add option
        if (e.target.classList.contains('add-option')) {
            const question = e.target.closest('.question');
            const optionsContainer = question.querySelector('.options-container');
            const questionIndex = question.getAttribute('data-question-index');
            const optionIndex = optionsContainer.children.length;
            
            const newOption = document.createElement('div');
            newOption.className = 'option mb-2';
            newOption.innerHTML = `
                <div class="input-group">
                    <input type="text" 
                           name="questions[${questionIndex}][options][${optionIndex}][text]" 
                           class="form-control" 
                           placeholder="Option text"
                           required>
                    <div class="input-group-text">
                        <input class="form-check-input" 
                               type="radio" 
                               name="questions[${questionIndex}][correct]" 
                               value="${optionIndex}">
                    </div>
                    <button type="button" class="btn btn-outline-danger remove-option">×</button>
                </div>
            `;
            optionsContainer.appendChild(newOption);
        }
        
        // Remove option
        if (e.target.classList.contains('remove-option')) {
            e.target.closest('.option').remove();
        }
    });

    function updateQuestionNumbers() {
        document.querySelectorAll('.question').forEach((question, index) => {
            question.querySelector('.card-header span').textContent = `Question #${index + 1}`;
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
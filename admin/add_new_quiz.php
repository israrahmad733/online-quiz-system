<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Insert quiz
        $stmt = $conn->prepare("INSERT INTO quizzes (title, description, time_limit, is_published, created_by) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['time_limit'],
            isset($_POST['is_published']) ? 1 : 0,
            $_SESSION['user_id']
        ]);
        $quiz_id = $conn->lastInsertId();

        // Insert questions
        foreach ($_POST['questions'] as $qIndex => $question) {
            if (empty($question['text'])) continue;

            $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, question_type, points)
                                   VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $quiz_id,
                $question['text'],
                $question['type'],
                $question['points']
            ]);
            $question_id = $conn->lastInsertId();

            // Insert options for multiple choice
            if ($question['type'] === 'multiple-choice' && !empty($question['options'])) {
                foreach ($question['options'] as $oIndex => $option) {
                    if (empty($option['text'])) continue;
                    
                    $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct)
                                           VALUES (?, ?, ?)");
                    $stmt->execute([
                        $question_id,
                        $option['text'],
                        //isset($question['correct']) && $question['correct'] == $oIndex ? 1 : 0
                        (isset($question['correct']) && intval($question['correct']) === $oIndex) ? 1 : 0

                    ]);
                }
            }
        }

        $conn->commit();
        $success = "Quiz created successfully!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error creating quiz: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Create New Quiz</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" id="quizForm">
        <!-- Quiz Details -->
        <div class="card mb-4">
            <div class="card-header">Quiz Information</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Time Limit (minutes)</label>
                        <input type="number" name="time_limit" class="form-control" value="30" min="1" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published">
                            <label class="form-check-label" for="is_published">Publish Immediately</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div id="questionsContainer">
            <div class="question card mb-4" data-question-index="0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Question #1</span>
                    <button type="button" class="btn btn-danger btn-sm remove-question">Remove</button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea name="questions[0][text]" class="form-control" rows="2" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Question Type</label>
                            <select name="questions[0][type]" class="form-select question-type" required>
                                <option value="multiple-choice">Multiple Choice</option>
                                <!-- <option value="true-false">True/False</option> -->
                                <option value="short-answer">Short Answer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" name="questions[0][points]" class="form-control" value="1" min="1" required>
                        </div>
                    </div>

                    <!-- Options for Multiple Choice -->
                    <div class="options-container">
                        <div class="option mb-2">
                            <div class="input-group">
                                <input type="text" name="questions[0][options][0][text]" class="form-control" placeholder="Option text" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" type="radio" name="questions[0][correct]" value="0">
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">×</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm add-option mt-2">Add Option</button>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-block mt-3">
            <button type="button" class="btn btn-success" id="addQuestion">Add Question</button>
            <button type="submit" class="btn btn-primary">Save Quiz</button>
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
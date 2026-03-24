<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Quiz System</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Quiz System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                                            <li class="nav-item">
                            <a class="nav-link" href="quiz.php">Quizzes</a>
                        </li>
                                                    <li class="nav-item">
                                <a class="nav-link" href="admin/admin_dashboard.php">Admin</a>
                            </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                                    </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
<div class="container mt-4">
    <h2>Edit Quiz: myComputer</h2>
    
    
    <form method="POST" id="quizForm">
        <!-- Quiz Details -->
        <div class="card mb-4">
            <div class="card-header">Quiz Information</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" 
                           value="myComputer" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">Hardware and software</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Time Limit (minutes)</label>
                        <input type="number" name="time_limit" class="form-control" 
                               value="30" min="1" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" 
                                   name="is_published" id="is_published" checked>
                            <label class="form-check-label" for="is_published">Published</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div id="questionsContainer">
                                    <div class="question card mb-4" data-question-id="10">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Question #1</span>
                    <button type="button" class="btn btn-danger btn-sm remove-question">Remove</button>
                </div>
                <div class="card-body">
                    <input type="hidden" name="questions[10][exists]" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea name="questions[10][text]" 
                                  class="form-control" rows="2" required>what is computer</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Question Type</label>
                            <select name="questions[10][type]" 
                                    class="form-select question-type" required>
                                <option value="multiple-choice" selected>Multiple Choice</option>
                                <option value="true-false" >True/False</option>
                                <option value="short-answer" >Short Answer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" name="questions[10][points]" 
                                   class="form-control" value="2" min="1" required>
                        </div>
                    </div>

                                        <div class="options-container">
                                                                        <div class="option mb-2">
                            <input type="hidden" name="questions[10][options][2][exists]" value="1">
                            <div class="input-group">
                                <input type="text" 
                                       name="questions[10][options][2][text]" 
                                       class="form-control" 
                                       value="hardware" 
                                       placeholder="Option text" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="questions[10][correct]" 
                                           value="2" >
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">×</button>
                            </div>
                        </div>
                                                <div class="option mb-2">
                            <input type="hidden" name="questions[10][options][3][exists]" value="1">
                            <div class="input-group">
                                <input type="text" 
                                       name="questions[10][options][3][text]" 
                                       class="form-control" 
                                       value="software" 
                                       placeholder="Option text" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="questions[10][correct]" 
                                           value="3" >
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">×</button>
                            </div>
                        </div>
                                                <div class="option mb-2">
                            <input type="hidden" name="questions[10][options][4][exists]" value="1">
                            <div class="input-group">
                                <input type="text" 
                                       name="questions[10][options][4][text]" 
                                       class="form-control" 
                                       value="machine" 
                                       placeholder="Option text" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="questions[10][correct]" 
                                           value="4" >
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">×</button>
                            </div>
                        </div>
                                                <div class="option mb-2">
                            <input type="hidden" name="questions[10][options][5][exists]" value="1">
                            <div class="input-group">
                                <input type="text" 
                                       name="questions[10][options][5][text]" 
                                       class="form-control" 
                                       value="hardware and software" 
                                       placeholder="Option text" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="questions[10][correct]" 
                                           value="5" checked>
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
            <button type="submit" class="btn btn-primary">Update Quiz</button>
        </div>
    </form>
</div>

</div>
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p>&copy; 2025 Online Quiz System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/script.js"></script>


</body>
</html>
<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Quiz System</title>
    <link href="<?= BASE_URL ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/font_awesome/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
      <style>
        .navbar-brand {
            flex-grow: 1;
            text-align: center;
        }
        .navbar-brand h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
            text-shadow: 3px 3px 5px rgba(0,0,0,1);
            margin: 0;
        }
        .navbar-nav .nav-link {
            font-size: 1rem;
            font-weight: 500;
        }
        .navbar .navbar-brand {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
         <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>/index.php"> <h2 class="m-0">Online Quiz Management System</h2></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/quiz.php">Quizzes</a>
                        </li>
                        <?php if($_SESSION['is_admin']): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>/admin/admin_dashboard.php">Admin</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>/user_dashboard.php">Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
    </body>
</html>        
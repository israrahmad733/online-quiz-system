<?php
session_start();

// Database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quiz_system_updated');

// Set BASE_URL manually to your root project folder
define('BASE_URL', 'http://localhost/quiz_system-updated');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

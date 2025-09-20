<?php
// Development configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blood_donation');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');

// Development settings
define('APP_ENV', 'development');
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);

// CORS settings for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

    public static function getDatabaseDSN() {
        return sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            self::DB_HOST,
            self::DB_NAME,
            self::DB_CHARSET
        );
    }
}
<?php
// Set timezone
date_default_timezone_set(Config::TIMEZONE);

// Error reporting settings
if (getenv('ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>
// config.php - Configuration settings
class Config {
    // Database configuration
    const DB_HOST = 'localhost';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = '';
    const DB_NAME = 'blood_donation';
    const DB_CHARSET = 'utf8mb4';

    // Application settings
    const APP_NAME = 'Blood Donation System';
    const APP_VERSION = '1.0.0';
    const TIMEZONE = 'UTC';

    // Security settings
    const SESSION_TIMEOUT = 3600; // 1 hour
    const MAX_LOGIN_ATTEMPTS = 5;
    const PASSWORD_MIN_LENGTH = 8;

    // Email settings (for notifications)
    const SMTP_HOST = 'localhost';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = '';
    const SMTP_PASSWORD = '';

    // File upload settings
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'pdf'];

    // Blood donation rules
    const MIN_DONATION_AGE = 18;
    const MAX_DONATION_AGE = 65;
    const DAYS_BETWEEN_DONATIONS = 90;
    const BLOOD_EXPIRY_DAYS = 42;

    public static function get($key) {
        return defined("self::$key") ? constant("self::$key") : null;
    }



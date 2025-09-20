<?php
// Database configuration for Blood Donation System

class Config {
    // Database connection settings
    const DB_HOST = 'localhost';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = '';
    const DB_NAME = 'blood_donation';
    const DB_PORT = 3306;

    // Application settings
    const APP_NAME = 'Blood Donation System';
    const APP_VERSION = '1.0.0';

    // Security settings
    const SESSION_TIMEOUT = 3600; // 1 hour
    const PASSWORD_MIN_LENGTH = 8;

    // Email settings (for notifications)
    const SMTP_HOST = 'localhost';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = '';
    const SMTP_PASSWORD = '';

    // Other settings
    const TIMEZONE = 'America/New_York';
    const DATE_FORMAT = 'Y-m-d';
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
}

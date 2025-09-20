-- Database migration to add missing medical_history column
-- Run this SQL in your MySQL database or phpMyAdmin

USE blood_donation;

-- Add medical_history column if it doesn't exist
ALTER TABLE donors ADD COLUMN medical_history TEXT NULL AFTER password;

-- Verify the column was added
DESCRIBE donors;

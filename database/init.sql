-- Database initialization script for Blood Donation System
-- This file ensures proper database setup and sample data loading

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS blood_donation;
USE blood_donation;

-- Create user and grant permissions
CREATE USER IF NOT EXISTS 'blooddonation'@'%' IDENTIFIED BY 'blooddonation123';
GRANT ALL PRIVILEGES ON blood_donation.* TO 'blooddonation'@'%';
FLUSH PRIVILEGES;

-- Donors table
CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    contact VARCHAR(15) NOT NULL,
    location VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    last_donation_date DATE NULL,
    medical_history TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blood requests table
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_name VARCHAR(100) NOT NULL,
    requester_contact VARCHAR(15) NOT NULL,
    requester_email VARCHAR(100) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    location VARCHAR(100) NOT NULL,
    urgency ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    hospital VARCHAR(100) NOT NULL,
    required_date DATE NOT NULL,
    units_needed INT DEFAULT 1,
    status ENUM('pending', 'fulfilled', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blood inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    donor_id INT NULL,
    collection_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('available', 'reserved', 'used', 'expired') DEFAULT 'available',
    location VARCHAR(100) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE SET NULL
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Donations log table
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    donation_date DATE NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_donated INT DEFAULT 1,
    location VARCHAR(100) NOT NULL,
    medical_checkup_passed BOOLEAN DEFAULT TRUE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
);

-- Request fulfillment tracking
CREATE TABLE IF NOT EXISTS request_fulfillments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    inventory_id INT NULL,
    donor_id INT NULL,
    fulfilled_date DATE NOT NULL,
    units_provided INT DEFAULT 1,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE SET NULL,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE SET NULL
);

-- Insert sample donors with hashed passwords (password: password123)
INSERT IGNORE INTO donors (name, age, gender, blood_group, contact, location, email, password, verified, last_donation_date) VALUES
('John Doe', 28, 'Male', 'O+', '+1-555-0101', 'New York', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2024-06-15'),
('Jane Smith', 32, 'Female', 'A+', '+1-555-0102', 'Los Angeles', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2024-07-20'),
('Mike Johnson', 25, 'Male', 'B-', '+1-555-0103', 'Chicago', 'mike.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL),
('Sarah Wilson', 35, 'Female', 'AB+', '+1-555-0104', 'Houston', 'sarah.wilson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2024-05-10'),
('David Brown', 29, 'Male', 'O-', '+1-555-0105', 'Phoenix', 'david.brown@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2024-08-01'),
('Lisa Davis', 26, 'Female', 'A-', '+1-555-0106', 'Philadelphia', 'lisa.davis@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, NULL);

-- Insert sample blood inventory
INSERT IGNORE INTO inventory (id, blood_group, donor_id, collection_date, expiry_date, location, status, notes) VALUES
(1, 'O+', 1, '2024-09-01', '2024-10-13', 'New York Blood Center', 'available', 'Fresh donation'),
(2, 'A+', 2, '2024-09-05', '2024-10-17', 'LA Medical Center', 'available', 'Good quality'),
(3, 'B-', 3, '2024-09-10', '2024-10-22', 'Chicago General Hospital', 'available', 'Rare type'),
(4, 'AB+', 4, '2024-09-15', '2024-10-27', 'Houston Medical Center', 'available', 'Universal plasma'),
(5, 'O-', 5, '2024-09-18', '2024-10-30', 'Phoenix Blood Bank', 'available', 'Universal donor'),
(6, 'A+', 2, '2024-08-01', '2024-09-12', 'LA Medical Center', 'expired', 'Expired unit'),
(7, 'O+', 1, '2024-09-12', '2024-10-24', 'New York Blood Center', 'reserved', 'Reserved for surgery');

-- Insert sample blood requests
INSERT IGNORE INTO requests (id, requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed, notes, status) VALUES
(1, 'Dr. Emergency Room', '+1-911-0001', 'emergency@hospital.com', 'O-', 'New York', 'Critical', 'NYC Emergency Hospital', '2024-09-21', 3, 'Car accident victim, immediate need', 'pending'),
(2, 'Dr. Surgery Dept', '+1-555-1001', 'surgery@medical.com', 'A+', 'Los Angeles', 'High', 'LA Medical Center', '2024-09-25', 2, 'Scheduled surgery patient', 'pending'),
(3, 'Dr. Oncology', '+1-555-1002', 'oncology@cancer.org', 'B-', 'Chicago', 'Medium', 'Chicago Cancer Center', '2024-09-30', 1, 'Cancer patient treatment', 'pending'),
(4, 'Dr. Maternity', '+1-555-1003', 'maternity@womens.com', 'AB+', 'Houston', 'High', 'Womens Hospital Houston', '2024-09-23', 2, 'Complications during delivery', 'pending'),
(5, 'Dr. Pediatrics', '+1-555-1004', 'pediatrics@children.org', 'O+', 'Phoenix', 'Low', 'Phoenix Children Hospital', '2024-10-05', 1, 'Routine procedure', 'pending');

-- Insert sample donations log
INSERT IGNORE INTO donations (id, donor_id, donation_date, blood_group, units_donated, location, medical_checkup_passed, notes) VALUES
(1, 1, '2024-09-01', 'O+', 1, 'New York Blood Center', 1, 'Healthy donor, no issues'),
(2, 2, '2024-09-05', 'A+', 1, 'LA Medical Center', 1, 'Regular donor, excellent health'),
(3, 3, '2024-09-10', 'B-', 1, 'Chicago General Hospital', 1, 'First time donor, very cooperative'),
(4, 4, '2024-09-15', 'AB+', 1, 'Houston Medical Center', 1, 'Experienced donor'),
(5, 5, '2024-09-18', 'O-', 1, 'Phoenix Blood Bank', 1, 'Universal donor, high demand type');

-- Insert default admin user (username: admin, password: admin123)
INSERT IGNORE INTO admins (id, username, email, password, role) VALUES
(1, 'admin', 'admin@blooddonation.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin'),
(2, 'manager', 'manager@blooddonation.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_donors_blood_group ON donors(blood_group);
CREATE INDEX IF NOT EXISTS idx_donors_location ON donors(location);
CREATE INDEX IF NOT EXISTS idx_donors_verified ON donors(verified);
CREATE INDEX IF NOT EXISTS idx_requests_blood_group ON requests(blood_group);
CREATE INDEX IF NOT EXISTS idx_requests_status ON requests(status);
CREATE INDEX IF NOT EXISTS idx_requests_urgency ON requests(urgency);
CREATE INDEX IF NOT EXISTS idx_inventory_blood_group ON inventory(blood_group);
CREATE INDEX IF NOT EXISTS idx_inventory_status ON inventory(status);
CREATE INDEX IF NOT EXISTS idx_inventory_expiry ON inventory(expiry_date);

-- Sample data for Blood Donation System
-- This file will be automatically executed when Docker MySQL container starts

USE blood_donation;

-- Insert sample donors with hashed passwords
INSERT INTO donors (name, age, gender, blood_group, contact, location, email, password, verified, last_donation_date) VALUES
('John Doe', 28, 'Male', 'O+', '+1-555-0101', 'New York', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-06-15'),
('Jane Smith', 32, 'Female', 'A+', '+1-555-0102', 'Los Angeles', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-07-20'),
('Mike Johnson', 25, 'Male', 'B-', '+1-555-0103', 'Chicago', 'mike.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL),
('Sarah Wilson', 35, 'Female', 'AB+', '+1-555-0104', 'Houston', 'sarah.wilson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-05-10'),
('David Brown', 29, 'Male', 'O-', '+1-555-0105', 'Phoenix', 'david.brown@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-08-01'),
('Lisa Davis', 26, 'Female', 'A-', '+1-555-0106', 'Philadelphia', 'lisa.davis@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, NULL);

-- Insert sample blood inventory
INSERT INTO inventory (blood_group, donor_id, collection_date, expiry_date, location, status, notes) VALUES
('O+', 1, '2025-09-01', '2025-10-13', 'New York Blood Center', 'available', 'Fresh donation'),
('A+', 2, '2025-09-05', '2025-10-17', 'LA Medical Center', 'available', 'Good quality'),
('B-', 3, '2025-09-10', '2025-10-22', 'Chicago General Hospital', 'available', 'Rare type'),
('AB+', 4, '2025-09-15', '2025-10-27', 'Houston Medical Center', 'available', 'Universal plasma'),
('O-', 5, '2025-09-18', '2025-10-30', 'Phoenix Blood Bank', 'available', 'Universal donor'),
('A+', 2, '2025-08-01', '2025-09-12', 'LA Medical Center', 'expired', 'Expired unit'),
('O+', 1, '2025-09-12', '2025-10-24', 'New York Blood Center', 'reserved', 'Reserved for surgery');

-- Insert sample blood requests
INSERT INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed, notes, status) VALUES
('Dr. Emergency Room', '+1-911-0001', 'emergency@hospital.com', 'O-', 'New York', 'Critical', 'NYC Emergency Hospital', '2025-09-21', 3, 'Car accident victim, immediate need', 'pending'),
('Dr. Surgery Dept', '+1-555-1001', 'surgery@medical.com', 'A+', 'Los Angeles', 'High', 'LA Medical Center', '2025-09-25', 2, 'Scheduled surgery patient', 'pending'),
('Dr. Oncology', '+1-555-1002', 'oncology@cancer.org', 'B-', 'Chicago', 'Medium', 'Chicago Cancer Center', '2025-09-30', 1, 'Cancer patient treatment', 'pending'),
('Dr. Maternity', '+1-555-1003', 'maternity@womens.com', 'AB+', 'Houston', 'High', 'Womens Hospital Houston', '2025-09-23', 2, 'Complications during delivery', 'pending'),
('Dr. Pediatrics', '+1-555-1004', 'pediatrics@children.org', 'O+', 'Phoenix', 'Low', 'Phoenix Children Hospital', '2025-10-05', 1, 'Routine procedure', 'pending');

-- Insert sample donations log
INSERT INTO donations (donor_id, donation_date, blood_group, units_donated, location, medical_checkup_passed, notes) VALUES
(1, '2025-09-01', 'O+', 1, 'New York Blood Center', 1, 'Healthy donor, no issues'),
(2, '2025-09-05', 'A+', 1, 'LA Medical Center', 1, 'Regular donor, excellent health'),
(3, '2025-09-10', 'B-', 1, 'Chicago General Hospital', 1, 'First time donor, very cooperative'),
(4, '2025-09-15', 'AB+', 1, 'Houston Medical Center', 1, 'Experienced donor'),
(5, '2025-09-18', 'O-', 1, 'Phoenix Blood Bank', 1, 'Universal donor, high demand type');

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admins (username, email, password, role) VALUES
('admin', 'admin@blooddonation.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin'),
('manager', 'manager@blooddonation.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Update some statistics
UPDATE donors SET last_donation_date = '2025-09-01' WHERE id = 1;
UPDATE donors SET last_donation_date = '2025-09-05' WHERE id = 2;
UPDATE donors SET last_donation_date = '2025-09-10' WHERE id = 3;
UPDATE donors SET last_donation_date = '2025-09-15' WHERE id = 4;
UPDATE donors SET last_donation_date = '2025-09-18' WHERE id = 5;

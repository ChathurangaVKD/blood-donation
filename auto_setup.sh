#!/bin/bash

# Blood Donation System - One-Time Automated Setup Script
# This script automatically sets up the complete system without manual intervention

set -e  # Exit on any error

echo "🩸 Blood Donation System - Automated One-Time Setup"
echo "===================================================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to install Homebrew if needed
install_homebrew() {
    if ! command_exists brew; then
        print_info "Installing Homebrew..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
        # Add Homebrew to PATH for this session
        export PATH="/opt/homebrew/bin:$PATH"
    else
        print_status "Homebrew already installed"
    fi
}

# Function to install PHP and MySQL
install_dependencies() {
    print_info "Installing PHP and MySQL via Homebrew..."

    # Update Homebrew
    brew update

    # Install PHP
    if ! command_exists php; then
        brew install php
    else
        print_status "PHP already installed"
    fi

    # Install MySQL
    if ! command_exists mysql; then
        brew install mysql
    else
        print_status "MySQL already installed"
    fi

    # Start MySQL service
    brew services start mysql
}

# Function to setup database
setup_database() {
    print_info "Setting up MySQL database..."

    # Wait for MySQL to start
    sleep 5

    # Create database and user
    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS blood_donation;
CREATE USER IF NOT EXISTS 'blooddonation'@'localhost' IDENTIFIED BY 'blooddonation123';
GRANT ALL PRIVILEGES ON blood_donation.* TO 'blooddonation'@'localhost';
FLUSH PRIVILEGES;
USE blood_donation;

-- Create tables
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    donor_id INT NULL,
    collection_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('available', 'reserved', 'used', 'expired') DEFAULT 'available',
    location VARCHAR(100) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT IGNORE INTO donors (name, age, gender, blood_group, contact, location, email, password, verified) VALUES
('John Doe', 28, 'Male', 'O+', '+1-555-0101', 'New York', 'john.doe@email.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Jane Smith', 32, 'Female', 'A+', '+1-555-0102', 'Los Angeles', 'jane.smith@email.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Mike Johnson', 25, 'Male', 'B-', '+1-555-0103', 'Chicago', 'mike.johnson@email.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT IGNORE INTO inventory (blood_group, collection_date, expiry_date, location, status) VALUES
('O+', '2024-09-01', '2024-10-13', 'New York Blood Center', 'available'),
('A+', '2024-09-05', '2024-10-17', 'LA Medical Center', 'available'),
('B-', '2024-09-10', '2024-10-22', 'Chicago General Hospital', 'available');

INSERT IGNORE INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed) VALUES
('Dr. Emergency', '+1-911-0001', 'emergency@hospital.com', 'O-', 'New York', 'Critical', 'NYC Emergency Hospital', '2024-09-25', 2),
('Dr. Surgery', '+1-555-1001', 'surgery@medical.com', 'A+', 'Los Angeles', 'High', 'LA Medical Center', '2024-09-28', 1);

INSERT IGNORE INTO admins (username, email, password, role) VALUES
('admin', 'admin@blooddonation.local', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
EOF

    print_status "Database setup completed"
}

# Function to start PHP server
start_server() {
    print_info "Starting PHP development server..."

    # Kill any existing PHP server on port 8080
    lsof -ti:8080 | xargs kill -9 2>/dev/null || true

    # Start PHP server in background
    nohup php -S localhost:8080 > server.log 2>&1 &
    SERVER_PID=$!

    # Wait for server to start
    sleep 3

    # Test if server is running
    if curl -s http://localhost:8080 > /dev/null; then
        print_status "PHP server started successfully on http://localhost:8080"
        echo $SERVER_PID > server.pid
    else
        print_error "Failed to start PHP server"
        exit 1
    fi
}

# Function to open application in browser
open_application() {
    print_info "Opening application in browser..."
    sleep 2
    open "http://localhost:8080/frontend/index.html"
}

# Main execution
main() {
    print_info "Starting automated setup process..."

    # Step 1: Install Homebrew if needed
    install_homebrew

    # Step 2: Install dependencies
    install_dependencies

    # Step 3: Setup database
    setup_database

    # Step 4: Start PHP server
    start_server

    # Step 5: Open application
    open_application

    # Success message
    echo ""
    echo "🎉 Setup Complete! Blood Donation System is now running!"
    echo ""
    echo "📱 Application URL: http://localhost:8080/frontend/index.html"
    echo "🔐 Demo Login: john.doe@email.com / password123"
    echo "🔧 Admin Login: admin / admin123"
    echo ""
    echo "📝 To stop the server: kill \$(cat server.pid)"
    echo "🔄 To restart: ./auto_setup.sh"
    echo ""
    print_status "System is ready for use!"
}

# Error handling
trap 'print_error "Setup failed. Please check the error messages above."' ERR

# Run main function
main

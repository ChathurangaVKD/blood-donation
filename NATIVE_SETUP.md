# Blood Donation System - Native Setup Guide

This guide provides multiple ways to run the Blood Donation System without Docker.

## 🎯 Setup Options

### Option 1: XAMPP (Recommended for beginners)
### Option 2: WAMP (Windows users)
### Option 3: Native PHP + MySQL (Advanced users)
### Option 4: Built-in PHP Server (Development only)

---

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3+
- Web server (Apache/Nginx) or use built-in PHP server

---

## 🚀 Option 1: XAMPP Setup

### Step 1: Install XAMPP
- Download from: https://www.apachefriends.org/
- Install with Apache, MySQL, and PHP modules

### Step 2: Setup Application
1. Copy the project folder to `xampp/htdocs/BloodDonationSystem`
2. Start XAMPP Control Panel
3. Start Apache and MySQL services
4. Run the setup script: `setup_xampp.bat` (Windows) or `setup_xampp.sh` (Linux/Mac)

### Step 3: Access Application
- Web Application: http://localhost/BloodDonationSystem/frontend/
- phpMyAdmin: http://localhost/phpmyadmin/
- Admin Panel: http://localhost/BloodDonationSystem/backend/admin.php

---

## 🚀 Option 2: WAMP Setup

### Step 1: Install WAMP
- Download from: http://www.wampserver.com/
- Install and start WAMP services

### Step 2: Setup Application
1. Copy project to `wamp64/www/BloodDonationSystem`
2. Run setup script: `setup_wamp.bat`
3. Configure virtual host (optional)

### Step 3: Access Application
- Web Application: http://localhost/BloodDonationSystem/frontend/
- phpMyAdmin: http://localhost/phpmyadmin/

---

## 🚀 Option 3: Native PHP + MySQL

### Step 1: Install Requirements
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php php-mysql mysql-server apache2

# CentOS/RHEL
sudo yum install php php-mysql mysql-server httpd

# macOS (with Homebrew)
brew install php mysql apache2

# Windows
Download PHP: https://www.php.net/downloads
Download MySQL: https://dev.mysql.com/downloads/mysql/
```

### Step 2: Configure Services
1. Start MySQL service
2. Start Apache service
3. Run setup script: `setup_native.sh` or `setup_native.bat`

---

## 🚀 Option 4: Built-in PHP Server (Development Only)

### Quick Start
```bash
# Clone/download the project
cd BloodDonationSystem

# Install MySQL and create database
mysql -u root -p < database/setup_database.sql

# Start PHP built-in server
php -S localhost:8080 -t frontend/

# In another terminal, start backend server
php -S localhost:8081 -t backend/
```

### Access Application
- Frontend: http://localhost:8080
- Backend API: http://localhost:8081

---

## 🗄️ Database Configuration

### Default Settings
- **Host:** localhost
- **Port:** 3306
- **Database:** blood_donation
- **Username:** blooddonation
- **Password:** blooddonation123

### Manual Database Setup
1. Create MySQL user and database
2. Import schema: `mysql -u root -p blood_donation < database/schema.sql`
3. Import sample data: `mysql -u root -p blood_donation < database/sample_data.sql`

---

## 🔧 Configuration Files

Update these files based on your setup:

### backend/config.php
```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blood_donation');
define('DB_USER', 'blooddonation');
define('DB_PASS', 'blooddonation123');
define('DB_PORT', '3306');
?>
```

### frontend/config.js
```javascript
const CONFIG = {
    API_BASE_URL: 'http://localhost/BloodDonationSystem/backend',
    // or for built-in server: 'http://localhost:8081'
};
```

---

## 🔐 Default Login Credentials

### User Account
- **Email:** john.doe@email.com
- **Password:** password123

### Admin Panel
- **Username:** admin
- **Password:** admin123

---

## 📁 Directory Structure

```
BloodDonationSystem/
├── frontend/           # Frontend files
├── backend/           # PHP backend API
├── database/          # SQL files
├── setup_xampp.bat   # XAMPP setup script
├── setup_wamp.bat    # WAMP setup script
├── setup_native.sh   # Native setup script
└── php_server.sh     # Built-in server script
```

---

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL service is running
   - Verify credentials in config files
   - Ensure database exists

2. **Permission Denied**
   - Set proper file permissions: `chmod -R 755 BloodDonationSystem`
   - Check Apache user permissions

3. **PHP Extensions Missing**
   - Install required extensions: `php-mysql`, `php-mysqli`, `php-json`

4. **Port Already in Use**
   - Change ports in configuration
   - Stop conflicting services

### Getting Help
- Check error logs in web server error log
- Enable PHP error reporting for debugging
- Verify all services are running

---

## 🎯 Quick Start Commands

### XAMPP
```bash
# Windows
start_xampp.bat

# Linux/Mac
./start_xampp.sh
```

### Native Setup
```bash
# Start all services
./start_native.sh

# Stop all services
./stop_native.sh
```

### Built-in Server
```bash
# Start development servers
./start_dev_server.sh
```

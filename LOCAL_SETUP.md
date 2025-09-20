# Local Development Setup Guide

## The Problem
The Docker containers are experiencing connectivity issues, causing the database integration to fail. This prevents the Blood Donation System from working properly.

## Solution: Local Development Setup

Since Docker is having persistent issues, I've created a local development solution that will work reliably.

## Requirements
1. **XAMPP**, **MAMP**, or **Local PHP/MySQL server**
2. **PHP 7.4+** with MySQL extension
3. **MySQL/MariaDB** database server

## Quick Setup Instructions

### Option 1: Using XAMPP (Recommended)

1. **Download and Install XAMPP**
   - Go to https://www.apachefriends.org/
   - Download XAMPP for macOS
   - Install and start Apache + MySQL

2. **Setup the Project**
   ```bash
   # Copy the project to XAMPP directory
   cp -r /Users/dchathuran/Documents/Projects/Nangi/BloodDonationSystem /Applications/XAMPP/htdocs/blooddonation
   ```

3. **Initialize Database**
   - Open browser: http://localhost/blooddonation/backend/local_db.php
   - This will create the database and sample data automatically

4. **Access Application**
   - Main App: http://localhost/blooddonation/frontend/index.html
   - phpMyAdmin: http://localhost/phpmyadmin

### Option 2: Using Built-in PHP Server

1. **Start PHP Development Server**
   ```bash
   cd /Users/dchathuran/Documents/Projects/Nangi/BloodDonationSystem
   php -S localhost:8080
   ```

2. **Setup MySQL Database**
   - Make sure MySQL is running locally
   - Visit: http://localhost:8080/backend/local_db.php
   - This creates all tables and sample data

3. **Access Application**
   - http://localhost:8080/frontend/index.html

## Demo Credentials
- **Email**: john.doe@email.com
- **Password**: password123

## Features That Will Work
✅ User Registration
✅ User Login  
✅ Blood Request Submission
✅ Donor Search
✅ Inventory Management
✅ Dashboard Statistics

## Database Structure
The system will create these tables automatically:
- `donors` - User registrations
- `requests` - Blood requests
- `inventory` - Blood units
- `admins` - Admin users

## Troubleshooting
If you encounter issues:
1. Check MySQL is running
2. Verify PHP has mysqli extension
3. Check database connection in browser console
4. Ensure proper file permissions

This local setup will work reliably without Docker complications.

#!/bin/bash

# BloodLink - Blood Donation System Startup Script
# Author: Blood Donation System Team
# Date: September 21, 2025

echo "🩸 BloodLink - Blood Donation Management System"
echo "=============================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 7.4 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo "✅ PHP Version: $PHP_VERSION"

# Check if MySQL is running (optional check)
if command -v mysql &> /dev/null; then
    echo "✅ MySQL is available"
else
    echo "⚠️  MySQL command not found. Make sure MySQL server is running."
fi

echo ""
echo "🚀 Starting BloodLink Development Server..."
echo "📍 Server will be available at: http://localhost:8080"
echo ""
echo "🌐 Available Pages:"
echo "   • Main Page:    http://localhost:8080/frontend/index.html"
echo "   • Search:       http://localhost:8080/frontend/search.html"
echo "   • Admin Panel:  http://localhost:8080/frontend/admin.html"
echo "   • Login:        http://localhost:8080/frontend/login.html"
echo ""
echo "👤 Default Admin Login:"
echo "   • Username: admin"
echo "   • Password: admin123"
echo ""
echo "🛑 Press Ctrl+C to stop the server"
echo "=============================================="
echo ""

# Start PHP built-in server
php -S localhost:8080 -t .

#!/bin/bash
# Blood Donation System - PHP Built-in Server (Development Mode)
# Quick setup for development and testing

echo "🩸 Blood Donation System - Development Server"
echo "=============================================="

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed"
    echo "Please install PHP 7.4+ first"
    exit 1
fi

PHP_VERSION=$(php -v | head -n1 | cut -d" " -f2 | cut -d"." -f1,2)
echo "✅ PHP $PHP_VERSION found"

# Check if MySQL is available
if ! command -v mysql &> /dev/null; then
    echo "⚠️  MySQL not found - you'll need to set up the database manually"
    echo "Alternative: Use SQLite for development (requires php-sqlite3)"
fi

# Create development configuration
echo "⚙️  Setting up development configuration..."

# Backend config for development
cat > "backend/config_dev.php" << 'EOF'
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
EOF

# Frontend config for development
cat > "frontend/config_dev.js" << 'EOF'
const CONFIG = {
    API_BASE_URL: 'http://localhost:8081',
    ENDPOINTS: {
        REGISTER: '/register.php',
        LOGIN: '/login.php',
        REQUEST: '/request.php',
        SEARCH: '/search.php',
        INVENTORY: '/inventory.php',
        DONATIONS: '/donations.php',
        ADMIN: '/admin.php'
    },
    BLOOD_GROUPS: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
    URGENCY_LEVELS: ['Low', 'Medium', 'High', 'Critical'],
    APP_NAME: 'Blood Donation System (Dev)',
    VERSION: '1.0.0-dev',
    DEBUG: true
};

// Helper functions
function getApiUrl(endpoint) {
    return CONFIG.API_BASE_URL + CONFIG.ENDPOINTS[endpoint];
}

async function apiCall(endpoint, data = null, method = 'GET') {
    const url = getApiUrl(endpoint);
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    };

    if (data && method !== 'GET') {
        if (data instanceof FormData) {
            delete options.headers['Content-Type'];
            options.body = data;
        } else {
            options.body = new URLSearchParams(data);
        }
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API call failed:', error);
        return { success: false, message: 'Network error occurred' };
    }
}
EOF

# Use development configs
cp backend/config_dev.php backend/config.php
cp frontend/config_dev.js frontend/config.js

# Setup database (if MySQL is available)
if command -v mysql &> /dev/null; then
    echo "🗄️  Setting up development database..."
    read -p "Enter MySQL root password (press Enter if no password): " -s MYSQL_PASS
    echo

    if [ -z "$MYSQL_PASS" ]; then
        MYSQL_CMD="mysql -u root"
    else
        MYSQL_CMD="mysql -u root -p$MYSQL_PASS"
    fi

    # Create database
    $MYSQL_CMD -e "CREATE DATABASE IF NOT EXISTS blood_donation;" 2>/dev/null

    # Import schema if available
    if [ -f "database/schema.sql" ]; then
        echo "📥 Importing database schema..."
        $MYSQL_CMD blood_donation < database/schema.sql 2>/dev/null
    fi

    if [ -f "database/sample_data.sql" ]; then
        echo "📥 Importing sample data..."
        $MYSQL_CMD blood_donation < database/sample_data.sql 2>/dev/null
    fi
fi

# Start servers
echo "🚀 Starting development servers..."

# Kill any existing servers on these ports
lsof -ti:8080 | xargs kill -9 2>/dev/null
lsof -ti:8081 | xargs kill -9 2>/dev/null

# Start frontend server
echo "Starting frontend server on http://localhost:8080..."
php -S localhost:8080 -t frontend/ > /dev/null 2>&1 &
FRONTEND_PID=$!

# Start backend server
echo "Starting backend server on http://localhost:8081..."
php -S localhost:8081 -t backend/ > /dev/null 2>&1 &
BACKEND_PID=$!

# Save PIDs for cleanup
echo $FRONTEND_PID > .dev_server_pids
echo $BACKEND_PID >> .dev_server_pids

echo "✅ Development servers started!"
echo ""
echo "🌐 Application URLs:"
echo "   - Frontend: http://localhost:8080"
echo "   - Backend API: http://localhost:8081"
echo "   - Admin Panel: http://localhost:8081/admin.php"
echo ""
echo "🗄️  Database: blood_donation (localhost:3306)"
echo "👤 Default Login: john.doe@email.com / password123"
echo ""
echo "📝 Development Features:"
echo "   - Debug mode enabled"
echo "   - Error reporting on"
echo "   - CORS headers for API testing"
echo ""
echo "🛑 To stop servers: ./stop_dev_servers.sh"
echo "📊 To view logs: tail -f /tmp/php_*.log"
echo ""
echo "Press Ctrl+C to stop servers..."

# Wait for interrupt
trap 'echo ""; echo "🛑 Stopping servers..."; kill $FRONTEND_PID $BACKEND_PID 2>/dev/null; rm -f .dev_server_pids; exit 0' INT
wait

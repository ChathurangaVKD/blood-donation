#!/bin/bash
# Blood Donation System - Native Setup Script for Linux/Mac
# This script sets up the application with native PHP and MySQL

echo "🩸 Blood Donation System - Native Setup"
echo "========================================"

# Check if required software is installed
echo "🔍 Checking system requirements..."

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed"
    echo "Install PHP 7.4+ with MySQL extension:"
    echo "  Ubuntu/Debian: sudo apt install php php-mysql php-mysqli php-json php-curl"
    echo "  CentOS/RHEL: sudo yum install php php-mysql php-json"
    echo "  macOS: brew install php"
    exit 1
fi

PHP_VERSION=$(php -v | head -n1 | cut -d" " -f2 | cut -d"." -f1,2)
echo "✅ PHP $PHP_VERSION found"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL is not installed"
    echo "Install MySQL:"
    echo "  Ubuntu/Debian: sudo apt install mysql-server"
    echo "  CentOS/RHEL: sudo yum install mysql-server"
    echo "  macOS: brew install mysql"
    exit 1
fi

echo "✅ MySQL found"

# Check Apache (optional)
if command -v apache2 &> /dev/null || command -v httpd &> /dev/null; then
    echo "✅ Apache web server found"
    USE_APACHE=true
else
    echo "⚠️  Apache not found - will use PHP built-in server"
    USE_APACHE=false
fi

# Create project directory
PROJECT_DIR="$HOME/BloodDonationSystem"
if [ "$USE_APACHE" = true ]; then
    # For Apache, try common web root directories
    WEB_ROOTS=("/var/www/html" "/usr/local/var/www" "/opt/lampp/htdocs")
    for root in "${WEB_ROOTS[@]}"; do
        if [ -d "$root" ] && [ -w "$root" ]; then
            PROJECT_DIR="$root/BloodDonationSystem"
            break
        fi
    done
fi

echo "📁 Setting up project in: $PROJECT_DIR"
mkdir -p "$PROJECT_DIR"

# Copy application files
echo "📋 Copying application files..."
cp -r frontend "$PROJECT_DIR/"
cp -r backend "$PROJECT_DIR/"
cp -r database "$PROJECT_DIR/"

# Create native configuration
echo "⚙️  Creating configuration files..."

# Backend config
cat > "$PROJECT_DIR/backend/config.php" << 'EOF'
<?php
// Native MySQL configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blood_donation');
define('DB_USER', 'blooddonation');
define('DB_PASS', 'blooddonation123');
define('DB_PORT', '3306');

// Application settings
define('APP_ENV', 'production');
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);
?>
EOF

# Frontend config
if [ "$USE_APACHE" = true ]; then
    API_URL="http://localhost/BloodDonationSystem/backend"
else
    API_URL="http://localhost:8081"
fi

cat > "$PROJECT_DIR/frontend/config.js" << EOF
const CONFIG = {
    API_BASE_URL: '$API_URL',
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
    APP_NAME: 'Blood Donation System',
    VERSION: '1.0.0',
    VALIDATION: {
        MIN_AGE: 18,
        MAX_AGE: 65,
        MIN_PASSWORD_LENGTH: 6,
        DAYS_BETWEEN_DONATIONS: 90
    }
};

// Helper function to get full API URL
function getApiUrl(endpoint) {
    return CONFIG.API_BASE_URL + CONFIG.ENDPOINTS[endpoint];
}

// Helper function to make API calls
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

# Setup MySQL database
echo "🗄️  Setting up MySQL database..."
echo "Please enter MySQL root password when prompted..."

# Create database and user
mysql -u root -p << 'EOF'
CREATE DATABASE IF NOT EXISTS blood_donation;
CREATE USER IF NOT EXISTS 'blooddonation'@'localhost' IDENTIFIED BY 'blooddonation123';
GRANT ALL PRIVILEGES ON blood_donation.* TO 'blooddonation'@'localhost';
FLUSH PRIVILEGES;
EOF

# Import database schema
if [ -f "$PROJECT_DIR/database/schema.sql" ]; then
    echo "📥 Importing database schema..."
    mysql -u blooddonation -pblooddonation123 blood_donation < "$PROJECT_DIR/database/schema.sql"
fi

if [ -f "$PROJECT_DIR/database/sample_data.sql" ]; then
    echo "📥 Importing sample data..."
    mysql -u blooddonation -pblooddonation123 blood_donation < "$PROJECT_DIR/database/sample_data.sql"
fi

# Set permissions
echo "🔐 Setting file permissions..."
chmod -R 755 "$PROJECT_DIR"
find "$PROJECT_DIR" -type f -name "*.php" -exec chmod 644 {} \;

# Create startup scripts
echo "📝 Creating startup scripts..."

if [ "$USE_APACHE" = true ]; then
    # Apache configuration
    cat > "$PROJECT_DIR/start_native.sh" << EOF
#!/bin/bash
echo "🚀 Starting Blood Donation System with Apache..."

# Start MySQL
sudo systemctl start mysql || sudo service mysql start

# Start Apache
sudo systemctl start apache2 || sudo systemctl start httpd || sudo service apache2 start

echo "✅ Services started!"
echo "🌐 Access your application at:"
echo "   - Frontend: http://localhost/BloodDonationSystem/frontend/"
echo "   - Backend API: http://localhost/BloodDonationSystem/backend/"
echo "   - Admin Panel: http://localhost/BloodDonationSystem/backend/admin.php"
EOF

    cat > "$PROJECT_DIR/stop_native.sh" << 'EOF'
#!/bin/bash
echo "🛑 Stopping Blood Donation System services..."

# Stop Apache
sudo systemctl stop apache2 || sudo systemctl stop httpd || sudo service apache2 stop

# Stop MySQL
sudo systemctl stop mysql || sudo service mysql stop

echo "✅ Services stopped!"
EOF
else
    # PHP built-in server
    cat > "$PROJECT_DIR/start_native.sh" << EOF
#!/bin/bash
echo "🚀 Starting Blood Donation System with PHP built-in server..."

# Start MySQL
sudo systemctl start mysql || sudo service mysql start

# Start PHP servers
cd "$PROJECT_DIR"
echo "Starting frontend server on port 8080..."
php -S localhost:8080 -t frontend/ &
FRONTEND_PID=\$!

echo "Starting backend server on port 8081..."
php -S localhost:8081 -t backend/ &
BACKEND_PID=\$!

echo "Frontend PID: \$FRONTEND_PID" > .server_pids
echo "Backend PID: \$BACKEND_PID" >> .server_pids

echo "✅ Servers started!"
echo "🌐 Access your application at:"
echo "   - Frontend: http://localhost:8080"
echo "   - Backend API: http://localhost:8081"
echo "   - Admin Panel: http://localhost:8081/admin.php"
echo ""
echo "Press Ctrl+C to stop servers"
wait
EOF

    cat > "$PROJECT_DIR/stop_native.sh" << EOF
#!/bin/bash
echo "🛑 Stopping Blood Donation System servers..."

cd "$PROJECT_DIR"
if [ -f .server_pids ]; then
    while read line; do
        PID=\$(echo \$line | cut -d':' -f2 | tr -d ' ')
        if [ ! -z "\$PID" ]; then
            kill \$PID 2>/dev/null
        fi
    done < .server_pids
    rm .server_pids
fi

# Stop any remaining PHP servers
pkill -f "php -S localhost:808"

echo "✅ Servers stopped!"
EOF
fi

# Make scripts executable
chmod +x "$PROJECT_DIR/start_native.sh"
chmod +x "$PROJECT_DIR/stop_native.sh"

# Create .htaccess for Apache
if [ "$USE_APACHE" = true ]; then
    cat > "$PROJECT_DIR/.htaccess" << 'EOF'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ frontend/$1 [L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# CORS headers for API
<FilesMatch "\.(php)$">
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
</FilesMatch>
EOF
fi

echo "✅ Setup completed successfully!"
echo ""
echo "🌐 Application Access:"
if [ "$USE_APACHE" = true ]; then
    echo "   - Frontend: http://localhost/BloodDonationSystem/frontend/"
    echo "   - Backend API: http://localhost/BloodDonationSystem/backend/"
    echo "   - Admin Panel: http://localhost/BloodDonationSystem/backend/admin.php"
else
    echo "   - Frontend: http://localhost:8080"
    echo "   - Backend API: http://localhost:8081"
    echo "   - Admin Panel: http://localhost:8081/admin.php"
fi
echo ""
echo "🗄️  Database Information:"
echo "   - Host: localhost"
echo "   - Database: blood_donation"
echo "   - Username: blooddonation"
echo "   - Password: blooddonation123"
echo ""
echo "👤 Default Login:"
echo "   - Email: john.doe@email.com"
echo "   - Password: password123"
echo ""
echo "🚀 Start the application:"
echo "   cd $PROJECT_DIR && ./start_native.sh"
echo ""
echo "🛑 Stop the application:"
echo "   cd $PROJECT_DIR && ./stop_native.sh"
echo ""

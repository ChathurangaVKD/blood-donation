#!/bin/bash
# Blood Donation System - XAMPP Setup Script for Linux/Mac
# This script sets up the application to run on XAMPP

echo "🩸 Blood Donation System - XAMPP Setup"
echo "=========================================="

# Check if XAMPP is installed (common paths)
XAMPP_PATHS=("/opt/lampp" "/Applications/XAMPP" "$HOME/xampp")
XAMPP_PATH=""

for path in "${XAMPP_PATHS[@]}"; do
    if [ -d "$path" ]; then
        XAMPP_PATH="$path"
        break
    fi
done

if [ -z "$XAMPP_PATH" ]; then
    echo "❌ XAMPP not found in common locations:"
    echo "   - /opt/lampp (Linux)"
    echo "   - /Applications/XAMPP (Mac)"
    echo "   - $HOME/xampp"
    echo ""
    echo "Please install XAMPP from: https://www.apachefriends.org/"
    echo "Or update the path in this script if installed elsewhere"
    exit 1
fi

echo "✅ XAMPP installation found at: $XAMPP_PATH"

# Set application directory
HTDOCS_PATH="$XAMPP_PATH/htdocs/BloodDonationSystem"

# Create application directory
echo "📁 Creating application directory..."
mkdir -p "$HTDOCS_PATH"

# Copy application files
echo "📋 Copying application files..."
cp -r frontend "$HTDOCS_PATH/"
cp -r backend "$HTDOCS_PATH/"
cp -r database "$HTDOCS_PATH/"

# Update configuration files
echo "⚙️  Updating configuration files..."

# Create backend config for XAMPP
cat > "$HTDOCS_PATH/backend/config_xampp.php" << 'EOF'
<?php
// XAMPP Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blood_donation');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
?>
EOF

# Update main config
cp "$HTDOCS_PATH/backend/config_xampp.php" "$HTDOCS_PATH/backend/config.php"

# Create frontend config for XAMPP
cat > "$HTDOCS_PATH/frontend/config_xampp.js" << 'EOF'
const CONFIG = {
    API_BASE_URL: 'http://localhost/BloodDonationSystem/backend',
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
    VERSION: '1.0.0'
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
            delete options.headers['Content-Type']; // Let browser set content-type for FormData
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

cp "$HTDOCS_PATH/frontend/config_xampp.js" "$HTDOCS_PATH/frontend/config.js"

# Setup database
echo "🗄️  Setting up database..."
echo "Please ensure XAMPP MySQL service is running before proceeding..."
read -p "Press Enter to continue when MySQL is running..."

# Check if MySQL is accessible
MYSQL_CMD="$XAMPP_PATH/bin/mysql"
if [ ! -f "$MYSQL_CMD" ]; then
    MYSQL_CMD="mysql"
fi

# Test MySQL connection
if ! command -v "$MYSQL_CMD" &> /dev/null; then
    echo "⚠️  MySQL command not found. Please ensure XAMPP MySQL is running."
    echo "Try running: sudo $XAMPP_PATH/lampp start"
    exit 1
fi

# Create database and user
echo "Creating database and importing schema..."
$MYSQL_CMD -u root -e "CREATE DATABASE IF NOT EXISTS blood_donation;"
$MYSQL_CMD -u root -e "CREATE USER IF NOT EXISTS 'blooddonation'@'localhost' IDENTIFIED BY 'blooddonation123';"
$MYSQL_CMD -u root -e "GRANT ALL PRIVILEGES ON blood_donation.* TO 'blooddonation'@'localhost';"
$MYSQL_CMD -u root -e "FLUSH PRIVILEGES;"

# Import database schema
if [ -f "$HTDOCS_PATH/database/schema.sql" ]; then
    echo "Importing database schema..."
    $MYSQL_CMD -u root blood_donation < "$HTDOCS_PATH/database/schema.sql"
fi

if [ -f "$HTDOCS_PATH/database/sample_data.sql" ]; then
    echo "Importing sample data..."
    $MYSQL_CMD -u root blood_donation < "$HTDOCS_PATH/database/sample_data.sql"
fi

# Create .htaccess for better URL handling
cat > "$HTDOCS_PATH/.htaccess" << 'EOF'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ frontend/$1 [L]
EOF

# Set proper permissions
chmod -R 755 "$HTDOCS_PATH"
chown -R $(whoami):$(whoami) "$HTDOCS_PATH" 2>/dev/null || true

echo "✅ Setup completed successfully!"
echo ""
echo "🌐 Application URLs:"
echo "   - Web Application: http://localhost/BloodDonationSystem/frontend/"
echo "   - Admin Panel: http://localhost/BloodDonationSystem/backend/admin.php"
echo "   - phpMyAdmin: http://localhost/phpmyadmin/"
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
echo "📝 Make sure XAMPP services are running!"
echo "🔧 Start XAMPP: sudo $XAMPP_PATH/lampp start"
echo "🛑 Stop XAMPP: sudo $XAMPP_PATH/lampp stop"
echo ""

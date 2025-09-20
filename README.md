# Blood Donation System - Complete Documentation & Setup Guide

A comprehensive blood donation management system built with PHP, MySQL, and Docker for easy deployment and management.

## 🚀 Project Overview

This Blood Donation System helps manage blood donors, blood requests, inventory, and facilitates matching between donors and recipients. The system includes donor registration, blood request management, inventory tracking, and an admin dashboard.

## 📁 Project Structure

```
BloodDonationSystem/
├── .gitignore                    # Git ignore file
├── README.md                     # This complete documentation
├── docker-compose.yml            # Docker services configuration
├── Dockerfile                    # Web server container definition
├── index.html                    # Welcome page with auto-redirect
├── start.sh                      # System startup script (macOS/Linux)
├── start.bat                     # System startup script (Windows Command Prompt)
├── start.ps1                     # System startup script (Windows PowerShell)
├── stop.sh                       # System shutdown script (macOS/Linux)
├── stop.bat                      # System shutdown script (Windows Command Prompt)
├── stop.ps1                      # System shutdown script (Windows PowerShell)
├── frontend/                     # Frontend application
│   ├── index.html               # Homepage
│   ├── login.html               # User login page
│   ├── register.html            # User registration page
│   ├── search.html              # Blood/donor search page
│   ├── request.html             # Blood request page
│   ├── contact.html             # Contact page
│   ├── style.css                # Main stylesheet
│   ├── script.js                # Frontend JavaScript
│   └── config.js                # Frontend configuration
├── backend/                      # Backend API endpoints
│   ├── db.php                   # Database connection & utilities
│   ├── login.php                # User authentication API
│   ├── register.php             # User registration API
│   ├── search.php               # Blood/donor search API
│   ├── request.php              # Blood request API
│   ├── inventory.php            # Inventory management API
│   ├── donations.php            # Donations tracking API
│   └── admin.php                # Admin panel API
├── database/                     # Database configuration
│   └── init.sql                 # Database schema & sample data
└── docker/                      # Docker configuration
    └── apache-config.conf       # Apache web server configuration
```

## 🛠️ Prerequisites

Before starting, ensure you have the following installed on your system:

- **Docker Desktop** (latest version)
  - Download: https://docs.docker.com/get-docker/
  - Make sure Docker is running before proceeding
- **Git** (for cloning the repository)
- **Terminal/Command Prompt access**

## 📥 Installation & Setup

### Step 1: Clone/Download the Project
```bash
# If using Git
git clone <repository-url>
cd BloodDonationSystem

# Or download and extract the ZIP file
```

### Step 2: Verify Docker Installation

#### For macOS/Linux:
```bash
# Check if Docker is installed and running
docker --version
docker-compose --version

# You should see version numbers for both commands
```

#### For Windows (Command Prompt):
```cmd
docker --version
docker-compose --version
```

#### For Windows (PowerShell):
```powershell
docker --version
docker-compose --version
```

**Note**: You should see version numbers for both commands on all platforms. If you get "command not found" errors, please install Docker Desktop and ensure it's running.

### Step 3: Make Scripts Executable (macOS/Linux)
```bash
chmod +x start.sh stop.sh
```

## 🚀 Starting the Application

### Quick Start (Recommended)

#### For macOS/Linux:
```bash
# Navigate to project directory
cd BloodDonationSystem

# Start the entire system
./start.sh
```

#### For Windows (Command Prompt):
```cmd
# Navigate to project directory
cd BloodDonationSystem

# Start the entire system
start.bat
```

#### For Windows (PowerShell):
```powershell
# Navigate to project directory
cd BloodDonationSystem

# Start the entire system
.\start.ps1
```

### Manual Start (Alternative)
```bash
# Build and start all services
docker-compose up -d --build

# Wait for services to initialize (about 30 seconds)
```

## 🌐 Accessing the Application

Once the system is running, you can access:

| Service | URL | Description |
|---------|-----|-------------|
| **Main Application** | http://localhost:8080/frontend/ | Primary user interface |
| **Welcome Page** | http://localhost:8080 | Auto-redirects to main app |
| **Admin Panel** | http://localhost:8080/backend/admin.php | Administrative interface |
| **Database Admin** | http://localhost:8081 | phpMyAdmin interface |

## 👤 Default Login Credentials

### Regular Users (for testing)
| Email | Password | Blood Type | Location |
|-------|----------|------------|----------|
| john.doe@email.com | password123 | O+ | New York |
| jane.smith@email.com | password123 | A+ | Los Angeles |
| mike.johnson@email.com | password123 | B- | Chicago |
| sarah.wilson@email.com | password123 | AB+ | Houston |
| david.brown@email.com | password123 | O- | Phoenix |

### Admin Users
| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Super Admin |
| manager | admin123 | Admin |

### Database Access (phpMyAdmin)
- **Server**: mysql
- **Username**: blooddonation
- **Password**: blooddonation123
- **Database**: blood_donation

## 🔧 System Features

### For Regular Users
1. **User Registration**: Create new donor accounts
2. **User Login**: Secure authentication system
3. **Blood Search**: Find available blood units and donors
4. **Blood Requests**: Submit requests for blood transfusions
5. **Contact System**: Get in touch with administrators

### For Administrators
1. **Donor Management**: View and manage registered donors
2. **Inventory Management**: Track blood units and expiry dates
3. **Request Management**: Handle blood requests and fulfillments
4. **Donation Tracking**: Monitor donation history and patterns
5. **System Analytics**: View system statistics and reports

### Blood Search Functionality
- **Search by Blood Type**: Find specific blood groups (A+, A-, B+, B-, AB+, AB-, O+, O-)
- **Location-based Search**: Find donors/inventory by city or area
- **Combined Search**: Search both donors and inventory simultaneously
- **Blood Compatibility**: Shows compatible blood types for transfusions
- **Donor Eligibility**: Indicates if donors are eligible to donate (90-day rule)

## 🩸 Blood Type Compatibility

The system includes built-in blood compatibility logic:

| Blood Type | Can Donate To | Can Receive From |
|------------|---------------|------------------|
| O- | All types | O- only |
| O+ | O+, A+, B+, AB+ | O+, O- |
| A- | A+, A-, AB+, AB- | A-, O- |
| A+ | A+, AB+ | A+, A-, O+, O- |
| B- | B+, B-, AB+, AB- | B-, O- |
| B+ | B+, AB+ | B+, B-, O+, O- |
| AB- | AB+, AB- | A-, B-, AB-, O- |
| AB+ | AB+ only | All types |

## 📊 Database Schema

The system uses the following main tables:

- **donors**: User registration and donor information
- **requests**: Blood transfusion requests
- **inventory**: Available blood units with expiry tracking
- **admins**: Administrative user accounts
- **donations**: Donation history and tracking
- **request_fulfillments**: Links requests with fulfilled donations

## 🔧 Technical Implementation Details

### Backend API Structure
```php
// Database Connection (db.php)
$servername = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : "mysql";
$username = "blooddonation";
$password = "blooddonation123";
$dbname = "blood_donation";
```

### Frontend Configuration
```javascript
// API Configuration (config.js)
const CONFIG = {
    API_BASE_URL: window.location.origin + '/backend',
    ENDPOINTS: {
        REGISTER: '/register.php',
        LOGIN: '/login.php',
        SEARCH: '/search.php',
        REQUEST: '/request.php',
        INVENTORY: '/inventory.php',
        DONATIONS: '/donations.php',
        ADMIN: '/admin.php'
    }
};
```

### Docker Configuration
```yaml
# docker-compose.yml
services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: blooddonation123
      MYSQL_DATABASE: blood_donation
      MYSQL_USER: blooddonation
      MYSQL_PASSWORD: blooddonation123
    ports:
      - "3306:3306"
  
  web:
    build: .
    ports:
      - "8080:80"
    depends_on:
      - mysql
  
  phpmyadmin:
    image: phpmyadmin/phpmyadmin:latest
    ports:
      - "8081:80"
    environment:
      PMA_HOST: mysql
```

## 🔍 Troubleshooting

### Common Issues

**1. Services won't start**
```bash
# Check if ports are already in use
lsof -i :8080
lsof -i :8081
lsof -i :3306

# Stop conflicting services or change ports in docker-compose.yml
```

**2. Database connection errors**
```bash
# Restart the database service
docker-compose restart mysql

# Check database logs
docker-compose logs mysql
```

**3. Permission errors**
```bash
# Fix script permissions
chmod +x start.sh stop.sh

# On Windows, run PowerShell as Administrator
```

**4. Search returns no results**
- Verify database contains sample data
- Check that inventory items have future expiry dates
- Ensure donors are marked as verified

### Checking System Status
```bash
# View all container status
docker-compose ps

# View logs for all services
docker-compose logs -f

# View logs for specific service
docker-compose logs -f web
docker-compose logs -f mysql
```

## 🛑 Stopping the Application

### Using the Stop Script

#### For macOS/Linux:
```bash
./stop.sh
```

#### For Windows (Command Prompt):
```cmd
stop.bat
```

#### For Windows (PowerShell):
```powershell
.\stop.ps1
```

### Manual Stop
```bash
# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: This deletes all data)
docker-compose down -v
```

## 🔄 Development & Maintenance

### Updating Code
1. Make changes to files
2. Restart only the web service: `docker-compose restart web`
3. For database changes, restart all: `docker-compose down && docker-compose up -d`

### Backup Database
```bash
# Export database
docker exec blooddonation_mysql mysqldump -u blooddonation -pblooddonation123 blood_donation > backup.sql

# Import database
docker exec -i blooddonation_mysql mysql -u blooddonation -pblooddonation123 blood_donation < backup.sql
```

### Adding New Sample Data
```bash
# Connect to database
docker exec -it blooddonation_mysql mysql -u blooddonation -pblooddonation123 blood_donation

# Run SQL commands to insert data
```

## 📝 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/backend/register.php` | POST | User registration |
| `/backend/login.php` | POST | User authentication |
| `/backend/search.php` | GET/POST | Search donors/inventory |
| `/backend/request.php` | POST | Submit blood requests |
| `/backend/inventory.php` | GET/POST | Manage inventory |
| `/backend/donations.php` | GET/POST | Track donations |
| `/backend/admin.php` | GET/POST | Admin operations |

### Sample API Requests

**Register New User:**
```bash
curl -X POST http://localhost:8080/backend/register.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "name=John Doe&age=28&gender=Male&blood_group=O+&contact=+1-555-0101&location=New York&email=john@example.com&password=password123"
```

**Search for Blood:**
```bash
curl "http://localhost:8080/backend/search.php?blood_group=O+&location=New York&search_type=both"
```

**Login User:**
```bash
curl -X POST http://localhost:8080/backend/login.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=john.doe@email.com&password=password123"
```

## 🎯 Production Deployment

### Security Checklist
1. **Change all default passwords**
2. **Enable HTTPS/SSL certificates**
3. **Use environment variables for sensitive data**
4. **Implement rate limiting**
5. **Set up regular database backups**
6. **Configure proper firewall rules**
7. **Enable logging and monitoring**

### Production Environment Variables
```bash
# Create .env file for production
DB_HOST=your-production-db-host
DB_USER=your-production-db-user
DB_PASSWORD=your-secure-password
DB_NAME=blood_donation_prod
```

### Docker Production Setup
```yaml
# docker-compose.prod.yml
version: '3.8'
services:
  web:
    build: .
    restart: unless-stopped
    environment:
      - DB_HOST=${DB_HOST}
      - DB_USER=${DB_USER}
      - DB_PASSWORD=${DB_PASSWORD}
    volumes:
      - ./ssl:/etc/ssl/certs
    ports:
      - "443:443"
      - "80:80"
```

## 📊 Database Schema Details

### Complete Table Structure
```sql
-- Donors table
CREATE TABLE donors (
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
CREATE TABLE requests (
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
CREATE TABLE inventory (
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
```

## 🧪 Testing the System

### Manual Testing Steps
1. **Start the system**: `./start.sh`
2. **Register a new user**: Visit http://localhost:8080/frontend/register.html
3. **Login with test account**: john.doe@email.com / password123
4. **Search for blood**: Use the search functionality with different filters
5. **Submit a blood request**: Test the request submission form
6. **Admin access**: Login to admin panel with admin/admin123

### Automated Testing
```bash
# Test API endpoints
curl -s http://localhost:8080/backend/search.php?search_type=both | jq .
curl -s http://localhost:8080/backend/inventory.php | jq .

# Test database connection
docker exec blooddonation_mysql mysql -u blooddonation -pblooddonation123 -e "SELECT COUNT(*) FROM blood_donation.donors;"
```

## 📞 Support & Maintenance

### Regular Maintenance Tasks
1. **Weekly**: Check system logs and performance
2. **Monthly**: Update Docker images and dependencies
3. **Quarterly**: Review and update security configurations
4. **Annually**: Full system backup and disaster recovery testing

### Monitoring Commands
```bash
# System resource usage
docker stats

# Database size and status
docker exec blooddonation_mysql mysql -u blooddonation -pblooddonation123 -e "
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'blood_donation';"

# Application logs
docker-compose logs -f --tail=100
```

---

## 🎉 Quick Start Summary

1. **Install Docker Desktop**
2. **Navigate to project**: `cd BloodDonationSystem`
3. **Start system**: `./start.sh`
4. **Access app**: http://localhost:8080/frontend/
5. **Test login**: john.doe@email.com / password123

## 🚀 One-Command Setup

```bash
# Complete setup in one command
git clone <repository-url> && cd BloodDonationSystem && chmod +x start.sh && ./start.sh
```

The Blood Donation System is now fully documented and ready for use! 🩸✨

---

**Version**: 1.0  
**Last Updated**: September 2025  
**Compatibility**: Docker Desktop (latest), PHP 8.1, MySQL 8.0

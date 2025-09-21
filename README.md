# 🩸 BloodLink - Blood Donation Management System

A comprehensive blood donation management system built with PHP, MySQL, and modern web technologies. This system facilitates blood donation requests, donor management, and inventory tracking for hospitals and blood banks.

## ✨ Features

### 🎯 Core Functionality
- **Blood Request Management** - Submit and track blood requests with urgency levels
- **Donor Registration** - Register blood donors with complete profiles
- **Smart Search System** - Find donors and blood inventory with advanced filtering
- **Admin Dashboard** - Comprehensive admin panel for managing requests and donors
- **Real-time Monitoring** - Track donation status and inventory levels

### 🔐 Security Features
- Secure user authentication and session management
- Password hashing and validation
- CSRF protection and input sanitization
- Role-based access control

### 🩸 Blood Management
- Support for all blood types (A+, A-, B+, B-, AB+, AB-, O+, O-)
- Blood compatibility checking and donor matching
- Donation eligibility tracking (90-day waiting periods)
- Inventory management with expiration dates

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server
- Composer (optional, for dependencies)

### Installation

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd blood-donation
   ```

2. **Database Setup**
   ```bash
   # Create database and user
   mysql -u root -p
   CREATE DATABASE blood_donation;
   CREATE USER 'blood_user'@'localhost' IDENTIFIED BY 'blood_pass123';
   GRANT ALL PRIVILEGES ON blood_donation.* TO 'blood_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Configure Database Connection**
   
   Update the database configuration in `database/config.php`:
   ```php
   class Config {
       const DB_HOST = 'localhost';
       const DB_NAME = 'blood_donation';
       const DB_USERNAME = 'blood_user';
       const DB_PASSWORD = 'blood_pass123';
   }
   ```

4. **Initialize Database with Sample Data**
   ```bash
   cd database
   php reset_and_populate.php
   cd ..
   ```

## 🎬 Starting the Application

### Option 1: Easy Start (Recommended)
Use the provided startup script for automatic setup and server launch:

```bash
# Make the script executable (one-time setup)
chmod +x start.sh

# Start the application
./start.sh
```

The startup script will:
- ✅ Check PHP installation and version
- ✅ Verify MySQL availability
- 🚀 Start the development server on `localhost:8080`
- 📋 Display all available page URLs
- 👤 Show default admin credentials

### Option 2: Manual Start
If you prefer to start manually:

```bash
# Start PHP built-in server
php -S localhost:8080 -t .
```

### Option 3: Development Mode
For development with error reporting enabled:

```bash
# Start development server with error reporting
php -S localhost:8080 -t . -d display_errors=1
```

## 🌐 Accessing the Application

Once the server is running, access these URLs:

- **🏠 Main Page**: http://localhost:8080/frontend/index.html
- **🔍 Search Page**: http://localhost:8080/frontend/search.html
- **📝 Request Blood**: http://localhost:8080/frontend/request.html
- **👥 Register**: http://localhost:8080/frontend/register.html
- **🔐 Login**: http://localhost:8080/frontend/login.html
- **⚙️ Admin Panel**: http://localhost:8080/frontend/admin.html
- **📊 Monitor**: http://localhost:8080/frontend/monitor.html
- **📞 Contact**: http://localhost:8080/frontend/contact.html

### 🛑 Stopping the Server
Press `Ctrl+C` in the terminal where the server is running to stop it.

## 🗂️ Project Structure

```
blood-donation/
├── README.md                 # Project documentation
├── frontend/                 # Client-side application
│   ├── index.html           # Main landing page
│   ├── search.html          # Blood search interface
│   ├── request.html         # Blood request form
│   ├── admin.html           # Admin dashboard
│   ├── login.html           # User authentication
│   ├── register.html        # User registration
│   ├── monitor.html         # Monitoring dashboard
│   ├── contact.html         # Contact information
│   ├── config.js            # API configuration
│   ├── script.js            # Core JavaScript functionality
│   ├── notifications.js     # Notification system
│   ├── monitor.js           # Monitoring functionality
│   └── style.css            # Application styles
├── backend/                  # Server-side API
│   ├── config.php           # Backend configuration
│   ├── db.php               # Database connection
│   ├── admin.php            # Admin API endpoints
│   ├── search.php           # Search functionality
│   ├── request.php          # Blood request handling
│   ├── register.php         # User registration
│   ├── login.php            # Authentication
│   ├── logout.php           # Session management
│   ├── profile.php          # User profiles
│   ├── donations.php        # Donation tracking
│   ├── inventory.php        # Inventory management
│   ├── monitor.php          # System monitoring
│   └── session_*.php        # Session management
└── database/                 # Database scripts
    ├── config.php           # Database configuration
    ├── schema.sql           # Database schema
    ├── sample_data.sql      # Sample data
    └── reset_and_populate.php # Database setup script
```

## 🔧 Configuration

### Database Configuration
Update `database/config.php` with your database credentials:
```php
class Config {
    const DB_HOST = 'your_host';
    const DB_NAME = 'your_database';
    const DB_USERNAME = 'your_username';
    const DB_PASSWORD = 'your_password';
}
```

### API Configuration
The frontend API configuration is in `frontend/config.js`:
```javascript
const CONFIG = {
    API_BASE_URL: 'http://localhost:8080',
    ENDPOINTS: {
        SEARCH: '/backend/search.php',
        REQUESTS: '/backend/request.php',
        ADMIN: '/backend/admin.php',
        // ... other endpoints
    }
};
```

## 👥 Default Accounts

After running the database setup script, you can use these default accounts:

### Admin Access
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: http://localhost:8080/frontend/admin.html

### Sample Donor Account
- **Email**: `vkdchathuranga@gmail.com`
- **Password**: `dasun123`

## 🩸 Blood Type Compatibility

The system includes comprehensive blood compatibility checking:

| Recipient | Can Receive From |
|-----------|------------------|
| A+ | A+, A-, O+, O- |
| A- | A-, O- |
| B+ | B+, B-, O+, O- |
| B- | B-, O- |
| AB+ | All types (Universal Receiver) |
| AB- | A-, B-, AB-, O- |
| O+ | O+, O- |
| O- | O- only |

## 📊 Sample Data

The system comes with comprehensive sample data including:
- **29 donors** across all blood types
- **47 blood inventory units** with realistic expiration dates
- **5 sample blood requests** with varying urgency levels
- Geographic distribution across major cities

## 🔍 API Endpoints

### Search API
- `GET /backend/search.php?blood_group=A+&search_type=donors`
- `GET /backend/search.php?location=New York&search_type=inventory`

### Request API
- `POST /backend/request.php` - Submit blood request
- `GET /backend/request.php` - Get request status

### Admin API
- `GET /backend/admin.php?action=stats` - Dashboard statistics
- `GET /backend/admin.php?action=list_requests` - All requests
- `PUT /backend/admin.php` - Update request status

## 🚧 Development

### Running in Development Mode
```bash
# Start development server with error reporting
php -S localhost:8080 -t . -d display_errors=1
```

### Database Reset
To reset the database with fresh sample data:
```bash
cd database
php reset_and_populate.php
```

### Debugging
- Check `error_log` for PHP errors
- Use browser developer tools for frontend debugging
- Enable database query logging for SQL debugging

## 🔒 Security Considerations

- **Never use default credentials in production**
- **Change database passwords before deployment**
- **Enable HTTPS in production environments**
- **Regularly update PHP and MySQL versions**
- **Implement proper backup strategies**

## 📝 License

This project is for educational and demonstration purposes. Please ensure compliance with healthcare data regulations (HIPAA, GDPR, etc.) before using in production environments.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For issues and questions:
- **Email**: nsirimanna25@gmail.com
- **Emergency Hotline**: +94-729-710-871

## 🎯 Roadmap

- [ ] Mobile responsive improvements
- [ ] Email notification system
- [ ] SMS integration for donor alerts
- [ ] Advanced reporting dashboard
- [ ] Multi-language support
- [ ] API rate limiting
- [ ] Docker containerization

---

*Last Updated: September 21, 2025*
*Version: 1.0.0*

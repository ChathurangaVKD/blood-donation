# Blood Donation System

A comprehensive web-based blood donation management system built with PHP and MySQL, designed for easy deployment using PHP's built-in server.

## 🩸 Features

- **Donor Management**: Register and manage blood donors with eligibility tracking
- **Blood Inventory**: Track blood units with expiry dates and availability status
- **Search System**: Advanced search for donors and blood inventory by type and location
- **Request Management**: Handle blood requests from hospitals and medical centers
- **Admin Panel**: Administrative interface for system management
- **Real-time Filtering**: Dynamic search with multiple filter options

## 🚀 Quick Start

### Prerequisites

- **PHP 7.4+** with mysqli extension
- **MySQL 5.7+** or MariaDB
- Web browser

### Installation & Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd BloodDonationSystem
   ```

2. **Configure Database**
   - Ensure MySQL is running on `localhost:3306`
   - Default credentials: `root` with no password
   - Edit `database/config.php` if needed

3. **Start the System**
   
   **On Linux/macOS:**
   ```bash
   ./start.sh
   ```
   
   **On Windows:**
   ```batch
   start.bat
   ```

The startup script will:
- Set up the database with comprehensive sample data
- Start the backend server on `http://localhost:8081`
- Start the frontend server on `http://localhost:8080`
- Open the frontend at `http://localhost:8080` in your browser

### How It Works

- **Frontend**: PHP Built-in Server on `localhost:8080` serves HTML/CSS/JS files
- **Backend**: PHP Built-in Server on `localhost:8081` serves the API
- **Database**: MySQL stores all donor, inventory, and request data

Both frontend and backend now run on separate PHP Built-in Server instances!

## 📁 Project Structure

```
BloodDonationSystem/
├── backend/                 # PHP API backend
│   ├── admin.php           # Admin panel
│   ├── db.php              # Database connection
│   ├── donations.php       # Donations management
│   ├── inventory.php       # Blood inventory API
│   ├── login.php           # User authentication
│   ├── register.php        # User registration
│   ├── request.php         # Blood requests API
│   └── search.php          # Search functionality
├── frontend/               # Frontend web interface
│   ├── config.js           # API configuration
│   ├── contact.html        # Contact page
│   ├── index.html          # Main homepage
│   ├── login.html          # Login interface
│   ├── notifications.js    # Notification system
│   ├── register.html       # Registration form
│   ├── request.html        # Blood request form
│   ├── script.js           # Main JavaScript
│   ├── search.html         # Search interface
│   └── style.css           # Styles
├── database/               # Database setup
│   ├── config.php          # Database configuration
│   ├── reset_and_populate.php # Database setup script
│   ├── sample_data.sql     # Sample data
│   └── schema.sql          # Database schema
├── start.sh               # Linux/macOS startup script
├── start.bat              # Windows startup script
└── README.md              # This file
```

## 🔧 Configuration

### Database Settings
Edit `database/config.php` to customize database connection:

```php
class Config {
    const DB_HOST = 'localhost';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = '';
    const DB_NAME = 'blood_donation';
    const DB_PORT = 3306;
}
```

### API Configuration
The frontend connects to the backend via `frontend/config.js`:

```javascript
const CONFIG = {
    API_BASE_URL: 'http://localhost:8081',
    ENDPOINTS: {
        REGISTER: '/register.php',
        LOGIN: '/login.php',
        REQUEST: '/request.php',
        SEARCH: '/search.php',
        // ...
    }
};
```

## 📊 Sample Data

The system includes comprehensive sample data:

- **29 Blood Donors** across all blood types (A+, A-, B+, B-, AB+, AB-, O+, O-)
- **47 Blood Inventory Units** with realistic expiry dates
- **5 Blood Requests** from various medical facilities
- **Multiple Locations** across major US cities

### Blood Type Distribution
- **O+/O-**: Universal donors (8 donors, 13 units)
- **A+/A-**: Common types (8 donors, 12 units)
- **B+/B-**: Less common (6 donors, 10 units)
- **AB+/AB-**: Rare types (7 donors, 9 units)

## 🎯 Usage

### For Donors
1. **Register**: Create account with blood type and contact info
2. **Login**: Access donor dashboard
3. **Update Status**: Track donation history and eligibility

### For Blood Banks
1. **Search**: Find donors by blood type and location
2. **Inventory**: Check available blood units
3. **Requests**: Submit blood requests for patients

### For Administrators
- Access admin panel at: `http://localhost:8081/admin.php`
- Default credentials: `admin` / `admin123`
- Manage donors, inventory, and requests

## 🔍 Search Features

The search system supports:

- **Blood Type Filtering**: Search by specific blood groups
- **Location Search**: Find donors/inventory by city
- **Search Types**: 
  - Donors Only
  - Inventory Only  
  - Both (default)
- **Real-time Results**: Dynamic filtering as you type

## 🏥 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/register.php` | POST | Register new donor |
| `/login.php` | POST | User authentication |
| `/search.php` | GET | Search donors/inventory |
| `/request.php` | POST/GET | Blood requests |
| `/inventory.php` | GET | Blood inventory |
| `/donations.php` | GET | Donation history |
| `/admin.php` | GET | Admin panel |

### Search API Example
```
GET /search.php?blood_group=O-&location=New York&search_type=both
```

## 🛠️ Development

### Manual Setup
If you prefer manual setup instead of using the startup scripts:

1. **Start Backend Server**
   ```bash
   cd backend
   php -S localhost:8081
   ```

2. **Setup Database**
   ```bash
   cd database
   php reset_and_populate.php
   ```

3. **Open Frontend**
   Open `frontend/index.html` in your browser

### Adding Sample Data
To reset and repopulate the database:
```bash
cd database
php reset_and_populate.php
```

## 🔒 Security Notes

- Change default admin password in production
- Update database credentials for production use
- Enable HTTPS for production deployment
- Validate and sanitize all user inputs

## 📱 Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 🆘 Troubleshooting

### Common Issues

**Database Connection Failed**
- Ensure MySQL is running on localhost:3306
- Check database credentials in `database/config.php`
- Verify PHP mysqli extension is installed

**Backend Server Not Starting**
- Check if port 8081 is available
- Ensure PHP is installed and in PATH
- Try running: `php --version`

**Search Not Working**
- Verify backend server is running
- Check browser console for JavaScript errors
- Ensure database has sample data

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Built with ❤️ for the blood donation community**

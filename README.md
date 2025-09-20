# BloodLink - Blood Donation Management System

A comprehensive web-based blood donation management system that connects donors with those in need of blood transfusions.

## 🩸 Features

- **Donor Registration & Management**: Complete donor profile system with blood type, location, and availability tracking
- **Blood Request System**: Submit and manage blood requests with urgency levels and hospital details
- **Smart Donor Search**: Find compatible donors based on blood type, location, and availability
- **User Dashboard**: Personal profile management with donation history and request tracking
- **Admin Panel**: Administrative interface for managing users, requests, and inventory
- **Responsive Design**: Modern, mobile-friendly interface built with Tailwind CSS

## 📁 Project Structure

```
blood-donation/
├── frontend/               # Frontend HTML, CSS, JS files
│   ├── index.html         # Home page
│   ├── login.html         # User login
│   ├── register.html      # Donor registration
│   ├── monitor.html       # User dashboard/profile
│   ├── request.html       # Blood request form
│   ├── search.html        # Donor search
│   ├── contact.html       # Contact information
│   ├── admin.html         # Admin dashboard
│   ├── style.css          # Main stylesheet
│   ├── script.js          # Main JavaScript
│   ├── monitor.js         # Dashboard functionality
│   ├── notifications.js   # Notification system
│   └── config.js          # Frontend configuration
├── backend/               # PHP backend API
│   ├── db.php            # Database connection & utilities
│   ├── config.php        # Backend configuration
│   ├── login.php         # Authentication API
│   ├── logout.php        # Logout functionality
│   ├── register.php      # Registration API
│   ├── session_check.php # Session validation
│   ├── monitor.php       # Dashboard data API
│   ├── request.php       # Blood requests API
│   ├── search.php        # Donor search API
│   ├── admin.php         # Admin functionality
│   ├── donations.php     # Donation tracking
│   ├── inventory.php     # Blood inventory management
│   └── create_demo_user.php # Demo user creation
├── database/             # Database setup files
│   ├── schema.sql        # Database structure
│   ├── sample_data.sql   # Sample data for testing
│   └── config.php        # Database configuration
├── index.php             # Main server router
├── start.sh              # Server startup script (Unix/macOS)
├── README.md             # This file
└── .gitignore            # Git ignore rules
```

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Installation

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd blood-donation
   ```

2. **Set up the database**
   - Start your MySQL server
   - Create a new database named `blood_donation`
   - Import the schema: `mysql -u root -p blood_donation < database/schema.sql`
   - (Optional) Import sample data: `mysql -u root -p blood_donation < database/sample_data.sql`

3. **Configure database connection**
   - Edit `backend/db.php` with your database credentials
   - Update `database/config.php` if needed

4. **Start the server**
   ```bash
   # On macOS/Linux
   chmod +x start.sh
   ./start.sh
   
   # Or manually
   php -S localhost:8080 index.php
   ```

5. **Access the application**
   - Open your browser and go to `http://localhost:8080`
   - Create a new account or use the demo user creation feature

## 🔧 Configuration

### Database Configuration
Edit `backend/db.php` to match your database setup:
```php
$servername = "localhost";
$username = "root";
$password = "your_password";
$dbname = "blood_donation";
```

### Frontend Configuration
Modify `frontend/config.js` for API endpoints and other settings.

## 📊 Database Schema

The system uses the following main tables:
- `donors` - Donor profiles and information
- `requests` - Blood requests and their status
- `donations` - Donation history tracking
- `inventory` - Blood bank inventory management

## 🛡️ Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session-based authentication
- CORS protection for API endpoints
- Input validation and sanitization

## 🎨 Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript ES6+, Tailwind CSS, Font Awesome
- **Backend**: PHP 7.4+, MySQLi
- **Database**: MySQL 5.7+
- **Server**: PHP Built-in Server (Development)

## 🔄 API Endpoints

### Authentication
- `POST /backend/login.php` - User login
- `POST /backend/logout.php` - User logout
- `GET /backend/session_check.php` - Check login status

### User Management
- `POST /backend/register.php` - Register new donor
- `GET /backend/monitor.php` - Get user dashboard data

### Blood Requests
- `POST /backend/request.php` - Submit blood request
- `GET /backend/request.php` - Get user's requests

### Donor Search
- `GET /backend/search.php` - Search for donors

### Admin Functions
- `GET /backend/admin.php` - Admin dashboard data
- `POST /backend/admin.php` - Admin actions

## 🧪 Development

### Creating Demo Data
Run the demo user creation script:
```bash
curl http://localhost:8080/backend/create_demo_user.php
```

### Testing
The system includes comprehensive error handling and logging for easier debugging.

## 📱 Mobile Support

The application is fully responsive and works on:
- Desktop browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Android Chrome)
- Tablet devices

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the MIT License.

## 📞 Support

For support or questions, please contact the development team or create an issue in the repository.

---

**BloodLink** - Connecting lives through blood donation 🩸❤️

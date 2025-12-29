# 🍃 Green Bites - University Canteen Management System

<p align="center">
  <img src="images/logo-icon.svg" alt="Green Bites Logo" width="120">
</p>

<p align="center">
  <strong>A Modern, Secure, and Feature-Rich Online Canteen Ordering System</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white">
  <img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black">
</p>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API Endpoints](#-api-endpoints)
- [Security](#-security)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🌟 Overview

**Green Bites** is a comprehensive university canteen management system designed to streamline food ordering for students. The platform provides a seamless experience for students to browse menus, place orders, track order status, and submit complaints, while offering administrators a powerful dashboard to manage menus, orders, users, and analytics.

### 🎯 Problem Solved
- Eliminates long queues at university canteens
- Provides real-time order tracking
- Enables cashless transactions
- Offers analytics for canteen management
- Improves student satisfaction with easy ordering

---

## ✨ Features

### 👨‍🎓 Student Features
| Feature | Description |
|---------|-------------|
| 🔐 **User Authentication** | Secure signup, login, and password reset with email verification |
| 🍽️ **Browse Menu** | View all food items categorized by Breakfast, Lunch, Snacks, and Drinks |
| 🛒 **Shopping Cart** | Add/remove items, adjust quantities, persistent cart using localStorage |
| 📦 **Order Placement** | Place orders with special instructions and student ID |
| 📜 **Order History** | View all past orders with status tracking |
| 📥 **Order PDF Download** | Generate and download order receipts as PDF |
| ❌ **Cancel Orders** | Cancel pending orders before preparation |
| 📝 **Submit Complaints** | File complaints with image attachments |
| 👤 **Profile Management** | Update personal information and view account stats |

### 👨‍💼 Admin Features
| Feature | Description |
|---------|-------------|
| 📊 **Dashboard** | Real-time statistics, charts, and key metrics |
| 🍕 **Menu Management** | Add, edit, delete menu items with images |
| 📂 **Category Management** | Organize menu items into categories |
| 📦 **Order Management** | View, update order status (Pending → Preparing → Ready → Completed) |
| 👥 **User Management** | View registered users and their activities |
| 📩 **Complaint Management** | View and respond to customer complaints |
| 🎠 **Carousel Management** | Manage homepage promotional banners |
| 📈 **Reports & Analytics** | Generate sales reports and export data |
| 📄 **PDF Export** | Export orders and reports as PDF documents |

---

## 🛠️ Tech Stack

### Backend
- **PHP 8.0+** - Server-side scripting
- **MySQL/MariaDB** - Database management
- **PHPMailer** - Email functionality

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling
- **Bootstrap 5.3** - UI Framework
- **JavaScript ES6+** - Client-side scripting
- **Bootstrap Icons** - Icon library
- **Chart.js** - Data visualization
- **jsPDF** - PDF generation

### Security
- CSRF Protection
- XSS Prevention
- SQL Injection Prevention
- Rate Limiting
- Secure Session Management
- Password Hashing (bcrypt)

---

## 📁 Project Structure

```
Green-Bites/
├── 📁 admin/                    # Admin panel module
│   ├── index.php               # Admin dashboard
│   ├── login.php               # Admin login
│   ├── logout.php              # Admin logout
│   └── 📁 api/                 # Admin API endpoints
│       ├── add_menu.php        # Add menu items
│       ├── update_menu.php     # Update menu items
│       ├── delete_menu.php     # Delete menu items
│       ├── add_category.php    # Category management
│       ├── get_orders.php      # Fetch orders
│       ├── update_order_status.php
│       ├── get_users.php       # User management
│       ├── get_complaints.php  # Complaint handling
│       └── reports.php         # Analytics & reports
│
├── 📁 api/                      # User API endpoints
│   ├── place_order.php         # Order placement
│   ├── cancel_order.php        # Order cancellation
│   └── update_profile.php      # Profile updates
│
├── 📁 auth/                     # Authentication handlers
│   ├── login.php               # User login handler
│   ├── register.php            # User registration
│   ├── logout.php              # Session termination
│   ├── forgot_password.php     # Password reset request
│   ├── reset_password.php      # Password reset handler
│   ├── check_session.php       # Session validation
│   └── check_username.php      # Username availability
│
├── 📁 config/                   # Configuration files
│   ├── security.php            # Security settings & functions
│   ├── email.php               # Email configuration
│   └── mail_helper.php         # Email helper functions
│
├── 📁 css/                      # Stylesheets
│   └── style.css               # Main stylesheet
│
├── 📁 database/                 # Database files
│   ├── green_bites_full.sql    # Complete database dump
│   ├── green_bites_schema.sql  # Schema only
│   └── setup.sql               # Initial setup queries
│
├── 📁 images/                   # Image assets
│
├── 📁 includes/                 # Reusable components
│   ├── header.php              # Navigation header
│   └── footer.php              # Page footer
│
├── 📁 js/                       # JavaScript files
│   ├── cart.js                 # Cart functionality
│   ├── user.js                 # User interactions
│   ├── admin.js                # Admin panel JS
│   └── firebase-config.js      # Firebase configuration
│
├── 📁 logs/                     # Log files
│   ├── 📁 security/            # Security event logs
│   ├── 📁 rate_limits/         # Rate limiting data
│   └── 📁 login_attempts/      # Failed login tracking
│
├── 📁 uploads/                  # User uploads
│   └── 📁 complaints/          # Complaint images
│
├── 📁 vendor/                   # Third-party libraries
│   └── 📁 phpmailer/           # PHPMailer library
│
├── 📄 index.php                 # Main landing page
├── 📄 login.php                 # User login page
├── 📄 signup.php                # User registration page
├── 📄 profile.php               # User profile page
├── 📄 my_orders.php             # Order history page
├── 📄 my_complaints.php         # Complaint history
├── 📄 submit_complaint.php      # Complaint submission
├── 📄 forgot_password.php       # Password recovery
├── 📄 reset_password.php        # Password reset
├── 📄 breakfast.php             # Breakfast menu
├── 📄 lunch.php                 # Lunch menu
├── 📄 snacks.php                # Snacks menu
├── 📄 drinks.php                # Drinks menu
├── 📄 category.php              # Category page
├── 📄 faq.php                   # FAQ page
├── 📄 terms.php                 # Terms of service
├── 📄 privacy.php               # Privacy policy
├── 📄 refund.php                # Refund policy
├── 📄 db.php                    # Database connection
├── 📄 404.html                  # Error page
└── 📄 SECURITY.md               # Security documentation
```

---

## 🚀 Installation

### Prerequisites
- **XAMPP** (PHP 8.0+, MySQL 5.7+/MariaDB 10.4+)
- **Web Browser** (Chrome, Firefox, Edge recommended)
- **Git** (optional)

### Step-by-Step Installation

1. **Clone or Download the Repository**
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/yourusername/Green-Bites.git
   ```
   Or download and extract the ZIP file to `C:\xampp\htdocs\Green-Bites`

2. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL**

3. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create a new database named `green_bites`

4. **Import Database Schema**
   ```sql
   -- Option 1: Full database with sample data
   Import: database/green_bites_full.sql
   
   -- Option 2: Schema only (clean start)
   Import: database/green_bites_schema.sql
   ```

5. **Configure Database Connection**
   Edit `db.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');      // Your MySQL password
   define('DB_NAME', 'green_bites');
   ```

6. **Set Up Email Configuration** (Optional)
   Edit `config/email.php`:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'your-email@gmail.com');
   define('SMTP_PASS', 'your-app-password');
   ```

7. **Access the Application**
   - **Student Portal**: http://localhost/Green-Bites/
   - **Admin Panel**: http://localhost/Green-Bites/admin/

---

## 🗄️ Database Setup

### Database Schema

The application uses the following main tables:

| Table | Description |
|-------|-------------|
| `users` | Student user accounts |
| `admins` | Admin user accounts |
| `categories` | Food categories (Breakfast, Lunch, etc.) |
| `menu_items` | Food items with prices and images |
| `orders` | Customer orders |
| `order_items` | Individual items in orders |
| `complaints` | Customer complaints |
| `carousel_slides` | Homepage promotional banners |
| `password_resets` | Password reset tokens |

### Create Admin User

```sql
INSERT INTO admins (username, email, password, full_name) 
VALUES ('admin', 'admin@greenbites.com', '$2y$10$YOUR_HASHED_PASSWORD', 'Administrator');
```

Or use the setup script:
```bash
php database/admin_setup.sql
```

---

## ⚙️ Configuration

### Security Configuration (`config/security.php`)

```php
// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Max login attempts before lockout
define('MAX_LOGIN_ATTEMPTS', 5);

// Lockout duration (15 minutes)
define('LOGIN_LOCKOUT_TIME', 900);

// Rate limiting (requests per minute)
define('RATE_LIMIT_REQUESTS', 100);
```

### Production Mode

For production deployment, update `config/security.php`:
```php
define('PRODUCTION_MODE', true);
```

---

## 📖 Usage

### For Students

1. **Register** - Create an account with email verification
2. **Login** - Access your account
3. **Browse Menu** - Explore categories and items
4. **Add to Cart** - Select items and quantities
5. **Checkout** - Place order with special instructions
6. **Track Order** - Monitor status in "My Orders"
7. **Download Receipt** - Get PDF invoice

### For Administrators

1. **Login** - Access admin panel at `/admin/`
2. **Dashboard** - View statistics and recent activity
3. **Manage Menu** - Add/edit/delete food items
4. **Process Orders** - Update order statuses
5. **Handle Complaints** - Respond to customer issues
6. **View Reports** - Analyze sales data

---

## 🔌 API Endpoints

### User APIs (`/api/`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/place_order.php` | POST | Place new order |
| `/api/cancel_order.php` | POST | Cancel pending order |
| `/api/update_profile.php` | POST | Update user profile |

### Authentication APIs (`/auth/`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/auth/login.php` | POST | User login |
| `/auth/register.php` | POST | User registration |
| `/auth/logout.php` | GET | User logout |
| `/auth/check_session.php` | GET | Validate session |
| `/auth/forgot_password.php` | POST | Request password reset |

### Admin APIs (`/admin/api/`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/admin/api/get_orders.php` | GET | Fetch all orders |
| `/admin/api/update_order_status.php` | POST | Update order status |
| `/admin/api/add_menu.php` | POST | Add menu item |
| `/admin/api/update_menu.php` | POST | Update menu item |
| `/admin/api/delete_menu.php` | POST | Delete menu item |
| `/admin/api/get_users.php` | GET | Fetch all users |
| `/admin/api/get_complaints.php` | GET | Fetch complaints |

---

## 🔒 Security

Green Bites implements comprehensive security measures:

### Authentication & Authorization
- ✅ Bcrypt password hashing
- ✅ Session-based authentication
- ✅ Session timeout (30 minutes)
- ✅ Brute force protection (5 attempts → 15 min lockout)
- ✅ IP consistency checks

### Data Protection
- ✅ CSRF token protection on all forms
- ✅ Prepared statements (SQL injection prevention)
- ✅ XSS prevention with output encoding
- ✅ Input validation and sanitization

### Security Headers
```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

### File Upload Security
- ✅ MIME type validation
- ✅ File size limits (5MB)
- ✅ Random filename generation
- ✅ PHP execution blocked in uploads folder

For detailed security information, see [SECURITY.md](SECURITY.md).

---

## 📸 Screenshots

### Student Portal
| Home Page | Menu | Cart |
|-----------|------|------|
| Homepage with carousel | Browse food items | Shopping cart panel |

### Admin Dashboard
| Dashboard | Orders | Menu Management |
|-----------|--------|-----------------|
| Statistics overview | Order management | Add/edit menu items |

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Comment complex logic
- Test thoroughly before submitting

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Development Team

**Green Bites Development Team**

- Project developed for university canteen management
- Version: 1.0.0
- Last Updated: December 2025

---

## 📞 Support

For support, please:
- 📧 Email: support@greenbites.com
- 📝 Create an issue on GitHub
- 📖 Check the [FAQ](faq.php) page

---

<p align="center">
  Made with 💚 by Green Bites Team
</p>

# 📁 Green Bites - Source Code Structure

```
src/
│
├── 📂 assets/                      # Static assets (CSS, JS, Images)
│   ├── 📂 css/                     # Stylesheets
│   │   └── style.css               # Main stylesheet
│   │
│   ├── 📂 js/                      # JavaScript files
│   │   ├── cart.js                 # Cart functionality
│   │   ├── admin.js                # Admin panel scripts
│   │   ├── user.js                 # User-side scripts
│   │   └── firebase-config.js      # Firebase configuration
│   │
│   └── 📂 images/                  # Image assets
│       ├── logo.svg                # Logo files
│       └── ...                     # Other images
│
├── 📂 config/                      # Configuration files
│   ├── bootstrap.php               # ⭐ Application bootstrap
│   ├── paths.php                   # Path constants & helpers
│   ├── db.php                      # Database connection
│   ├── security.php                # Security settings
│   ├── email.php                   # SMTP configuration
│   └── mail_helper.php             # Email helpers
│
├── 📂 includes/                    # Reusable components
│   ├── 📂 components/              # UI components
│   │   ├── header.php              # Page header/navbar
│   │   └── footer.php              # Page footer
│   │
│   └── 📂 helpers/                 # Helper functions
│       └── ...
│
├── 📂 modules/                     # Application modules
│   │
│   ├── 📂 auth/                    # 🔐 Authentication module
│   │   │
│   │   ├── 📂 views/               # Login/Signup pages (HTML forms)
│   │   │   ├── login.php           # Login page
│   │   │   ├── signup.php          # Registration page
│   │   │   ├── forgot_password.php # Forgot password page
│   │   │   └── reset_password.php  # Reset password page
│   │   │
│   │   └── 📂 handlers/            # API endpoints (form processors)
│   │       ├── login.php           # Login handler
│   │       ├── register.php        # Registration handler
│   │       ├── logout.php          # Logout handler
│   │       ├── forgot_password.php # Password reset request
│   │       ├── reset_password.php  # Password reset execution
│   │       ├── check_session.php   # Session validation
│   │       └── check_username.php  # Username availability
│   │
│   ├── 📂 admin/                   # 👨‍💼 Admin panel module
│   │   ├── index.php               # Admin dashboard
│   │   ├── login.php               # Admin login page
│   │   ├── logout.php              # Admin logout
│   │   │
│   │   └── 📂 api/                 # Admin API endpoints
│   │       ├── add_menu.php
│   │       ├── update_menu.php
│   │       ├── delete_menu.php
│   │       ├── add_category.php
│   │       ├── update_category.php
│   │       ├── delete_category.php
│   │       ├── update_order_status.php
│   │       ├── get_complaints.php
│   │       ├── mark_complaint_seen.php
│   │       ├── get_users.php
│   │       ├── carousel.php
│   │       ├── reports.php
│   │       └── middleware.php
│   │
│   ├── 📂 api/                     # 🔌 Public API endpoints
│   │   ├── place_order.php         # Place new order
│   │   └── update_profile.php      # Update user profile
│   │
│   ├── 📂 user/                    # 👤 User module
│   │   ├── profile.php             # User profile page
│   │   └── my_orders.php           # Order history page
│   │
│   └── 📂 pages/                   # 📄 Content pages
│       │
│       ├── index.php               # Main landing page
│       │
│       ├── 📂 menu/                # 🍔 Menu/Category pages
│       │   ├── breakfast.php       # Breakfast items
│       │   ├── lunch.php           # Lunch items
│       │   ├── snacks.php          # Snacks items
│       │   ├── drinks.php          # Drinks items
│       │   └── category.php        # Generic category page
│       │
│       ├── 📂 policies/            # 📋 Policy pages
│       │   ├── terms.php           # Terms & conditions
│       │   ├── privacy.php         # Privacy policy
│       │   └── refund.php          # Refund policy
│       │
│       └── 📂 support/             # 💬 Support pages
│           ├── faq.php             # FAQ page
│           └── submit_complaint.php # Complaint form
│
├── 📂 database/                    # Database files
│   ├── green_bites_schema.sql
│   ├── green_bites_full.sql
│   └── ...
│
└── 📂 vendor/                      # Third-party libraries
    └── 📂 phpmailer/
```

---

## 🚀 How to Use

### Include Bootstrap (Recommended)

```php
<?php
// This single include loads: paths, security, and database
require_once __DIR__ . '/src/config/bootstrap.php';

// Now you can use:
echo CSS_URL;        // http://localhost/Green-Bites/src/assets/css
echo IMAGES_URL;     // http://localhost/Green-Bites/src/assets/images
echo AUTH_URL;       // http://localhost/Green-Bites/src/modules/auth
?>
```

### Use Asset Helper Function

```php
<link rel="stylesheet" href="<?php echo asset('css', 'style.css'); ?>">
<script src="<?php echo asset('js', 'cart.js'); ?>"></script>
<img src="<?php echo asset('images', 'logo.svg'); ?>">
```

### Use Module URL Helper

```php
<a href="<?php echo moduleUrl('auth', 'login.php'); ?>">Login</a>
<a href="<?php echo moduleUrl('user', 'profile.php'); ?>">Profile</a>
<form action="<?php echo moduleUrl('api', 'place_order.php'); ?>">
```

### Include Components

```php
<?php component('header'); ?>  <!-- Includes src/includes/components/header.php -->
<?php component('footer'); ?>  <!-- Includes src/includes/components/footer.php -->
```

---

## 📝 Path Constants Available

| Constant | Description | Example Value |
|----------|-------------|---------------|
| `ROOT_PATH` | Project root folder | `E:\xampp\htdocs\Green-Bites` |
| `SRC_PATH` | Source folder | `E:\xampp\htdocs\Green-Bites\src` |
| `CONFIG_PATH` | Config folder | `src/config` |
| `MODULES_PATH` | Modules folder | `src/modules` |
| `ASSETS_PATH` | Assets folder | `src/assets` |
| `CSS_URL` | CSS URL | `http://localhost/Green-Bites/src/assets/css` |
| `JS_URL` | JS URL | `http://localhost/Green-Bites/src/assets/js` |
| `IMAGES_URL` | Images URL | `http://localhost/Green-Bites/src/assets/images` |
| `AUTH_URL` | Auth module URL | `http://localhost/Green-Bites/src/modules/auth` |
| `ADMIN_URL` | Admin module URL | `http://localhost/Green-Bites/src/modules/admin` |

---

## ✅ Module Organization

| Module | Purpose | Key Files |
|--------|---------|-----------|
| `auth` | User authentication | login, register, logout, password reset |
| `admin` | Admin panel | dashboard, menu management, orders |
| `admin/api` | Admin API endpoints | CRUD operations, reports |
| `api` | Public API | order placement, profile update |
| `user` | User features | profile, order history |
| `pages` | Content pages | category pages, policies |

---

*Green Bites - University Canteen Management System © 2024*

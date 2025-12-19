<?php
/**
 * Email Configuration for Green Bites
 * ------------------------------------
 * Configure your SMTP settings here for sending emails
 * 
 * For Gmail: 
 * - Enable 2-Factor Authentication
 * - Create App Password: https://myaccount.google.com/apppasswords
 * - Use the app password below
 * 
 * For other providers, use their SMTP settings
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');        // SMTP server (Gmail: smtp.gmail.com)
define('SMTP_PORT', 587);                      // SMTP port (Gmail: 587 for TLS, 465 for SSL)
define('SMTP_SECURE', 'tls');                  // Encryption: 'tls' or 'ssl'
define('SMTP_AUTH', true);                     // Enable SMTP authentication

// Email Credentials - UPDATE THESE WITH YOUR EMAIL CREDENTIALS
define('SMTP_USERNAME', 'sajjadmahmudsuton@gmail.com');     // Your email address
define('SMTP_PASSWORD', 'sbzllmcscbngurfd');         // Your app password (NOT regular password)

// Sender Information
define('MAIL_FROM_EMAIL', 'sajjadmahmudsuton@gmail.com');   // From email address
define('MAIL_FROM_NAME', 'Green Bites');              // From name

// Site URL for links
define('SITE_URL', 'http://localhost/Green-Bites');   // Your website URL

// Email Templates Settings
define('MAIL_REPLY_TO', 'noreply@greenbites.com');    // Reply-to email
define('MAIL_DEBUG', 0);                               // Debug level (0=off, 1=client, 2=server)

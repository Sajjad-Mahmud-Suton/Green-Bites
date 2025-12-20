<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ ██████╗ ███████╗███████╗███╗   ██╗    ██████╗ ██╗████████╗███████╗║
 * ║  ██╔════╝ ██╔══██╗██╔════╝██╔════╝████╗  ██║    ██╔══██╗██║╚══██╔══╝██╔════╝║
 * ║  ██║  ███╗██████╔╝█████╗  █████╗  ██╔██╗ ██║    ██████╔╝██║   ██║   █████╗  ║
 * ║  ██║   ██║██╔══██╗██╔══╝  ██╔══╝  ██║╚██╗██║    ██╔══██╗██║   ██║   ██╔══╝  ║
 * ║  ╚██████╔╝██║  ██║███████╗███████╗██║ ╚████║    ██████╔╝██║   ██║   ███████╗║
 * ║   ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝  ╚═══╝    ╚═════╝ ╚═╝   ╚═╝   ╚══════╝║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: email.php                                                          ║
 * ║  PATH: /config/email.php                                                  ║
 * ║  DESCRIPTION: SMTP email configuration for Green Bites                    ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. SMTP Server Settings                                                ║
 * ║    2. Email Credentials                                                   ║
 * ║    3. Sender Information                                                  ║
 * ║    4. Email Template Settings                                             ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SETUP INSTRUCTIONS:                                                      ║
 * ║    For Gmail:                                                             ║
 * ║    - Enable 2-Factor Authentication                                       ║
 * ║    - Create App Password: https://myaccount.google.com/apppasswords       ║
 * ║    - Use the app password below                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: SMTP SERVER SETTINGS
   ═══════════════════════════════════════════════════════════════════════════ */

define('SMTP_HOST', 'smtp.gmail.com');        // SMTP server (Gmail: smtp.gmail.com)
define('SMTP_PORT', 587);                      // SMTP port (Gmail: 587 for TLS, 465 for SSL)
define('SMTP_SECURE', 'tls');                  // Encryption: 'tls' or 'ssl'
define('SMTP_AUTH', true);                     // Enable SMTP authentication


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: EMAIL CREDENTIALS
   ═══════════════════════════════════════════════════════════════════════════ */

// ⚠️ UPDATE THESE WITH YOUR EMAIL CREDENTIALS
define('SMTP_USERNAME', 'sajjadmahmudsuton@gmail.com');     // Your email address
define('SMTP_PASSWORD', 'sbzllmcscbngurfd');                // Your app password (NOT regular password)


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: SENDER INFORMATION
   ═══════════════════════════════════════════════════════════════════════════ */

define('MAIL_FROM_EMAIL', 'sajjadmahmudsuton@gmail.com');   // From email address
define('MAIL_FROM_NAME', 'Green Bites');                    // From name


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 4: EMAIL TEMPLATE SETTINGS
   ═══════════════════════════════════════════════════════════════════════════ */

// Site URL for links
define('SITE_URL', 'http://localhost/Green-Bites');         // Your website URL

// Email Templates Settings
define('MAIL_REPLY_TO', 'noreply@greenbites.com');          // Reply-to email
define('MAIL_DEBUG', 0);                                     // Debug level (0=off, 1=client, 2=server)

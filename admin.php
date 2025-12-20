<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                       GREEN BITES - ADMIN ENTRY POINT                     ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * 
 * This file redirects to the admin login page
 * Access: http://localhost/Green-Bites/admin.php
 */

// Redirect to admin login page
header('Location: src/modules/admin/login.php');
exit;

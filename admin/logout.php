<?php
session_start();

// Clear admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_username']);

// Redirect to login
header('Location: login.php');
exit;

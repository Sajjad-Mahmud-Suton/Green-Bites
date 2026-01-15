<?php
/**
 * Admin Dashboard - Secure Version
 */

require_once __DIR__ . '/../config/security.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Set security headers
setSecurityHeaders();

require_once __DIR__ . '/../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Session timeout check (30 minutes)
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// IP consistency check
if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== getClientIP()) {
    securityLog('admin_session_hijack', 'Possible session hijack attempt', [
        'session_ip' => $_SESSION['admin_ip'],
        'current_ip' => getClientIP()
    ]);
    session_unset();
    session_destroy();
    header('Location: login.php?error=security');
    exit;
}

// Update last activity
$_SESSION['admin_login_time'] = time();

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Get statistics
$stats = [];

// Total menu items
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items");
$stats['menu_items'] = mysqli_fetch_assoc($result)['count'];

// Total orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = mysqli_fetch_assoc($result)['count'];

// Pending orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
$stats['pending_orders'] = mysqli_fetch_assoc($result)['count'];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$stats['total_users'] = mysqli_fetch_assoc($result)['count'];

// Total revenue
$result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE status != 'Cancelled'");
$stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;

// Today's orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
$stats['today_orders'] = mysqli_fetch_assoc($result)['count'];

// Unseen complaints count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE is_seen = 0");
$stats['new_complaints'] = mysqli_fetch_assoc($result)['count'];

// Total complaints
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints");
$stats['total_complaints'] = mysqli_fetch_assoc($result)['count'];

// Low stock items (quantity <= 5)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity <= 5 AND quantity > 0");
$stats['low_stock'] = mysqli_fetch_assoc($result)['count'];

// Out of stock items (quantity = 0)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE quantity = 0");
$stats['out_of_stock'] = mysqli_fetch_assoc($result)['count'];

// Get categories
$categories = [];
$catResult = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row;
}

// Get recent orders
$recentOrders = [];
$orderResult = mysqli_query($conn, "SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($orderResult)) {
    $recentOrders[] = $row;
}

// Get all menu items
$menuItems = [];
$menuResult = mysqli_query($conn, "SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.id DESC");
while ($row = mysqli_fetch_assoc($menuResult)) {
    $menuItems[] = $row;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Green Bites</title>
  <link rel="icon" type="image/svg+xml" href="../images/logo-icon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
  <style>
    :root {
      --primary: #16a34a;
      --primary-dark: #15803d;
      --sidebar-width: 260px;
    }
    * { box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #f1f5f9;
      margin: 0;
      min-height: 100vh;
    }
    
    /* Sidebar */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
      color: #fff;
      z-index: 1000;
      overflow-y: auto;
    }
    .sidebar-brand {
      padding: 24px 20px;
      font-size: 1.3rem;
      font-weight: 700;
      color: #4ade80;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 12px;
      text-align: center;
    }
    .sidebar-logo {
      height: 55px;
      width: auto;
      filter: drop-shadow(0 0 10px rgba(74, 222, 128, 0.5));
    }
    .sidebar-brand-text {
      font-size: 1.2rem;
      letter-spacing: 1px;
    }
    .sidebar-brand-text .text-green {
      color: #4ade80;
    }
    .sidebar-brand-text .text-white {
      color: #ffffff;
    }
    .sidebar-nav {
      padding: 20px 0;
    }
    .nav-section {
      padding: 10px 20px 5px;
      font-size: 0.75rem;
      text-transform: uppercase;
      color: #64748b;
      letter-spacing: 1px;
    }
    .nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: #94a3b8;
      text-decoration: none;
      transition: all 0.2s;
      border-left: 3px solid transparent;
    }
    .nav-link:hover, .nav-link.active {
      background: rgba(255,255,255,0.05);
      color: #fff;
      border-left-color: var(--primary);
    }
    .nav-link i {
      font-size: 1.2rem;
      width: 24px;
    }
    .nav-link .badge {
      margin-left: auto;
    }
    
    /* Main Content */
    .main-content {
      margin-left: var(--sidebar-width);
      min-height: 100vh;
    }
    
    /* Top Header */
    .top-header {
      background: #fff;
      padding: 16px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .page-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #1e293b;
      margin: 0;
    }
    .admin-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .admin-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }
    
    /* Content Area */
    .content-area {
      padding: 30px;
    }
    
    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: #fff;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-3px);
    }
    @keyframes statPulse {
      0% { transform: scale(1); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
      50% { transform: scale(1.02); box-shadow: 0 6px 20px rgba(34, 197, 94, 0.2); }
      100% { transform: scale(1); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    }
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.5; }
      100% { opacity: 1; }
    }
    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 15px;
    }
    .stat-icon.green { background: #dcfce7; color: #16a34a; }
    .stat-icon.blue { background: #dbeafe; color: #2563eb; }
    .stat-icon.orange { background: #ffedd5; color: #ea580c; }
    .stat-icon.purple { background: #f3e8ff; color: #9333ea; }
    .text-purple { color: #9333ea; }
    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
    }
    .stat-label {
      color: #64748b;
      font-size: 0.9rem;
    }
    
    /* Cards */
    .card-custom {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      border: none;
      overflow: hidden;
    }
    .card-custom .card-header {
      background: #fff;
      border-bottom: 1px solid #e2e8f0;
      padding: 20px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .card-custom .card-header h5 {
      margin: 0;
      font-weight: 600;
      color: #1e293b;
    }
    .card-custom .card-body {
      padding: 24px;
    }
    
    /* Tables */
    .table-custom {
      margin: 0;
    }
    .table-custom th {
      background: #f8fafc;
      color: #64748b;
      font-weight: 600;
      font-size: 0.85rem;
      text-transform: uppercase;
      padding: 14px 16px;
      border: none;
    }
    .table-custom td {
      padding: 16px;
      vertical-align: middle;
      border-bottom: 1px solid #f1f5f9;
    }
    .table-custom tbody tr:hover {
      background: #f8fafc;
    }
    
    /* Status badges */
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-processing { background: #dbeafe; color: #2563eb; }
    .status-completed { background: #d1fae5; color: #059669; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }
    
    /* Buttons */
    .btn-primary-custom {
      background: var(--primary);
      border: none;
      padding: 10px 20px;
      border-radius: 10px;
      font-weight: 500;
      transition: all 0.2s;
    }
    .btn-primary-custom:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
    }
    .btn-action {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: none;
      transition: all 0.2s;
    }
    .btn-action.edit { background: #dbeafe; color: #2563eb; }
    .btn-action.edit:hover { background: #2563eb; color: #fff; }
    .btn-action.delete { background: #fee2e2; color: #dc2626; }
    .btn-action.delete:hover { background: #dc2626; color: #fff; }
    
    /* Menu item image */
    .menu-item-img {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      object-fit: cover;
      background: #f1f5f9;
    }
    
    /* Sections */
    .section-tab {
      display: none;
    }
    .section-tab.active {
      display: block;
    }
    
    /* Modal custom */
    .modal-content {
      border: none;
      border-radius: 20px;
    }
    .modal-header {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: #fff;
      border-radius: 20px 20px 0 0;
      padding: 20px 24px;
    }
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
    }
    
    /* Alert toast */
    .alert-toast {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
    }
    
    /* Search */
    .search-box {
      position: relative;
    }
    .search-box input {
      padding-left: 40px;
      border-radius: 10px;
      border: 2px solid #e2e8f0;
    }
    .search-box input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    .search-box i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s;
      }
      .sidebar.show {
        transform: translateX(0);
      }
      .main-content {
        margin-left: 0;
      }
    }
    
    /* Reports Section Styles */
    .report-stat-card {
      background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
      border-radius: 16px;
      padding: 24px;
      display: flex;
      align-items: center;
      gap: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }
    .report-stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    .report-stat-card.today {
      border-left: 4px solid #22c55e;
    }
    .report-stat-card.week {
      border-left: 4px solid #3b82f6;
    }
    .report-stat-card.month {
      border-left: 4px solid #f59e0b;
    }
    .report-stat-card.year {
      border-left: 4px solid #8b5cf6;
    }
    .report-stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }
    .report-stat-card.today .report-stat-icon {
      background: rgba(34, 197, 94, 0.15);
      color: #22c55e;
    }
    .report-stat-card.week .report-stat-icon {
      background: rgba(59, 130, 246, 0.15);
      color: #3b82f6;
    }
    .report-stat-card.month .report-stat-icon {
      background: rgba(245, 158, 11, 0.15);
      color: #f59e0b;
    }
    .report-stat-card.year .report-stat-icon {
      background: rgba(139, 92, 246, 0.15);
      color: #8b5cf6;
    }
    .report-stat-content h6 {
      font-size: 0.85rem;
      color: #64748b;
      margin: 0 0 4px;
      font-weight: 500;
    }
    .report-stat-content h3 {
      font-size: 1.75rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0;
    }
    .report-stat-content small {
      color: #94a3b8;
      font-size: 0.8rem;
    }
    
    /* Growth indicators */
    #revenueGrowth.positive, #orderGrowth.positive { color: #22c55e; }
    #revenueGrowth.negative, #orderGrowth.negative { color: #ef4444; }
    #revenueGrowth.positive i, #orderGrowth.positive i { color: #22c55e; }
    #revenueGrowth.negative i, #orderGrowth.negative i { color: #ef4444; }
    
    /* Chart containers - fixed heights */
    #monthlyRevenueChart { max-height: 280px !important; }
    #dailyRevenueChart { max-height: 180px !important; }
    #orderStatusChart { max-height: 220px !important; }
    #hourlyChart { max-height: 180px !important; }
    
    /* Reports tables compact */
    #reports .table-custom tbody { max-height: 300px; overflow-y: auto; display: block; }
    #reports .table-custom thead, #reports .table-custom tbody tr { display: table; width: 100%; table-layout: fixed; }
  </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="../images/logo-icon.svg" alt="Green Bites" class="sidebar-logo">
    <span class="sidebar-brand-text"><span class="text-green">Green</span> <span class="text-white">Bites</span></span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <a href="#" class="nav-link active" data-section="dashboard">
      <i class="bi bi-grid-1x2-fill"></i>
      Dashboard
    </a>
    
    <div class="nav-section">Management</div>
    <a href="#" class="nav-link" data-section="menu">
      <i class="bi bi-egg-fried"></i>
      Menu Items
      <?php if ($stats['out_of_stock'] > 0): ?>
        <span class="badge bg-danger" title="<?php echo $stats['out_of_stock']; ?> out of stock"><?php echo $stats['out_of_stock']; ?> OOS</span>
      <?php elseif ($stats['low_stock'] > 0): ?>
        <span class="badge bg-warning text-dark" title="<?php echo $stats['low_stock']; ?> low stock"><?php echo $stats['low_stock']; ?> Low</span>
      <?php else: ?>
        <span class="badge bg-success"><?php echo $stats['menu_items']; ?></span>
      <?php endif; ?>
    </a>
    <a href="#" class="nav-link" data-section="profits">
      <i class="bi bi-currency-dollar"></i>
      Profit Dashboard
      <span class="badge bg-success"><i class="bi bi-graph-up-arrow"></i></span>
    </a>
    <a href="#" class="nav-link" data-section="orders">
      <i class="bi bi-bag-check"></i>
      Orders
      <?php if ($stats['pending_orders'] > 0): ?>
        <span class="badge bg-warning text-dark"><?php echo $stats['pending_orders']; ?></span>
      <?php endif; ?>
    </a>
    <a href="#" class="nav-link" data-section="users">
      <i class="bi bi-people"></i>
      Users
    </a>
    <a href="#" class="nav-link" data-section="complaints">
      <i class="bi bi-chat-dots"></i>
      Complaints
      <?php if ($stats['new_complaints'] > 0): ?>
        <span class="badge bg-danger" id="complaintsBadge"><?php echo $stats['new_complaints']; ?></span>
      <?php else: ?>
        <span class="badge bg-danger" id="complaintsBadge" style="display:none;">0</span>
      <?php endif; ?>
    </a>
    
    <div class="nav-section">Settings</div>
    <a href="#" class="nav-link" data-section="reports">
      <i class="bi bi-graph-up-arrow"></i>
      Reports & Analytics
    </a>
    <a href="#" class="nav-link" data-section="categories">
      <i class="bi bi-tag"></i>
      Categories
    </a>
    <a href="#" class="nav-link" data-section="carousel">
      <i class="bi bi-images"></i>
      Carousel Slides
    </a>
    <a href="logout.php" class="nav-link text-danger">
      <i class="bi bi-box-arrow-left"></i>
      Logout
    </a>
  </nav>
</aside>

<!-- Main Content -->
<main class="main-content">
  <!-- Top Header -->
  <header class="top-header">
    <h1 class="page-title" id="pageTitle">Dashboard</h1>
    <div class="admin-info">
      <span class="text-muted">Welcome,</span>
      <div class="admin-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
      <strong><?php echo htmlspecialchars($admin_name); ?></strong>
    </div>
  </header>

  <!-- Alert Container -->
  <div id="alertContainer"></div>

  <!-- Content Area -->
  <div class="content-area">
    
    <!-- Dashboard Section -->
    <div id="dashboard" class="section-tab active">
      <!-- Stats -->
      <div class="stats-grid" id="statsGrid">
        <div class="stat-card" id="stat-menu-items">
          <div class="stat-icon green"><i class="bi bi-egg-fried"></i></div>
          <div class="stat-value"><?php echo $stats['menu_items']; ?></div>
          <div class="stat-label">Menu Items</div>
        </div>
        <div class="stat-card" id="stat-total-orders">
          <div class="stat-icon blue"><i class="bi bi-bag-check"></i></div>
          <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card" id="stat-pending-orders">
          <div class="stat-icon orange"><i class="bi bi-clock-history"></i></div>
          <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
          <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-card" id="stat-total-users">
          <div class="stat-icon purple"><i class="bi bi-people"></i></div>
          <div class="stat-value"><?php echo $stats['total_users']; ?></div>
          <div class="stat-label">Registered Users</div>
        </div>
        <div class="stat-card" id="stat-total-revenue">
          <div class="stat-icon green"><i class="bi bi-currency-dollar"></i></div>
          <div class="stat-value">৳<?php echo number_format($stats['total_revenue'], 0); ?></div>
          <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card" id="stat-today-orders">
          <div class="stat-icon blue"><i class="bi bi-calendar-check"></i></div>
          <div class="stat-value"><?php echo $stats['today_orders']; ?></div>
          <div class="stat-label">Today's Orders</div>
        </div>
        <div class="stat-card" id="stat-stock-alerts" style="<?php echo $stats['out_of_stock'] > 0 ? 'border-left: 4px solid #ef4444;' : ($stats['low_stock'] > 0 ? 'border-left: 4px solid #f59e0b;' : 'display:none;'); ?>">
          <div class="stat-icon" style="<?php echo $stats['out_of_stock'] > 0 ? 'background: #fee2e2; color: #ef4444;' : 'background: #fef3c7; color: #f59e0b;'; ?>">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <div class="stat-value"><?php echo $stats['out_of_stock'] + $stats['low_stock']; ?></div>
          <div class="stat-label">
            <span class="text-danger stockout-label"><?php echo $stats['out_of_stock'] > 0 ? $stats['out_of_stock'] . ' Stockout' : ''; ?></span>
            <span class="text-warning lowstock-label"><?php echo $stats['low_stock'] > 0 ? $stats['low_stock'] . ' Low Stock' : ''; ?></span>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="card-custom">
        <div class="card-header">
          <h5><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
          <a href="#" class="btn btn-sm btn-primary-custom" data-section="orders">View All</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-custom">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Bill No</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($recentOrders, 0, 5) as $order): 
                $items = json_decode($order['items'], true) ?: [];
                $itemCount = count($items);
              ?>
              <tr>
                <td><strong>#<?php echo $order['id']; ?></strong></td>
                <td><span class="badge bg-success"><?php echo htmlspecialchars($order['bill_number'] ?? '-'); ?></span></td>
                <td><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></td>
                <td><?php echo $itemCount; ?> item(s)</td>
                <td><strong>৳<?php echo number_format($order['total_price'], 0); ?></strong></td>
                <td>
                  <span class="status-badge status-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                    <?php echo $order['status'] ?? 'Pending'; ?>
                  </span>
                </td>
                <td><?php echo date('M j, g:i A', strtotime($order['order_date'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Menu Items Section -->
    <div id="menu" class="section-tab">
      <div class="card-custom">
        <div class="card-header">
          <h5><i class="bi bi-egg-fried me-2"></i>Menu Items</h5>
          <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#menuModal" onclick="openAddMenuModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Item
          </button>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="menuSearch" placeholder="Search menu items...">
              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select" id="categoryFilter">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-custom" id="menuTable">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Buying</th>
                  <th>Selling</th>
                  <th>Profit</th>
                  <th>Discount</th>
                  <th>Stock</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($menuItems as $item): 
                  $originalPrice = floatval($item['price']);
                  $buyingPrice = floatval($item['buying_price'] ?? 0);
                  $discountPercent = intval($item['discount_percent'] ?? 0);
                  $finalPrice = $discountPercent > 0 ? $originalPrice - ($originalPrice * $discountPercent / 100) : $originalPrice;
                  $hasDiscount = $discountPercent > 0;
                  $profitPerItem = $finalPrice - $buyingPrice;
                  $profitMargin = $buyingPrice > 0 ? round(($profitPerItem / $buyingPrice) * 100, 1) : 0;
                ?>
                <tr data-id="<?php echo $item['id']; ?>" data-category="<?php echo $item['category_id']; ?>">
                  <td>
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/50'); ?>" 
                         class="menu-item-img" alt="<?php echo htmlspecialchars($item['title']); ?>">
                  </td>
                  <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                  <td><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></td>
                  <td>
                    <small class="text-muted">৳<?php echo number_format($buyingPrice, 0); ?></small>
                  </td>
                  <td>
                    <?php if ($hasDiscount): ?>
                      <strong class="text-success">৳<?php echo number_format($finalPrice, 0); ?></strong>
                      <br><small class="text-muted text-decoration-line-through">৳<?php echo number_format($originalPrice, 0); ?></small>
                    <?php else: ?>
                      <strong>৳<?php echo number_format($originalPrice, 0); ?></strong>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($profitPerItem > 0): ?>
                      <span class="text-success">৳<?php echo number_format($profitPerItem, 0); ?></span>
                      <br><small class="badge bg-success"><?php echo $profitMargin; ?>%</small>
                    <?php elseif ($profitPerItem < 0): ?>
                      <span class="text-danger">৳<?php echo number_format($profitPerItem, 0); ?></span>
                      <br><small class="badge bg-danger">Loss</small>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($hasDiscount): ?>
                      <span class="badge bg-danger"><?php echo $discountPercent; ?>% OFF</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">No Discount</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php 
                    $qty = $item['quantity'] ?? 0;
                    if ($qty == 0): ?>
                      <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Stockout</span>
                    <?php elseif ($qty <= 5): ?>
                      <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i><?php echo $qty; ?> left</span>
                    <?php else: ?>
                      <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i><?php echo $qty; ?> in stock</span>
                    <?php endif; ?>
                    <div class="btn-group btn-group-sm mt-1" role="group">
                      <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)" title="Decrease">
                        <i class="bi bi-dash"></i>
                      </button>
                      <button type="button" class="btn btn-outline-success btn-sm" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)" title="Increase">
                        <i class="bi bi-plus"></i>
                      </button>
                    </div>
                  </td>
                  <td>
                    <button class="btn-action edit" onclick="editMenuItem(<?php echo $item['id']; ?>)" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-action delete" onclick="deleteMenuItem(<?php echo $item['id']; ?>, '<?php echo addslashes($item['title']); ?>')" title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Section -->
    <div id="orders" class="section-tab">
      <div class="card-custom">
        <div class="card-header">
          <h5><i class="bi bi-bag-check me-2"></i>All Orders</h5>
          <button class="btn btn-outline-primary btn-sm" onclick="exportOrdersPDF()">
            <i class="bi bi-file-pdf me-1"></i>Export PDF
          </button>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3">
              <select class="form-select" id="orderStatusFilter">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-custom" id="ordersTable">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Bill No</th>
                  <th>Customer</th>
                  <th>Items</th>
                  <th>Total</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentOrders as $order): 
                  $items = json_decode($order['items'], true) ?: [];
                ?>
                <tr data-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['status'] ?? 'Pending'; ?>">
                  <td><strong>#<?php echo $order['id']; ?></strong></td>
                  <td><span class="badge bg-success"><?php echo htmlspecialchars($order['bill_number'] ?? '-'); ?></span></td>
                  <td>
                    <strong><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></strong>
                    <br><small class="text-muted"><?php echo htmlspecialchars($order['email'] ?? ''); ?></small>
                  </td>
                  <td>
                    <?php foreach ($items as $item): ?>
                      <div class="small"><?php echo htmlspecialchars($item['title'] ?? $item['name'] ?? 'Item'); ?> × <?php echo $item['quantity']; ?></div>
                    <?php endforeach; ?>
                  </td>
                  <td><strong>৳<?php echo number_format($order['total_price'], 0); ?></strong></td>
                  <td><span class="badge bg-info"><?php echo htmlspecialchars($order['payment_method'] ?? 'Pay at Counter'); ?></span></td>
                  <td>
                    <select class="form-select form-select-sm status-select" data-order-id="<?php echo $order['id']; ?>" style="width: 130px;">
                      <option value="Pending" <?php echo ($order['status'] ?? '') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                      <option value="Processing" <?php echo ($order['status'] ?? '') == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                      <option value="Completed" <?php echo ($order['status'] ?? '') == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                      <option value="Delivered" <?php echo ($order['status'] ?? '') == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                      <option value="Cancelled" <?php echo ($order['status'] ?? '') == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                  </td>
                  <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                  <td>
                    <button class="btn-action edit" onclick="viewOrder(<?php echo $order['id']; ?>)" title="View Details">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-action" style="background: #3b82f6;" onclick="printAdminBill(<?php echo $order['id']; ?>)" title="Print Admin Copy">
                      <i class="bi bi-printer"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Section -->
    <div id="users" class="section-tab">
      <div class="card-custom">
        <div class="card-header">
          <h5><i class="bi bi-people me-2"></i>Registered Users</h5>
        </div>
        <div class="card-body p-0">
          <table class="table table-custom" id="usersTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Joined</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <!-- Loaded via AJAX -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Complaints Section -->
    <div id="complaints" class="section-tab">
      <div class="card-custom">
        <div class="card-header">
          <h5><i class="bi bi-chat-dots me-2"></i>Complaints</h5>
          <button class="btn btn-success btn-sm" onclick="markAllComplaintsSeen()" id="markAllSeenBtn">
            <i class="bi bi-check-all me-1"></i>Mark All as Seen
          </button>
        </div>
        <div class="card-body p-0">
          <table class="table table-custom" id="complaintsTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Image</th>
                <th>Status</th>
                <th>Date & Time</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="complaintsTableBody">
              <!-- Loaded via AJAX -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Complaint Response Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="bi bi-chat-dots me-2"></i>Complaint Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="complaintId">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">From</label>
                <p id="complaintFrom" class="mb-0"></p>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Email</label>
                <p id="complaintEmail" class="mb-0"></p>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Message</label>
              <div id="complaintMessage" class="p-3 bg-light rounded"></div>
            </div>
            <div id="complaintImageSection" class="mb-3" style="display:none;">
              <label class="form-label fw-bold">Attached Image</label>
              <div><img id="complaintImage" src="" class="img-fluid rounded" style="max-height: 200px;"></div>
            </div>
            <hr>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Update Status</label>
                <select class="form-select" id="complaintStatus">
                  <option value="pending">Pending</option>
                  <option value="seen">Seen</option>
                  <option value="in_progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                  <option value="closed">Closed</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Admin Response <small class="text-muted">(visible to user)</small></label>
              <textarea class="form-control" id="complaintResponse" rows="4" placeholder="Write your response to the user..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger me-auto" onclick="deleteComplaintFromModal()">
              <i class="bi bi-trash me-1"></i>Delete Complaint
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" onclick="saveComplaintResponse()">
              <i class="bi bi-check-lg me-1"></i>Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Categories Section -->
    <div id="categories" class="section-tab">
      <div class="card-custom">
        <div class="card-header">
          <h5><i class="bi bi-tag me-2"></i>Categories</h5>
          <button class="btn btn-primary-custom" onclick="showAddCategoryModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Category
          </button>
        </div>
        <div class="card-body p-0">
          <table class="table table-custom">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Icon</th>
                <th>Items Count</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $cat): 
                $countResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM menu_items WHERE category_id = " . $cat['id']);
                $itemCount = mysqli_fetch_assoc($countResult)['cnt'];
              ?>
              <tr>
                <td><?php echo $cat['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                <td><i class="bi bi-<?php echo $cat['icon'] ?? 'tag'; ?> fs-5"></i></td>
                <td><?php echo $itemCount; ?> items</td>
                <td>
                  <button class="btn-action edit" onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>', '<?php echo $cat['icon'] ?? 'tag'; ?>', '<?php echo htmlspecialchars(addslashes($cat['description'] ?? '')); ?>')">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn-action delete" onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>')">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Carousel Section -->
    <div id="carousel" class="section-tab">
      <div class="card-custom">
        <div class="card-header">
          <h5><i class="bi bi-images me-2"></i>Carousel Slides</h5>
          <button class="btn btn-primary-custom" onclick="showAddSlideModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Slide
          </button>
        </div>
        <div class="card-body p-0">
          <table class="table table-custom">
            <thead>
              <tr>
                <th width="80">Order</th>
                <th width="100">Preview</th>
                <th>Title</th>
                <th>Description</th>
                <th width="100">Price</th>
                <th width="80">Status</th>
                <th width="120">Actions</th>
              </tr>
            </thead>
            <tbody id="carouselTableBody">
              <?php
              $slidesResult = mysqli_query($conn, "SELECT * FROM carousel_slides ORDER BY sort_order ASC");
              while ($slide = mysqli_fetch_assoc($slidesResult)):
              ?>
              <tr data-id="<?php echo $slide['id']; ?>">
                <td><span class="badge bg-secondary"><?php echo $slide['sort_order']; ?></span></td>
                <td>
                  <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" 
                       class="rounded" style="width: 70px; height: 45px; object-fit: cover;">
                </td>
                <td><strong><?php echo htmlspecialchars($slide['title']); ?></strong></td>
                <td class="text-muted"><?php echo htmlspecialchars(substr($slide['description'], 0, 50)) . (strlen($slide['description']) > 50 ? '...' : ''); ?></td>
                <td><span class="badge bg-success">৳<?php echo number_format($slide['price'], 0); ?></span></td>
                <td>
                  <span class="badge <?php echo $slide['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                    <?php echo $slide['is_active'] ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td>
                  <button class="btn-action edit" onclick="editSlide(<?php echo $slide['id']; ?>)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn-action <?php echo $slide['is_active'] ? 'warning' : 'success'; ?>" 
                          onclick="toggleSlide(<?php echo $slide['id']; ?>)" 
                          title="<?php echo $slide['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                    <i class="bi bi-<?php echo $slide['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                  </button>
                  <button class="btn-action delete" onclick="deleteSlide(<?php echo $slide['id']; ?>, '<?php echo htmlspecialchars(addslashes($slide['title'])); ?>')">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Profit Dashboard Section -->
    <div id="profits" class="section-tab">
      <div class="profits-header mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h5 class="mb-0"><i class="bi bi-currency-dollar text-success me-2"></i>Profit Dashboard</h5>
            <small class="text-muted">Track profit margins, revenue, and investment</small>
          </div>
          <div class="d-flex gap-2">
            <div class="input-group input-group-sm" style="width: 300px;">
              <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
              <input type="date" class="form-control" id="profitDateFrom" value="">
              <span class="input-group-text">to</span>
              <input type="date" class="form-control" id="profitDateTo" value="">
            </div>
            <button class="btn btn-sm btn-outline-success" onclick="refreshProfitData()">
              <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </button>
            <button class="btn btn-sm btn-success" onclick="exportProfitPDF()">
              <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Profit Overview Cards -->
      <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
          <div class="card-custom h-100" style="border-left: 4px solid #22c55e;">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon green me-3"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                  <h6 class="text-muted mb-1">Total Profit</h6>
                  <h3 class="mb-0 text-success" id="totalProfit">৳0</h3>
                  <small class="text-muted" id="profitOrders">0 orders</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card-custom h-100" style="border-left: 4px solid #3b82f6;">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon blue me-3"><i class="bi bi-cash-stack"></i></div>
                <div>
                  <h6 class="text-muted mb-1">Total Revenue</h6>
                  <h3 class="mb-0 text-primary" id="totalRevenue">৳0</h3>
                  <small class="text-muted" id="revenueItems">0 items sold</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card-custom h-100" style="border-left: 4px solid #f59e0b;">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon orange me-3"><i class="bi bi-wallet2"></i></div>
                <div>
                  <h6 class="text-muted mb-1">Total Investment</h6>
                  <h3 class="mb-0 text-warning" id="totalInvestment">৳0</h3>
                  <small class="text-muted" id="investmentNote">Cost of goods sold</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card-custom h-100" style="border-left: 4px solid #8b5cf6;">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="stat-icon purple me-3"><i class="bi bi-percent"></i></div>
                <div>
                  <h6 class="text-muted mb-1">Profit Margin</h6>
                  <h3 class="mb-0 text-purple" id="profitMarginPercent">0%</h3>
                  <small class="text-muted" id="avgProfitItem">৳0 avg/item</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Period Comparison Cards -->
      <div class="row g-2 mb-4">
        <div class="col-md-3">
          <div class="report-stat-card today" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-day"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">Today's Profit</h6>
              <h3 id="todayProfit" style="font-size: 1.3rem;">৳0</h3>
              <small id="todayProfitOrders">0 orders</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="report-stat-card week" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-week"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">This Week</h6>
              <h3 id="weekProfit" style="font-size: 1.3rem;">৳0</h3>
              <small id="weekProfitOrders">0 orders</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="report-stat-card month" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-month"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">This Month</h6>
              <h3 id="monthProfit" style="font-size: 1.3rem;">৳0</h3>
              <small id="monthProfitOrders">0 orders</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="report-stat-card year" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-check"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">This Year</h6>
              <h3 id="yearProfit" style="font-size: 1.3rem;">৳0</h3>
              <small id="yearProfitOrders">0 orders</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Profit Charts -->
      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <div class="card-custom h-100">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Profit Trend (Last 30 Days)</h6>
            </div>
            <div class="card-body py-2">
              <canvas id="profitTrendChart" height="200"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card-custom h-100">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Profit Distribution by Category</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center py-2">
              <canvas id="profitByCategoryChart" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Items & Revenue vs Profit Comparison -->
      <div class="row g-3 mb-4">
        <div class="col-lg-6">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Most Profitable Items</h6>
            </div>
            <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
              <table class="table table-custom table-sm mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th class="text-center">Units Sold</th>
                    <th class="text-end">Profit</th>
                    <th class="text-end">Margin</th>
                  </tr>
                </thead>
                <tbody id="mostProfitableItems">
                  <tr><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-hourglass-split me-2"></i>Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Most Sold Items</h6>
            </div>
            <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
              <table class="table table-custom table-sm mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th class="text-center">Units Sold</th>
                    <th class="text-end">Revenue</th>
                    <th class="text-end">Profit</th>
                  </tr>
                </thead>
                <tbody id="mostSoldItems">
                  <tr><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-hourglass-split me-2"></i>Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Revenue vs Profit vs Investment Chart -->
      <div class="row g-3 mb-4">
        <div class="col-12">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Monthly Revenue vs Profit vs Investment</h6>
            </div>
            <div class="card-body py-2">
              <canvas id="revenueProfitChart" height="120"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Profit Records Table -->
      <div class="row g-3">
        <div class="col-12">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-table me-2"></i>Recent Profit Records</h6>
              <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="profitSearch" placeholder="Search product...">
              </div>
            </div>
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
              <table class="table table-custom table-sm mb-0">
                <thead style="position: sticky; top: 0; background: #f8fafc;">
                  <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Selling</th>
                    <th class="text-end">Buying</th>
                    <th class="text-end">Revenue</th>
                    <th class="text-end">Investment</th>
                    <th class="text-end">Profit</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody id="profitRecordsTable">
                  <tr><td colspan="9" class="text-center text-muted py-3"><i class="bi bi-hourglass-split me-2"></i>Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reports & Analytics Section -->
    <div id="reports" class="section-tab">
      <div class="reports-header mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h5 class="mb-0"><i class="bi bi-graph-up-arrow text-success me-2"></i>Business Analytics</h5>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" onclick="refreshReports()">
              <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </button>
            <button class="btn btn-sm btn-success" onclick="exportReportPDF()">
              <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Stats Cards - Compact -->
      <div class="row g-2 mb-3">
        <div class="col-xl-3 col-md-6">
          <div class="report-stat-card today" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-day"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">Today</h6>
              <h3 id="todayRevenue" style="font-size: 1.3rem;">৳0</h3>
              <small id="todayOrders">0 orders</small>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="report-stat-card week" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-week"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">This Week</h6>
              <h3 id="weekRevenue" style="font-size: 1.3rem;">৳0</h3>
              <small id="weekOrders">0 orders</small>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="report-stat-card month" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-month"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">This Month</h6>
              <h3 id="monthRevenue" style="font-size: 1.3rem;">৳0</h3>
              <small id="monthOrders">0 orders</small>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="report-stat-card year" style="padding: 16px;">
            <div class="report-stat-icon" style="width: 44px; height: 44px; font-size: 1.2rem;"><i class="bi bi-calendar-check"></i></div>
            <div class="report-stat-content">
              <h6 style="font-size: 0.75rem;">This Year</h6>
              <h3 id="yearRevenue" style="font-size: 1.3rem;">৳0</h3>
              <small id="yearOrders">0 orders</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Growth Indicator - Compact single row -->
      <div class="row g-2 mb-3">
        <div class="col-md-4">
          <div class="card-custom">
            <div class="card-body text-center py-2">
              <small class="text-muted">Revenue Growth</small>
              <h4 class="mb-0" id="revenueGrowth">
                <i class="bi bi-graph-up text-success"></i> <span>0%</span>
              </h4>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-custom">
            <div class="card-body text-center py-2">
              <small class="text-muted">Order Growth</small>
              <h4 class="mb-0" id="orderGrowth">
                <i class="bi bi-graph-up text-success"></i> <span>0%</span>
              </h4>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-custom">
            <div class="card-body text-center py-2">
              <small class="text-muted">Avg Order Value</small>
              <h4 class="mb-0 text-primary" id="avgOrderValue">৳0</h4>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row - More Compact -->
      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <div class="card-custom h-100">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Monthly Revenue (Last 12 Months)</h6>
            </div>
            <div class="card-body py-2">
              <canvas id="monthlyRevenueChart" height="200"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card-custom h-100">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Order Status</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center py-2">
              <canvas id="orderStatusChart" height="180"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Revenue Chart - More Compact -->
      <div class="row g-3 mb-4">
        <div class="col-12">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Daily Revenue (Last 30 Days)</h6>
            </div>
            <div class="card-body py-2">
              <canvas id="dailyRevenueChart" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Items & Categories -->
      <div class="row g-3 mb-4">
        <div class="col-lg-6">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Selling Items</h6>
            </div>
            <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
              <table class="table table-custom table-sm mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Revenue</th>
                  </tr>
                </thead>
                <tbody id="topItemsTable">
                  <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-tag me-2"></i>Category Performance</h6>
            </div>
            <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
              <table class="table table-custom table-sm mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th class="text-center">Sold</th>
                    <th class="text-end">Revenue</th>
                  </tr>
                </thead>
                <tbody id="categoryPerfTable">
                  <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Customers & Peak Hours - Compact -->
      <div class="row g-3 mb-3">
        <div class="col-lg-6">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-people me-2"></i>Top Customers</h6>
            </div>
            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
              <table class="table table-custom table-sm mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th class="text-center">Orders</th>
                    <th class="text-end">Spent</th>
                  </tr>
                </thead>
                <tbody id="topCustomersTable">
                  <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card-custom">
            <div class="card-header py-2">
              <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Hourly Distribution</h6>
            </div>
            <div class="card-body py-2">
              <canvas id="hourlyChart" height="120"></canvas>
              <div class="text-center mt-2">
                <span class="badge bg-success" id="peakHourBadge">
                  <i class="bi bi-clock me-1"></i>Peak Hour: Loading...
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</main>

<!-- Menu Item Modal -->
<div class="modal fade" id="menuModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="menuModalTitle"><i class="bi bi-plus-circle me-2"></i>Add Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="menuForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="menuItemId">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Item Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" id="menuName" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Category <span class="text-danger">*</span></label>
              <select class="form-select" name="category_id" id="menuCategory" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label">Buying Price (৳) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="buying_price" id="menuBuyingPrice" min="0" step="0.01" required>
              <small class="text-muted">Cost price</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Selling Price (৳) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="price" id="menuPrice" min="0" step="0.01" required>
              <small class="text-muted">Customer price</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Discount (%) <i class="bi bi-percent text-success"></i></label>
              <input type="number" class="form-control" name="discount_percent" id="menuDiscount" min="0" max="99" value="0">
              <small class="text-muted">0 = No discount</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="quantity" id="menuQuantity" min="0" value="10" required>
              <small class="text-muted">Available stock</small>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Final Price</label>
              <div class="input-group">
                <span class="input-group-text">৳</span>
                <input type="text" class="form-control bg-light" id="menuFinalPrice" readonly>
              </div>
              <small class="text-success" id="discountPreview"></small>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Profit Margin</label>
              <div class="input-group">
                <span class="input-group-text">৳</span>
                <input type="text" class="form-control bg-light" id="menuProfitMargin" readonly>
              </div>
              <small class="text-info" id="profitPercent"></small>
            </div>
            <div class="col-md-4 mb-3 d-flex align-items-end">
              <div id="priceValidation" class="alert alert-sm mb-0 py-1 px-2 d-none"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Image URL</label>
              <input type="url" class="form-control" name="image_url" id="menuImage" placeholder="https://...">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="menuDescription" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="menuSubmitBtn">
            <i class="bi bi-check-lg me-1"></i>Save Item
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="categoryModalTitle"><i class="bi bi-tag me-2"></i>Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="categoryForm">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
          <input type="hidden" name="id" id="categoryId">
          <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" class="form-control" name="name" id="categoryName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Icon (Bootstrap Icons name)</label>
            <input type="text" class="form-control" name="icon" id="categoryIcon" placeholder="e.g. cup-straw, egg-fried">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="categorySubmitBtn">Save Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Carousel Slide Modal -->
<div class="modal fade" id="slideModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="slideModalTitle"><i class="bi bi-images me-2"></i>Add Carousel Slide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="slideForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="slideId">
          
          <div class="row">
            <div class="col-md-8 mb-3">
              <label class="form-label">Slide Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" id="slideTitle" required placeholder="e.g. Biriyani Special">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Price (৳) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="price" id="slidePrice" min="0" step="1" required placeholder="120">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="slideDescription" rows="2" placeholder="Delicious food description..."></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Image URL <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="image_url" id="slideImageUrl" required placeholder="images/food.jpg or https://...">
            <small class="text-muted">Use local path (e.g. images/biriyani.jpg) or full URL</small>
          </div>
          
          <div id="slideImagePreview" class="mb-3" style="display: none;">
            <label class="form-label">Preview</label>
            <div class="border rounded p-2 bg-light text-center">
              <img id="slidePreviewImg" src="" class="rounded" style="max-height: 150px; object-fit: cover;">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Link to Menu Item <span class="text-success">(For direct ordering)</span></label>
            <select class="form-select" name="menu_item_id" id="slideMenuItemId">
              <option value="">-- No linked item (View Menu button) --</option>
              <?php foreach ($menuItems as $item): ?>
                <option value="<?php echo $item['id']; ?>">
                  <?php echo htmlspecialchars($item['title']); ?> - ৳<?php echo number_format($item['price'], 0); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">If linked, "Order Now" will add this item to cart directly</small>
          </div>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Button Text</label>
              <input type="text" class="form-control" name="btn_text" id="slideBtnText" value="Order Now">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Button Link</label>
              <input type="text" class="form-control" name="btn_link" id="slideBtnLink" value="#dealsSection" placeholder="#section or URL">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Sort Order</label>
              <input type="number" class="form-control" name="sort_order" id="slideSortOrder" value="0" min="0">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3 d-flex align-items-end">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_active" id="slideActive" checked>
                <label class="form-check-label" for="slideActive">Active</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="slideSubmitBtn">
            <i class="bi bi-check-lg me-1"></i>Save Slide
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Order View Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Order Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="orderModalBody">
        <div class="text-center py-5">
          <div class="spinner-border text-success"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="printOrderBtnModal">
          <i class="bi bi-printer me-1"></i>Print Admin Copy
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script>
const csrfToken = '<?php echo $csrf_token; ?>';
const menuData = <?php echo json_encode($menuItems); ?>;
const ordersData = <?php echo json_encode($recentOrders); ?>;
let lastComplaintCount = <?php echo $stats['total_complaints']; ?>;

// Check for new complaints every 30 seconds
function checkNewComplaints() {
  fetch('api/get_complaints.php', { credentials: 'same-origin' })
    .then(res => res.json())
    .then(result => {
      if (result.success && result.complaints) {
        const totalCount = result.complaints.length;
        
        // Count unseen complaints
        const unseenComplaints = result.complaints.filter(c => c.is_seen == 0 || c.is_seen === '0');
        const unseenCount = unseenComplaints.length;
        
        // Update badge
        updateComplaintsBadge(unseenCount);
        
        // Show notification if new complaint arrived
        if (totalCount > lastComplaintCount) {
          showNotification('New Complaint!', 'A new complaint has been submitted.');
          lastComplaintCount = totalCount;
        }
      }
    })
    .catch(err => console.error('Complaint check error:', err));
}

// Show browser notification
function showNotification(title, body) {
  // Show toast notification
  showAlert(`🔔 ${title}: ${body}`, 'warning');
  
  // Play notification sound (optional)
  try {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleR0EB5/Y7M9rOCMAN5bb/9lqWCEAPJPZ');
    audio.volume = 0.3;
    audio.play().catch(() => {});
  } catch(e) {}
  
  // Browser notification (if permitted)
  if ('Notification' in window && Notification.permission === 'granted') {
    new Notification(title, { body, icon: '../images/logo-icon.svg' });
  }
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
  Notification.requestPermission();
}

// Start checking for new complaints
setInterval(checkNewComplaints, 15000); // Every 15 seconds (faster)

// Check for orders updates every 8 seconds (faster for real-time feel)
let lastOrdersData = JSON.stringify(ordersData);
function checkOrdersUpdate() {
  fetch('api/get_orders.php', { credentials: 'same-origin' })
    .then(res => res.json())
    .then(result => {
      if (result.success && result.orders) {
        const newData = JSON.stringify(result.orders);
        if (newData !== lastOrdersData) {
          lastOrdersData = newData;
          refreshOrdersTable(result.orders);
          
          // Show notification for new orders
          showAlert('📦 Orders updated!', 'info');
        }
      }
    })
    .catch(err => console.error('Orders check error:', err));
}

// Refresh orders table
function refreshOrdersTable(orders) {
  const tbody = document.querySelector('#ordersTable tbody');
  if (!tbody) return;
  
  tbody.innerHTML = orders.map(order => {
    const items = typeof order.items === 'string' ? JSON.parse(order.items) : order.items;
    const status = order.status || 'Pending';
    
    return `
      <tr data-id="${order.id}" data-status="${status}">
        <td><strong>#${order.id}</strong></td>
        <td><span class="badge bg-success">${order.bill_number || '-'}</span></td>
        <td>
          <strong>${order.full_name || 'Guest'}</strong>
          <br><small class="text-muted">${order.email || ''}</small>
        </td>
        <td>
          ${items.map(item => `<div class="small">${item.title || item.name || 'Item'} × ${item.quantity}</div>`).join('')}
        </td>
        <td><strong>৳${parseFloat(order.total_price).toLocaleString()}</strong></td>
        <td><span class="badge bg-info">${order.payment_method || 'Pay at Counter'}</span></td>
        <td>
          <select class="form-select form-select-sm status-select" data-order-id="${order.id}" style="width: 130px;">
            <option value="Pending" ${status == 'Pending' ? 'selected' : ''}>Pending</option>
            <option value="Processing" ${status == 'Processing' ? 'selected' : ''}>Processing</option>
            <option value="Completed" ${status == 'Completed' ? 'selected' : ''}>Completed</option>
            <option value="Delivered" ${status == 'Delivered' ? 'selected' : ''}>Delivered</option>
            <option value="Cancelled" ${status == 'Cancelled' ? 'selected' : ''}>Cancelled</option>
          </select>
        </td>
        <td>${new Date(order.order_date).toLocaleString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})}</td>
        <td>
          <button class="btn-action edit" onclick="viewOrder(${order.id})" title="View Details">
            <i class="bi bi-eye"></i>
          </button>
          <button class="btn-action" style="background: #3b82f6;" onclick="printAdminBill(${order.id})" title="Print Admin Copy">
            <i class="bi bi-printer"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
  
  // Re-attach status change listeners
  attachStatusListeners();
}

setInterval(checkOrdersUpdate, 8000); // Every 8 seconds (faster)

// Auto-refresh menu/stock data every 20 seconds
function refreshMenuStock() {
  fetch('api/get_menu.php', { credentials: 'same-origin' })
    .then(res => res.json())
    .then(result => {
      if (result.success && result.items) {
        updateMenuTable(result.items);
      }
    })
    .catch(err => console.error('Menu refresh error:', err));
}

function updateMenuTable(items) {
  const tbody = document.querySelector('#menuTable tbody');
  if (!tbody) return;
  
  items.forEach(item => {
    const row = tbody.querySelector(`tr[data-id="${item.id}"]`);
    if (row) {
      // Update quantity
      const qtyCell = row.querySelector('.quantity-value');
      if (qtyCell) {
        const oldQty = parseInt(qtyCell.textContent);
        const newQty = parseInt(item.quantity);
        if (oldQty !== newQty) {
          qtyCell.textContent = newQty;
          qtyCell.style.animation = 'none';
          qtyCell.offsetHeight;
          qtyCell.style.animation = 'pulse 0.5s ease';
          
          // Update badge class
          const badge = qtyCell.closest('.badge');
          if (badge) {
            badge.className = 'badge ' + (newQty <= 0 ? 'bg-danger' : (newQty <= 5 ? 'bg-warning text-dark' : 'bg-success'));
          }
        }
      }
    }
  });
}

setInterval(refreshMenuStock, 20000); // Every 20 seconds

// Dashboard Stats Auto-Refresh
async function refreshDashboardStats() {
  try {
    const response = await fetch('api/get_stats.php');
    const result = await response.json();
    
    if (result.success) {
      const stats = result.stats;
      
      // Update Menu Items
      updateStatCard('stat-menu-items', stats.menu_items);
      
      // Update Total Orders
      updateStatCard('stat-total-orders', stats.total_orders);
      
      // Update Pending Orders
      updateStatCard('stat-pending-orders', stats.pending_orders);
      
      // Update Total Users
      updateStatCard('stat-total-users', stats.total_users);
      
      // Update Total Revenue
      const revenueCard = document.querySelector('#stat-total-revenue .stat-value');
      if (revenueCard) {
        const newRevenue = '৳' + new Intl.NumberFormat('en-US').format(stats.total_revenue);
        if (revenueCard.textContent !== newRevenue) {
          revenueCard.textContent = newRevenue;
          animateStatCard('stat-total-revenue');
        }
      }
      
      // Update Today's Orders
      updateStatCard('stat-today-orders', stats.today_orders);
      
      // Update Stock Alerts
      const stockCard = document.getElementById('stat-stock-alerts');
      if (stockCard) {
        const totalAlerts = stats.out_of_stock + stats.low_stock;
        if (totalAlerts > 0) {
          stockCard.style.display = '';
          stockCard.style.borderLeft = stats.out_of_stock > 0 ? '4px solid #ef4444' : '4px solid #f59e0b';
          stockCard.querySelector('.stat-icon').style.background = stats.out_of_stock > 0 ? '#fee2e2' : '#fef3c7';
          stockCard.querySelector('.stat-icon').style.color = stats.out_of_stock > 0 ? '#ef4444' : '#f59e0b';
          stockCard.querySelector('.stat-value').textContent = totalAlerts;
          stockCard.querySelector('.stockout-label').textContent = stats.out_of_stock > 0 ? stats.out_of_stock + ' Stockout' : '';
          stockCard.querySelector('.lowstock-label').textContent = stats.low_stock > 0 ? stats.low_stock + ' Low Stock' : '';
        } else {
          stockCard.style.display = 'none';
        }
      }
    }
  } catch (error) {
    console.error('Error refreshing stats:', error);
  }
}

function updateStatCard(cardId, newValue) {
  const card = document.getElementById(cardId);
  if (card) {
    const valueEl = card.querySelector('.stat-value');
    if (valueEl && valueEl.textContent !== String(newValue)) {
      valueEl.textContent = newValue;
      animateStatCard(cardId);
    }
  }
}

function animateStatCard(cardId) {
  const card = document.getElementById(cardId);
  if (card) {
    card.style.animation = 'none';
    card.offsetHeight;
    card.style.animation = 'statPulse 0.5s ease';
  }
}

setInterval(refreshDashboardStats, 15000); // Every 15 seconds

// Navigation
document.querySelectorAll('.nav-link[data-section]').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    const section = this.dataset.section;
    
    // Update nav
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    this.classList.add('active');
    
    // Update sections
    document.querySelectorAll('.section-tab').forEach(s => s.classList.remove('active'));
    document.getElementById(section).classList.add('active');
    
    // Update title
    document.getElementById('pageTitle').textContent = this.textContent.trim();
    
    // Load data if needed
    if (section === 'users') loadUsers();
    if (section === 'complaints') loadComplaints();
  });
});

// Alert function
function showAlert(message, type = 'success') {
  const container = document.getElementById('alertContainer');
  const alertId = 'alert-' + Date.now();
  container.innerHTML = `
    <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show alert-toast" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  setTimeout(() => document.getElementById(alertId)?.remove(), 4000);
}

// Order Modal instance - will be initialized when DOM is ready
let orderModal = null;
let currentViewOrderId = null;

// Initialize modal when document is ready
document.addEventListener('DOMContentLoaded', function() {
  const orderModalEl = document.getElementById('orderModal');
  if (orderModalEl) {
    orderModal = new bootstrap.Modal(orderModalEl);
  }
});

// View Order Details
function viewOrder(orderId) {
  // Initialize modal if not already done
  if (!orderModal) {
    const orderModalEl = document.getElementById('orderModal');
    if (orderModalEl) {
      orderModal = new bootstrap.Modal(orderModalEl);
    }
  }
  
  currentViewOrderId = orderId;
  const order = ordersData.find(o => o.id == orderId);
  if (!order) {
    showAlert('Order not found', 'danger');
    return;
  }
  
  const items = JSON.parse(order.items || '[]');
  const statusColors = {
    'Pending': 'warning',
    'Processing': 'info',
    'Completed': 'success',
    'Cancelled': 'danger'
  };
  const statusColor = statusColors[order.status] || 'secondary';
  
  const html = `
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card border-0 bg-light">
          <div class="card-body">
            <h6 class="text-muted mb-3"><i class="bi bi-info-circle me-2"></i>Order Information</h6>
            <p class="mb-2"><strong>Order ID:</strong> #${order.id}</p>
            <p class="mb-2"><strong>Date:</strong> ${new Date(order.order_date).toLocaleString('en-US', { 
              weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', 
              hour: '2-digit', minute: '2-digit' 
            })}</p>
            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-${statusColor}">${order.status || 'Pending'}</span></p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-0 bg-light">
          <div class="card-body">
            <h6 class="text-muted mb-3"><i class="bi bi-person me-2"></i>Customer Details</h6>
            <p class="mb-2"><strong>Name:</strong> ${order.full_name || 'Guest'}</p>
            <p class="mb-2"><strong>Email:</strong> ${order.email || 'N/A'}</p>
          </div>
        </div>
      </div>
    </div>
    
    <h6 class="text-muted mb-3"><i class="bi bi-bag me-2"></i>Order Items</h6>
    <div class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead class="table-success">
          <tr>
            <th>#</th>
            <th>Item</th>
            <th class="text-center">Qty</th>
            <th class="text-end">Price</th>
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody>
          ${items.map((item, idx) => `
            <tr>
              <td>${idx + 1}</td>
              <td><strong>${item.title || item.name}</strong></td>
              <td class="text-center">${item.quantity}</td>
              <td class="text-end">৳${parseFloat(item.price).toFixed(0)}</td>
              <td class="text-end">৳${(item.quantity * parseFloat(item.price)).toFixed(0)}</td>
            </tr>
          `).join('')}
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
            <td class="text-end"><strong class="text-success fs-5">৳${parseFloat(order.total_price).toFixed(0)}</strong></td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;
  
  document.getElementById('orderModalBody').innerHTML = html;
  document.getElementById('printOrderBtnModal').onclick = () => printAdminBill(orderId);
  orderModal.show();
}

// Print Admin Bill (Single Order PDF)
function printAdminBill(orderId) {
  const order = ordersData.find(o => o.id == orderId);
  if (!order) {
    showAlert('Order not found', 'danger');
    return;
  }
  
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p', 'mm', 'a4');
  
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  const items = JSON.parse(order.items || '[]');
  const billNumber = order.bill_number || 'GB-' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '-' + String(order.id).padStart(4, '0');
  
  // Background Watermarks
  doc.setTextColor(245, 245, 245);
  doc.setFontSize(50);
  doc.setFont('helvetica', 'bold');
  doc.text('GREEN BITES', pageWidth / 2, pageHeight / 2, { angle: 45, align: 'center' });
  doc.setFontSize(30);
  doc.text('ADMIN COPY', pageWidth / 2, pageHeight / 2 + 30, { angle: 45, align: 'center' });
  
  // Reset text color
  doc.setTextColor(0, 0, 0);
  
  // Header
  doc.setFillColor(34, 197, 94);
  doc.rect(0, 0, pageWidth, 40, 'F');
  
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(24);
  doc.setFont('helvetica', 'bold');
  doc.text('GREEN BITES', pageWidth / 2, 18, { align: 'center' });
  
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text('Campus Canteen - Order Receipt', pageWidth / 2, 26, { align: 'center' });
  
  // Admin Copy Badge
  doc.setFillColor(239, 68, 68);
  doc.roundedRect(pageWidth / 2 - 20, 30, 40, 7, 2, 2, 'F');
  doc.setFontSize(9);
  doc.setFont('helvetica', 'bold');
  doc.text('ADMIN COPY', pageWidth / 2, 35, { align: 'center' });
  
  doc.setTextColor(0, 0, 0);
  
  // Bill Number - Prominent Display
  doc.setFillColor(34, 197, 94);
  doc.roundedRect(pageWidth / 2 - 35, 44, 70, 10, 2, 2, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(11);
  doc.setFont('helvetica', 'bold');
  doc.text('Bill No: ' + billNumber, pageWidth / 2, 51, { align: 'center' });
  doc.setTextColor(0, 0, 0);
  
  // Order Info Section
  doc.setFillColor(248, 250, 252);
  doc.roundedRect(15, 58, pageWidth - 30, 35, 3, 3, 'F');
  
  doc.setFontSize(11);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(34, 197, 94);
  doc.text('Order #' + order.id, 20, 68);
  
  doc.setTextColor(100, 100, 100);
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  doc.text('Date: ' + new Date(order.order_date).toLocaleString('en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  }), 20, 76);
  doc.text('Status: ' + (order.status || 'Pending').toUpperCase(), 20, 84);
  doc.setTextColor(59, 130, 246);
  doc.text('Payment: ' + (order.payment_method || 'Pay at Counter'), 20, 90);
  doc.setTextColor(100, 100, 100);
  
  // Customer Info
  doc.setFont('helvetica', 'bold');
  doc.text('Customer:', pageWidth - 80, 68);
  doc.setFont('helvetica', 'normal');
  doc.text(order.full_name || 'Guest', pageWidth - 80, 76);
  doc.text(order.email || 'N/A', pageWidth - 80, 84);
  
  // Items Table
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(0, 0, 0);
  doc.text('Order Items', 15, 112);
  
  const tableData = items.map((item, idx) => [
    idx + 1,
    item.title || item.name,
    item.quantity,
    'TK ' + parseFloat(item.price).toFixed(0),
    'TK ' + (item.quantity * parseFloat(item.price)).toFixed(0)
  ]);
  
  doc.autoTable({
    head: [['#', 'Item Name', 'Qty', 'Price', 'Total']],
    body: tableData,
    startY: 115,
    styles: {
      fontSize: 10,
      cellPadding: 5
    },
    headStyles: {
      fillColor: [34, 197, 94],
      textColor: 255,
      fontStyle: 'bold',
      halign: 'center'
    },
    columnStyles: {
      0: { halign: 'center', cellWidth: 15 },
      1: { cellWidth: 80 },
      2: { halign: 'center', cellWidth: 20 },
      3: { halign: 'right', cellWidth: 30 },
      4: { halign: 'right', cellWidth: 35 }
    },
    alternateRowStyles: {
      fillColor: [249, 250, 251]
    },
    margin: { left: 15, right: 15 }
  });
  
  const finalY = doc.lastAutoTable.finalY + 10;
  
  // Total Section
  doc.setFillColor(34, 197, 94);
  doc.roundedRect(pageWidth - 80, finalY, 65, 20, 3, 3, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text('Grand Total:', pageWidth - 75, finalY + 8);
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('TK ' + parseFloat(order.total_price).toFixed(0), pageWidth - 75, finalY + 16);
  
  // Footer
  doc.setTextColor(150, 150, 150);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'normal');
  doc.text('Generated on: ' + new Date().toLocaleString(), pageWidth / 2, pageHeight - 15, { align: 'center' });
  doc.text('Green Bites - Admin Copy', pageWidth / 2, pageHeight - 10, { align: 'center' });
  
  // Save PDF
  doc.save(`GreenBites_Order_${order.id}_Admin.pdf`);
  showAlert('Admin copy PDF downloaded!');
}

// Menu Item Functions
function openAddMenuModal() {
  document.getElementById('menuModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Menu Item';
  document.getElementById('menuForm').reset();
  document.getElementById('menuItemId').value = '';
  document.getElementById('menuBuyingPrice').value = '';
  document.getElementById('menuDiscount').value = 0;
  document.getElementById('menuFinalPrice').value = '';
  document.getElementById('menuProfitMargin').value = '';
  document.getElementById('discountPreview').textContent = '';
  document.getElementById('profitPercent').textContent = '';
  document.getElementById('priceValidation').className = 'd-none';
  document.getElementById('menuSubmitBtn').disabled = false;
}

function editMenuItem(id) {
  const item = menuData.find(m => m.id == id);
  if (!item) return;
  
  document.getElementById('menuModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Menu Item';
  document.getElementById('menuItemId').value = item.id;
  document.getElementById('menuName').value = item.title;
  document.getElementById('menuCategory').value = item.category_id;
  document.getElementById('menuBuyingPrice').value = item.buying_price || '';
  document.getElementById('menuPrice').value = item.price;
  document.getElementById('menuDiscount').value = item.discount_percent || 0;
  document.getElementById('menuQuantity').value = item.quantity || 0;
  document.getElementById('menuImage').value = item.image_url || '';
  document.getElementById('menuDescription').value = item.description || '';
  
  // Calculate and show final price and profit margin
  calculateFinalPrice();
  
  new bootstrap.Modal(document.getElementById('menuModal')).show();
}

// Calculate final price with discount and profit margin
function calculateFinalPrice() {
  const buyingPrice = parseFloat(document.getElementById('menuBuyingPrice').value) || 0;
  const sellingPrice = parseFloat(document.getElementById('menuPrice').value) || 0;
  const discount = parseInt(document.getElementById('menuDiscount').value) || 0;
  const validationDiv = document.getElementById('priceValidation');
  
  // Calculate final price after discount
  if (sellingPrice > 0) {
    const finalPrice = sellingPrice - (sellingPrice * discount / 100);
    document.getElementById('menuFinalPrice').value = finalPrice.toFixed(0);
    
    if (discount > 0) {
      const savings = sellingPrice - finalPrice;
      document.getElementById('discountPreview').innerHTML = `<i class="bi bi-tag-fill"></i> Save ৳${savings.toFixed(0)} (${discount}% off)`;
    } else {
      document.getElementById('discountPreview').textContent = '';
    }
    
    // Calculate profit margin
    if (buyingPrice > 0) {
      const profitAmount = finalPrice - buyingPrice;
      const profitPercent = ((finalPrice - buyingPrice) / buyingPrice * 100).toFixed(1);
      document.getElementById('menuProfitMargin').value = profitAmount.toFixed(0);
      document.getElementById('profitPercent').innerHTML = `${profitPercent}% margin`;
      
      // Validation
      if (buyingPrice > sellingPrice) {
        validationDiv.className = 'alert alert-danger mb-0 py-1 px-2';
        validationDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Buying price must be ≤ selling price';
        document.getElementById('menuSubmitBtn').disabled = true;
      } else if (buyingPrice > finalPrice) {
        validationDiv.className = 'alert alert-warning mb-0 py-1 px-2';
        validationDiv.innerHTML = '<i class="bi bi-exclamation-circle"></i> Negative profit with discount!';
        document.getElementById('menuSubmitBtn').disabled = false;
      } else {
        validationDiv.className = 'alert alert-success mb-0 py-1 px-2';
        validationDiv.innerHTML = '<i class="bi bi-check-circle"></i> Valid pricing';
        document.getElementById('menuSubmitBtn').disabled = false;
      }
    } else {
      document.getElementById('menuProfitMargin').value = '';
      document.getElementById('profitPercent').textContent = '';
      validationDiv.className = 'alert alert-info mb-0 py-1 px-2';
      validationDiv.innerHTML = '<i class="bi bi-info-circle"></i> Enter buying price';
      document.getElementById('menuSubmitBtn').disabled = false;
    }
  } else {
    document.getElementById('menuFinalPrice').value = '';
    document.getElementById('discountPreview').textContent = '';
    document.getElementById('menuProfitMargin').value = '';
    document.getElementById('profitPercent').textContent = '';
    validationDiv.className = 'd-none';
  }
}

// Add event listeners for price calculation
document.getElementById('menuBuyingPrice').addEventListener('input', calculateFinalPrice);
document.getElementById('menuPrice').addEventListener('input', calculateFinalPrice);
document.getElementById('menuDiscount').addEventListener('input', calculateFinalPrice);

document.getElementById('menuForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const btn = document.getElementById('menuSubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
  
  const formData = new FormData(this);
  const id = formData.get('id');
  const url = id ? 'api/update_menu.php' : 'api/add_menu.php';
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      showAlert(result.message || 'Menu item saved!');
      bootstrap.Modal.getInstance(document.getElementById('menuModal')).hide();
      setTimeout(() => location.reload(), 1000);
    } else {
      showAlert(result.message || 'Failed to save.', 'danger');
    }
  } catch (err) {
    showAlert('Network error.', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Item';
  }
});

function deleteMenuItem(id, name) {
  if (!confirm(`Are you sure you want to delete "${name}"?`)) return;
  
  fetch('api/delete_menu.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id, csrf_token: csrfToken }),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      showAlert('Item deleted!');
      document.querySelector(`tr[data-id="${id}"]`)?.remove();
    } else {
      showAlert(result.message || 'Failed to delete.', 'danger');
    }
  })
  .catch(() => showAlert('Network error.', 'danger'));
}

// Update Quantity Function (Quick +/- buttons)
function updateQuantity(itemId, change) {
  fetch('api/update_quantity.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: itemId, change: change, csrf_token: csrfToken }),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      showAlert(result.message || 'Quantity updated!');
      setTimeout(() => location.reload(), 500);
    } else {
      showAlert(result.message || 'Failed to update quantity.', 'danger');
    }
  })
  .catch(() => showAlert('Network error.', 'danger'));
}

// Order Status Update - Function for re-attaching listeners
function attachStatusListeners() {
  document.querySelectorAll('.status-select').forEach(select => {
    // Remove existing listener to prevent duplicates
    select.removeEventListener('change', handleStatusChange);
    select.addEventListener('change', handleStatusChange);
  });
}

function handleStatusChange() {
  const orderId = this.dataset.orderId;
  const status = this.value;
  
  fetch('api/update_order_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ order_id: orderId, status: status, csrf_token: csrfToken }),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      showAlert('Order status updated!');
    } else {
      showAlert(result.message || 'Failed to update.', 'danger');
    }
  });
}

// Initial attach
attachStatusListeners();

// Search & Filter
document.getElementById('menuSearch')?.addEventListener('input', function() {
  const search = this.value.toLowerCase();
  document.querySelectorAll('#menuTable tbody tr').forEach(row => {
    const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
    row.style.display = name.includes(search) ? '' : 'none';
  });
});

document.getElementById('categoryFilter')?.addEventListener('change', function() {
  const catId = this.value;
  document.querySelectorAll('#menuTable tbody tr').forEach(row => {
    row.style.display = (!catId || row.dataset.category == catId) ? '' : 'none';
  });
});

document.getElementById('orderStatusFilter')?.addEventListener('change', function() {
  const status = this.value;
  document.querySelectorAll('#ordersTable tbody tr').forEach(row => {
    row.style.display = (!status || row.dataset.status == status) ? '' : 'none';
  });
});

// Load Users
async function loadUsers() {
  try {
    const response = await fetch('api/get_users.php', { credentials: 'same-origin' });
    const result = await response.json();
    if (result.success) {
      const tbody = document.getElementById('usersTableBody');
      tbody.innerHTML = result.users.map(u => `
        <tr>
          <td>${u.id}</td>
          <td><strong>${u.full_name}</strong></td>
          <td>@${u.username}</td>
          <td>${u.email}</td>
          <td>${new Date(u.created_at).toLocaleDateString()}</td>
        </tr>
      `).join('');
    }
  } catch (err) {
    console.error(err);
  }
}

// Load Complaints
async function loadComplaints() {
  try {
    const response = await fetch('api/get_complaints.php', { credentials: 'same-origin' });
    const result = await response.json();
    if (result.success) {
      const tbody = document.getElementById('complaintsTableBody');
      let unseenCount = 0;
      
      const statusLabels = {
        'pending': '<span class="badge bg-warning text-dark">Pending</span>',
        'seen': '<span class="badge bg-info">Seen</span>',
        'in_progress': '<span class="badge bg-primary">In Progress</span>',
        'resolved': '<span class="badge bg-success">Resolved</span>',
        'closed': '<span class="badge bg-secondary">Closed</span>'
      };
      
      tbody.innerHTML = result.complaints.map(c => {
        const createdAt = new Date(c.created_at);
        const isUnseen = c.is_seen == 0 || c.is_seen === '0';
        if (isUnseen) unseenCount++;
        
        const status = c.status || 'pending';
        const statusBadge = statusLabels[status] || statusLabels['pending'];
        
        const dateTimeStr = createdAt.toLocaleString('en-US', {
          month: 'short', day: 'numeric', year: 'numeric',
          hour: '2-digit', minute: '2-digit', hour12: true
        });
        
        return `
          <tr class="${isUnseen ? 'table-warning' : ''}" data-id="${c.id}">
            <td>${c.id} ${isUnseen ? '<span class="badge bg-danger">New</span>' : ''}</td>
            <td><strong>${c.name}</strong></td>
            <td>${c.email}</td>
            <td>${c.message.length > 80 ? c.message.substring(0, 80) + '...' : c.message}</td>
            <td>${c.image_path ? `<a href="../${c.image_path}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-image"></i></a>` : '-'}</td>
            <td>${statusBadge}</td>
            <td><small>${dateTimeStr}</small></td>
            <td>
              <button class="btn btn-sm btn-outline-info" onclick="viewComplaint(${c.id}, '${escapeHtml(c.name)}', '${escapeHtml(c.email)}', \`${escapeHtml(c.message)}\`, '${c.image_path || ''}', '${status}', \`${escapeHtml(c.admin_response || '')}\`)">
                <i class="bi bi-eye"></i>
              </button>
              ${isUnseen ? `<button class="btn btn-sm btn-outline-success ms-1" onclick="markComplaintSeen(${c.id})"><i class="bi bi-check"></i></button>` : ''}
              <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteComplaint(${c.id})" title="Delete Complaint">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
      
      // Update badge
      updateComplaintsBadge(unseenCount);
      
      // Show/hide mark all button
      document.getElementById('markAllSeenBtn').style.display = unseenCount > 0 ? 'inline-block' : 'none';
    }
  } catch (err) {
    console.error(err);
  }
}

// Escape HTML for safe display
function escapeHtml(text) {
  if (!text) return '';
  return text.replace(/[&<>"'`]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[m]));
}

// View complaint details
function viewComplaint(id, name, email, message, imagePath, status, response) {
  document.getElementById('complaintId').value = id;
  document.getElementById('complaintFrom').textContent = name;
  document.getElementById('complaintEmail').textContent = email;
  document.getElementById('complaintMessage').innerHTML = message.replace(/\n/g, '<br>');
  document.getElementById('complaintStatus').value = status;
  document.getElementById('complaintResponse').value = response;
  
  const imgSection = document.getElementById('complaintImageSection');
  if (imagePath) {
    document.getElementById('complaintImage').src = '../' + imagePath;
    imgSection.style.display = 'block';
  } else {
    imgSection.style.display = 'none';
  }
  
  new bootstrap.Modal(document.getElementById('complaintModal')).show();
  
  // Auto mark as seen
  markComplaintSeen(id, true);
}

// Save complaint response
async function saveComplaintResponse() {
  const id = document.getElementById('complaintId').value;
  const status = document.getElementById('complaintStatus').value;
  const response = document.getElementById('complaintResponse').value;
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);
    formData.append('admin_response', response);
    
    const res = await fetch('api/update_complaint_status.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await res.json();
    
    if (result.success) {
      bootstrap.Modal.getInstance(document.getElementById('complaintModal')).hide();
      loadComplaints();
      showAlert('Complaint updated successfully', 'success');
    } else {
      showAlert(result.message || 'Failed to update', 'danger');
    }
  } catch (err) {
    console.error(err);
    showAlert('Error updating complaint', 'danger');
  }
}

// Mark single complaint as seen
async function markComplaintSeen(id, silent = false) {
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('api/mark_complaint_seen.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      if (!silent) {
        loadComplaints(); // Reload table
        showAlert('Complaint marked as seen', 'success');
      }
    }
  } catch (err) {
    console.error(err);
  }
}

// Mark all complaints as seen
async function markAllComplaintsSeen() {
  try {
    const formData = new FormData();
    formData.append('mark_all', '1');
    
    const response = await fetch('api/mark_complaint_seen.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      loadComplaints(); // Reload table
      showAlert('All complaints marked as seen', 'success');
    }
  } catch (err) {
    console.error(err);
  }
}

// Delete complaint
async function deleteComplaint(id) {
  if (!confirm('Are you sure you want to delete this complaint? This action cannot be undone.')) {
    return;
  }
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('api/delete_complaint.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      loadComplaints(); // Reload table
      showAlert('Complaint deleted successfully', 'success');
    } else {
      showAlert(result.message || 'Failed to delete complaint', 'danger');
    }
  } catch (err) {
    console.error(err);
    showAlert('Error deleting complaint', 'danger');
  }
}

// Delete complaint from modal
async function deleteComplaintFromModal() {
  const id = document.getElementById('complaintId').value;
  if (!id) return;
  
  if (!confirm('Are you sure you want to delete this complaint? This action cannot be undone.')) {
    return;
  }
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('api/delete_complaint.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      bootstrap.Modal.getInstance(document.getElementById('complaintModal')).hide();
      loadComplaints(); // Reload table
      showAlert('Complaint deleted successfully', 'success');
    } else {
      showAlert(result.message || 'Failed to delete complaint', 'danger');
    }
  } catch (err) {
    console.error(err);
    showAlert('Error deleting complaint', 'danger');
  }
}

// Update complaints badge
function updateComplaintsBadge(count) {
  const badge = document.getElementById('complaintsBadge');
  if (count > 0) {
    badge.textContent = count;
    badge.style.display = 'inline-block';
  } else {
    badge.style.display = 'none';
  }
}

// Export PDF - Beautiful Order Report with Watermark
function exportOrdersPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('l', 'mm', 'a4'); // Landscape for better table view
  
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  
  // Add Text Watermarks
  doc.setTextColor(240, 240, 240);
  doc.setFontSize(55);
  doc.setFont('helvetica', 'bold');
  doc.text('GREEN BITES', pageWidth / 2, pageHeight / 2, { angle: 35, align: 'center' });
  doc.setFontSize(35);
  doc.text('GREEN BITES', 50, 70, { angle: 35 });
  doc.text('GREEN BITES', 200, 170, { angle: 35 });
  doc.text('GREEN BITES', 80, 170, { angle: 35 });
  
  // Reset for content
  doc.setTextColor(0, 0, 0);
  
  // Header Background
  doc.setFillColor(34, 197, 94);
  doc.rect(0, 0, pageWidth, 35, 'F');
  
  // Header Text
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(24);
  doc.setFont('helvetica', 'bold');
  doc.text('GREEN BITES', 15, 15);
  doc.setFontSize(12);
  doc.setFont('helvetica', 'normal');
  doc.text('Campus Canteen - Admin Order Report', 15, 25);
  
  // Report Info on right
  doc.setFontSize(10);
  doc.text('Generated: ' + new Date().toLocaleString('en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  }), pageWidth - 15, 15, { align: 'right' });
  doc.text('Total Orders: ' + ordersData.length, pageWidth - 15, 22, { align: 'right' });
  
  // Calculate total revenue
  const totalRevenue = ordersData.reduce((sum, o) => sum + parseFloat(o.total_price || 0), 0);
  doc.text('Total Revenue: TK ' + totalRevenue.toFixed(0), pageWidth - 15, 29, { align: 'right' });
  
  doc.setTextColor(0, 0, 0);
  
  // Summary Stats
  const pendingCount = ordersData.filter(o => (o.status || '').toLowerCase() === 'pending').length;
  const completedCount = ordersData.filter(o => (o.status || '').toLowerCase() === 'completed').length;
  const processingCount = ordersData.filter(o => (o.status || '').toLowerCase() === 'processing').length;
  
  doc.setFillColor(248, 250, 252);
  doc.roundedRect(15, 40, pageWidth - 30, 15, 3, 3, 'F');
  
  doc.setFontSize(10);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(100, 100, 100);
  doc.text('Summary:', 20, 49);
  
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(245, 158, 11);
  doc.text('Pending: ' + pendingCount, 55, 49);
  doc.setTextColor(59, 130, 246);
  doc.text('Processing: ' + processingCount, 100, 49);
  doc.setTextColor(34, 197, 94);
  doc.text('Completed: ' + completedCount, 155, 49);
  doc.setTextColor(0, 0, 0);
  
  // Prepare table data
  const tableData = ordersData.map(o => {
    const items = JSON.parse(o.items || '[]');
    const itemsList = items.map(i => `${i.title || i.name} x${i.quantity}`).join(', ');
    return [
      '#' + o.id,
      o.full_name || 'Guest',
      o.email || 'N/A',
      itemsList.length > 50 ? itemsList.substring(0, 47) + '...' : itemsList,
      'TK ' + parseFloat(o.total_price).toFixed(0),
      (o.status || 'Pending').toUpperCase(),
      new Date(o.order_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    ];
  });
  
  // Create table
  doc.autoTable({
    head: [['Order ID', 'Customer', 'Email', 'Items', 'Total', 'Status', 'Date']],
    body: tableData,
    startY: 60,
    styles: { 
      fontSize: 9,
      cellPadding: 4
    },
    headStyles: { 
      fillColor: [34, 197, 94],
      textColor: 255,
      fontStyle: 'bold',
      halign: 'center'
    },
    alternateRowStyles: {
      fillColor: [249, 250, 251]
    },
    columnStyles: {
      0: { halign: 'center', cellWidth: 20 },
      1: { cellWidth: 35 },
      2: { cellWidth: 45 },
      3: { cellWidth: 70 },
      4: { halign: 'right', cellWidth: 25 },
      5: { halign: 'center', cellWidth: 25 },
      6: { halign: 'center', cellWidth: 30 }
    },
    didParseCell: function(data) {
      // Color code status
      if (data.column.index === 5 && data.section === 'body') {
        const status = (data.cell.raw || '').toLowerCase();
        if (status === 'completed') {
          data.cell.styles.textColor = [34, 197, 94];
          data.cell.styles.fontStyle = 'bold';
        } else if (status === 'pending') {
          data.cell.styles.textColor = [245, 158, 11];
          data.cell.styles.fontStyle = 'bold';
        } else if (status === 'processing') {
          data.cell.styles.textColor = [59, 130, 246];
          data.cell.styles.fontStyle = 'bold';
        } else if (status === 'cancelled') {
          data.cell.styles.textColor = [239, 68, 68];
          data.cell.styles.fontStyle = 'bold';
        }
      }
    }
  });
  
  // Footer
  const finalY = doc.lastAutoTable.finalY + 10;
  doc.setDrawColor(34, 197, 94);
  doc.setLineWidth(0.5);
  doc.line(15, finalY, pageWidth - 15, finalY);
  
  doc.setFontSize(9);
  doc.setTextColor(100, 100, 100);
  doc.text('Green Bites Campus Canteen | Contact: +8801968-161494 | Email: sajjadmahmudsuton@gmail.com', pageWidth / 2, finalY + 7, { align: 'center' });
  doc.text('This is a computer generated report.', pageWidth / 2, finalY + 12, { align: 'center' });
  
  // Save PDF
  const fileName = 'GreenBites_Orders_Report_' + new Date().toISOString().split('T')[0] + '.pdf';
  doc.save(fileName);
  showAlert('PDF Report downloaded!');
}

// Category Data
const categoryData = <?php echo json_encode($categories); ?>;

// Category Functions
function openAddCategoryModal() {
  document.getElementById('categoryModalTitle').innerHTML = '<i class="bi bi-tag me-2"></i>Add Category';
  document.getElementById('categoryForm').reset();
  document.getElementById('categoryId').value = '';
  if (document.getElementById('categoryDescription')) {
    document.getElementById('categoryDescription').value = '';
  }
}

function editCategory(id, name, icon, description) {
  document.getElementById('categoryModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Category';
  document.getElementById('categoryId').value = id;
  document.getElementById('categoryName').value = name;
  document.getElementById('categoryIcon').value = icon || '';
  if (document.getElementById('categoryDescription')) {
    document.getElementById('categoryDescription').value = description || '';
  }
  
  new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

document.getElementById('categoryForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const btn = document.getElementById('categorySubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
  
  const formData = new FormData(this);
  const id = formData.get('id');
  const url = id ? 'api/update_category.php' : 'api/add_category.php';
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      showAlert(result.message || 'Category saved!');
      bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
      setTimeout(() => location.reload(), 1000);
    } else {
      showAlert(result.message || 'Failed to save.', 'danger');
    }
  } catch (err) {
    showAlert('Network error.', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'Save Category';
  }
});

// Delete Category
async function deleteCategory(id, name) {
  if (!confirm(`Are you sure you want to delete category "${name}"?`)) return;
  
  try {
    const response = await fetch('api/delete_category.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
      },
      body: JSON.stringify({ id: id }),
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      showAlert(result.message, 'success');
      setTimeout(() => location.reload(), 1000);
    } else {
      showAlert(result.message || 'Failed to delete category.', 'danger');
    }
  } catch (err) {
    showAlert('Network error.', 'danger');
  }
}

// Show Add Category Modal
function showAddCategoryModal() {
  document.getElementById('categoryModalTitle').innerHTML = '<i class="bi bi-tag me-2"></i>Add Category';
  document.getElementById('categoryForm').reset();
  document.getElementById('categoryId').value = '';
  new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

// ==================== CAROUSEL SLIDE FUNCTIONS ====================

let slideModal = null;

// Initialize slide modal when needed
function getSlideModal() {
  if (!slideModal) {
    const slideModalEl = document.getElementById('slideModal');
    if (slideModalEl) {
      slideModal = new bootstrap.Modal(slideModalEl);
    }
  }
  return slideModal;
}

// Image Preview
document.addEventListener('DOMContentLoaded', function() {
  const slideImageInput = document.getElementById('slideImageUrl');
  if (slideImageInput) {
    slideImageInput.addEventListener('input', function() {
      const url = this.value.trim();
      const previewDiv = document.getElementById('slideImagePreview');
      const previewImg = document.getElementById('slidePreviewImg');
      
      if (url) {
        previewImg.src = url;
        previewDiv.style.display = 'block';
        previewImg.onerror = () => { previewDiv.style.display = 'none'; };
      } else {
        previewDiv.style.display = 'none';
      }
    });
  }
});


// Show Add Slide Modal
function showAddSlideModal() {
  document.getElementById('slideModalTitle').innerHTML = '<i class="bi bi-images me-2"></i>Add Carousel Slide';
  document.getElementById('slideForm').reset();
  document.getElementById('slideId').value = '';
  document.getElementById('slideActive').checked = true;
  document.getElementById('slideBtnText').value = 'Order Now';
  document.getElementById('slideBtnLink').value = '#dealsSection';
  document.getElementById('slideSortOrder').value = '0';
  document.getElementById('slideImagePreview').style.display = 'none';
  getSlideModal().show();
}

// Edit Slide
async function editSlide(id) {
  try {
    const response = await fetch(`api/carousel.php?action=get&id=${id}`, {
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      const slide = result.slide;
      document.getElementById('slideModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Carousel Slide';
      document.getElementById('slideId').value = slide.id;
      document.getElementById('slideTitle').value = slide.title;
      document.getElementById('slideDescription').value = slide.description || '';
      document.getElementById('slidePrice').value = slide.price;
      document.getElementById('slideImageUrl').value = slide.image_url;
      document.getElementById('slideBtnText').value = slide.btn_text || 'Order Now';
      document.getElementById('slideSortOrder').value = slide.sort_order || 0;
      document.getElementById('slideActive').checked = slide.is_active == 1;
      document.getElementById('slideMenuItemId').value = slide.menu_item_id || '';
      
      // Show preview
      if (slide.image_url) {
        document.getElementById('slidePreviewImg').src = slide.image_url;
        document.getElementById('slideImagePreview').style.display = 'block';
      }
      
      getSlideModal().show();
    } else {
      showAlert(result.message || 'Failed to load slide', 'danger');
    }
  } catch (err) {
    showAlert('Network error', 'danger');
  }
}

// Save Slide
document.getElementById('slideForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const btn = document.getElementById('slideSubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
  
  const formData = new FormData(this);
  const id = formData.get('id');
  const action = id ? 'update' : 'add';
  
  // Handle checkbox
  formData.set('is_active', document.getElementById('slideActive').checked ? '1' : '0');
  
  try {
    const response = await fetch(`api/carousel.php?action=${action}`, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      showAlert(result.message || 'Slide saved successfully!');
      getSlideModal().hide();
      setTimeout(() => location.reload(), 1000);
    } else {
      showAlert(result.message || 'Failed to save slide', 'danger');
    }
  } catch (err) {
    showAlert('Network error', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Slide';
  }
});

// Toggle Slide Active Status
async function toggleSlide(id) {
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('api/carousel.php?action=toggle', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      showAlert('Slide status updated!');
      setTimeout(() => location.reload(), 500);
    } else {
      showAlert(result.message || 'Failed to update status', 'danger');
    }
  } catch (err) {
    showAlert('Network error', 'danger');
  }
}

// Delete Slide
async function deleteSlide(id, title) {
  if (!confirm(`Are you sure you want to delete slide "${title}"?`)) return;
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('api/carousel.php?action=delete', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      showAlert('Slide deleted successfully!');
      setTimeout(() => location.reload(), 1000);
    } else {
      showAlert(result.message || 'Failed to delete slide', 'danger');
    }
  } catch (err) {
    showAlert('Network error', 'danger');
  }
}
</script>

<!-- Admin Footer -->
<footer class="admin-footer">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-md-6 text-center text-md-start">
        <span class="text-muted">
          <i class="bi bi-shield-lock me-1"></i>
          Green Bites Admin Panel &copy; <?php echo date('Y'); ?>
        </span>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <span class="text-muted small">
          <i class="bi bi-person-badge me-1"></i>Logged in as: <strong><?php echo htmlspecialchars($admin_name); ?></strong>
          <span class="mx-2">|</span>
          <i class="bi bi-clock me-1"></i><span id="liveDateTime"></span>
        </span>
      </div>
    </div>
  </div>
</footer>

<style>
.admin-footer {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  color: #a0aec0;
  padding: 8px 20px;
  margin-top: auto;
  border-top: 1px solid rgba(255,255,255,0.1);
  position: fixed;
  bottom: 0;
  left: 250px;
  right: 0;
  z-index: 100;
  font-size: 0.8rem;
}
.admin-footer .text-muted {
  color: #a0aec0 !important;
}
@media (max-width: 991px) {
  .admin-footer {
    left: 0;
  }
}
/* Add padding to main content to prevent footer overlap */
main {
  padding-bottom: 50px !important;
}
</style>

<script>
// Live Date Time in Footer
function updateDateTime() {
  const now = new Date();
  const options = { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  };
  const formatted = now.toLocaleDateString('en-US', options);
  document.getElementById('liveDateTime').textContent = formatted;
}
updateDateTime();
setInterval(updateDateTime, 1000);

// ============================================
// REPORTS & ANALYTICS FUNCTIONALITY
// ============================================
let reportsData = null;
let monthlyChart = null;
let dailyChart = null;
let statusChart = null;
let hourlyChart = null;

// Load reports when section is shown
document.querySelector('[data-section="reports"]').addEventListener('click', function() {
  setTimeout(() => loadReports(), 100);
});

async function loadReports() {
  try {
    const response = await fetch('api/reports.php');
    reportsData = await response.json();
    
    if (reportsData.success) {
      updateReportStats(reportsData);
      updateCharts(reportsData);
      updateTables(reportsData);
    }
  } catch (error) {
    console.error('Error loading reports:', error);
  }
}

function refreshReports() {
  loadReports();
  showAlert('Reports refreshed!', 'success');
}

function updateReportStats(data) {
  // Today
  document.getElementById('todayRevenue').textContent = '৳' + formatNumber(data.overview.today.revenue);
  document.getElementById('todayOrders').textContent = data.overview.today.orders + ' orders';
  
  // Week
  document.getElementById('weekRevenue').textContent = '৳' + formatNumber(data.overview.week.revenue);
  document.getElementById('weekOrders').textContent = data.overview.week.orders + ' orders';
  
  // Month
  document.getElementById('monthRevenue').textContent = '৳' + formatNumber(data.overview.month.revenue);
  document.getElementById('monthOrders').textContent = data.overview.month.orders + ' orders';
  
  // Year
  document.getElementById('yearRevenue').textContent = '৳' + formatNumber(data.overview.year.revenue);
  document.getElementById('yearOrders').textContent = data.overview.year.orders + ' orders';
  
  // Average order value
  document.getElementById('avgOrderValue').textContent = '৳' + formatNumber(data.overview.avg_order_value);
  
  // Growth indicators
  const revenueGrowthEl = document.getElementById('revenueGrowth');
  const orderGrowthEl = document.getElementById('orderGrowth');
  
  const revGrowth = data.growth.revenue;
  const ordGrowth = data.growth.orders;
  
  revenueGrowthEl.innerHTML = `<i class="bi bi-graph-${revGrowth >= 0 ? 'up' : 'down'}"></i> <span>${revGrowth >= 0 ? '+' : ''}${revGrowth}%</span>`;
  revenueGrowthEl.className = revGrowth >= 0 ? 'mb-0 positive' : 'mb-0 negative';
  
  orderGrowthEl.innerHTML = `<i class="bi bi-graph-${ordGrowth >= 0 ? 'up' : 'down'}"></i> <span>${ordGrowth >= 0 ? '+' : ''}${ordGrowth}%</span>`;
  orderGrowthEl.className = ordGrowth >= 0 ? 'mb-0 positive' : 'mb-0 negative';
  
  // Peak hour
  document.getElementById('peakHourBadge').innerHTML = `<i class="bi bi-clock me-1"></i>Peak Hour: ${data.peak_hour_label}`;
}

function formatNumber(num) {
  return new Intl.NumberFormat('en-BD').format(Math.round(num));
}

function updateCharts(data) {
  // Monthly Revenue Chart
  const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
  if (monthlyChart) monthlyChart.destroy();
  
  monthlyChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
      labels: data.monthly_chart.map(m => m.label),
      datasets: [{
        label: 'Revenue (৳)',
        data: data.monthly_chart.map(m => m.revenue),
        backgroundColor: 'rgba(34, 197, 94, 0.7)',
        borderColor: '#22c55e',
        borderWidth: 2,
        borderRadius: 8,
        barThickness: 30
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => '৳' + formatNumber(value)
          }
        }
      }
    }
  });
  
  // Daily Revenue Chart
  const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
  if (dailyChart) dailyChart.destroy();
  
  dailyChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
      labels: data.daily_chart.map(d => d.label),
      datasets: [{
        label: 'Revenue',
        data: data.daily_chart.map(d => d.revenue),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointBackgroundColor: '#3b82f6'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => '৳' + formatNumber(value)
          }
        }
      }
    }
  });
  
  // Order Status Pie Chart
  const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
  if (statusChart) statusChart.destroy();
  
  const statusData = data.order_status;
  const statusColors = {
    'Pending': '#f59e0b',
    'Processing': '#3b82f6',
    'Completed': '#22c55e',
    'Cancelled': '#ef4444'
  };
  
  statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
      labels: Object.keys(statusData),
      datasets: [{
        data: Object.values(statusData),
        backgroundColor: Object.keys(statusData).map(s => statusColors[s] || '#94a3b8'),
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 20,
            usePointStyle: true
          }
        }
      }
    }
  });
  
  // Hourly Distribution Chart
  const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
  if (hourlyChart) hourlyChart.destroy();
  
  const hourLabels = [];
  for (let i = 0; i < 24; i++) {
    hourLabels.push(i.toString().padStart(2, '0') + ':00');
  }
  
  hourlyChart = new Chart(hourlyCtx, {
    type: 'bar',
    data: {
      labels: hourLabels,
      datasets: [{
        label: 'Orders',
        data: data.hourly_distribution,
        backgroundColor: data.hourly_distribution.map((v, i) => 
          i === data.peak_hour ? 'rgba(34, 197, 94, 0.9)' : 'rgba(148, 163, 184, 0.5)'
        ),
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}

function updateTables(data) {
  // Top Items Table
  const topItemsHtml = data.top_items.map((item, i) => `
    <tr>
      <td><span class="badge ${i < 3 ? 'bg-warning' : 'bg-secondary'}">${i + 1}</span></td>
      <td><strong>${escapeHtml(item.name)}</strong></td>
      <td class="text-center">${item.quantity}</td>
      <td class="text-end text-success fw-bold">৳${formatNumber(item.revenue)}</td>
    </tr>
  `).join('');
  document.getElementById('topItemsTable').innerHTML = topItemsHtml || '<tr><td colspan="4" class="text-center text-muted">No data</td></tr>';
  
  // Category Performance Table
  const catPerfHtml = data.category_performance.map((cat, i) => `
    <tr>
      <td><span class="badge ${i < 3 ? 'bg-success' : 'bg-secondary'}">${i + 1}</span></td>
      <td><strong>${escapeHtml(cat.name)}</strong></td>
      <td class="text-center">${cat.quantity}</td>
      <td class="text-end text-success fw-bold">৳${formatNumber(cat.revenue)}</td>
    </tr>
  `).join('');
  document.getElementById('categoryPerfTable').innerHTML = catPerfHtml || '<tr><td colspan="4" class="text-center text-muted">No data</td></tr>';
  
  // Top Customers Table
  const topCustHtml = data.top_customers.map((cust, i) => `
    <tr>
      <td><span class="badge ${i < 3 ? 'bg-primary' : 'bg-secondary'}">${i + 1}</span></td>
      <td>
        <strong>${escapeHtml(cust.name)}</strong>
        <br><small class="text-muted">${escapeHtml(cust.email)}</small>
      </td>
      <td class="text-center">${cust.orders}</td>
      <td class="text-end text-success fw-bold">৳${formatNumber(cust.spent)}</td>
    </tr>
  `).join('');
  document.getElementById('topCustomersTable').innerHTML = topCustHtml || '<tr><td colspan="4" class="text-center text-muted">No data</td></tr>';
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Export Report as PDF
function exportReportPDF() {
  if (!reportsData) {
    showAlert('Please wait for reports to load', 'warning');
    return;
  }
  
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p', 'mm', 'a4');
  const pageWidth = doc.internal.pageSize.getWidth();
  
  // Header
  doc.setFillColor(34, 197, 94);
  doc.rect(0, 0, pageWidth, 40, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(24);
  doc.setFont('helvetica', 'bold');
  doc.text('Green Bites', pageWidth / 2, 18, { align: 'center' });
  doc.setFontSize(14);
  doc.setFont('helvetica', 'normal');
  doc.text('Business Analytics Report', pageWidth / 2, 28, { align: 'center' });
  doc.setFontSize(10);
  doc.text('Generated: ' + new Date().toLocaleDateString(), pageWidth / 2, 36, { align: 'center' });
  
  let yPos = 55;
  doc.setTextColor(0, 0, 0);
  
  // Revenue Summary
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Revenue Summary', 15, yPos);
  yPos += 10;
  
  doc.setFontSize(11);
  doc.setFont('helvetica', 'normal');
  
  const summaryData = [
    ['Today', reportsData.overview.today.orders + ' orders', 'TK ' + formatNumber(reportsData.overview.today.revenue)],
    ['This Week', reportsData.overview.week.orders + ' orders', 'TK ' + formatNumber(reportsData.overview.week.revenue)],
    ['This Month', reportsData.overview.month.orders + ' orders', 'TK ' + formatNumber(reportsData.overview.month.revenue)],
    ['This Year', reportsData.overview.year.orders + ' orders', 'TK ' + formatNumber(reportsData.overview.year.revenue)],
    ['All Time', reportsData.overview.all_time.orders + ' orders', 'TK ' + formatNumber(reportsData.overview.all_time.revenue)]
  ];
  
  doc.autoTable({
    startY: yPos,
    head: [['Period', 'Orders', 'Revenue']],
    body: summaryData,
    theme: 'grid',
    headStyles: { fillColor: [34, 197, 94] },
    margin: { left: 15, right: 15 }
  });
  
  yPos = doc.lastAutoTable.finalY + 15;
  
  // Top Selling Items
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Top Selling Items', 15, yPos);
  yPos += 10;
  
  const topItemsData = reportsData.top_items.slice(0, 10).map((item, i) => [
    i + 1,
    item.name,
    item.quantity,
    'TK ' + formatNumber(item.revenue)
  ]);
  
  doc.autoTable({
    startY: yPos,
    head: [['#', 'Item Name', 'Qty Sold', 'Revenue']],
    body: topItemsData,
    theme: 'grid',
    headStyles: { fillColor: [34, 197, 94] },
    margin: { left: 15, right: 15 }
  });
  
  yPos = doc.lastAutoTable.finalY + 15;
  
  // Check if need new page
  if (yPos > 240) {
    doc.addPage();
    yPos = 20;
  }
  
  // Top Customers
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Top Customers', 15, yPos);
  yPos += 10;
  
  const topCustData = reportsData.top_customers.slice(0, 10).map((cust, i) => [
    i + 1,
    cust.name,
    cust.orders,
    'TK ' + formatNumber(cust.spent)
  ]);
  
  doc.autoTable({
    startY: yPos,
    head: [['#', 'Customer', 'Orders', 'Total Spent']],
    body: topCustData,
    theme: 'grid',
    headStyles: { fillColor: [34, 197, 94] },
    margin: { left: 15, right: 15 }
  });
  
  // Footer
  const pageCount = doc.internal.getNumberOfPages();
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i);
    doc.setFontSize(9);
    doc.setTextColor(128, 128, 128);
    doc.text('Green Bites Canteen - Confidential Report', pageWidth / 2, 290, { align: 'center' });
  }
  
  doc.save('GreenBites_Report_' + new Date().toISOString().slice(0, 10) + '.pdf');
  showAlert('Report exported successfully!', 'success');
}

// ============================================
// PROFIT DASHBOARD FUNCTIONALITY
// ============================================
let profitsData = null;
let profitTrendChart = null;
let profitByCategoryChart = null;
let revenueProfitChart = null;

// Set default date range (last 30 days)
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date();
  const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
  
  document.getElementById('profitDateTo').value = today.toISOString().split('T')[0];
  document.getElementById('profitDateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
  
  // Add date filter listeners
  document.getElementById('profitDateFrom').addEventListener('change', loadProfitData);
  document.getElementById('profitDateTo').addEventListener('change', loadProfitData);
  document.getElementById('profitSearch').addEventListener('input', debounce(loadProfitData, 500));
});

// Load profits when section is shown
document.querySelector('[data-section="profits"]').addEventListener('click', function() {
  setTimeout(() => loadProfitData(), 100);
});

// Debounce function for search
function debounce(func, wait) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

async function loadProfitData() {
  const dateFrom = document.getElementById('profitDateFrom').value;
  const dateTo = document.getElementById('profitDateTo').value;
  const search = document.getElementById('profitSearch').value;
  
  let url = 'api/profits.php?';
  if (dateFrom) url += `from=${dateFrom}&`;
  if (dateTo) url += `to=${dateTo}&`;
  if (search) url += `search=${encodeURIComponent(search)}&`;
  
  try {
    const response = await fetch(url);
    profitsData = await response.json();
    
    if (profitsData.success) {
      updateProfitOverview(profitsData);
      updateProfitPeriods(profitsData);
      updateProfitCharts(profitsData);
      updateProfitTables(profitsData);
    } else {
      showAlert(profitsData.message || 'Failed to load profit data', 'danger');
    }
  } catch (error) {
    console.error('Error loading profits:', error);
    showAlert('Error loading profit data', 'danger');
  }
}

function refreshProfitData() {
  loadProfitData();
  showAlert('Profit data refreshed!', 'success');
}

function updateProfitOverview(data) {
  const ov = data.overview;
  
  document.getElementById('totalProfit').textContent = '৳' + formatNumber(ov.total_profit);
  document.getElementById('profitOrders').textContent = ov.total_orders + ' orders';
  
  document.getElementById('totalRevenue').textContent = '৳' + formatNumber(ov.total_revenue);
  document.getElementById('revenueItems').textContent = ov.total_items_sold + ' items sold';
  
  document.getElementById('totalInvestment').textContent = '৳' + formatNumber(ov.total_investment);
  
  document.getElementById('profitMarginPercent').textContent = ov.profit_margin_percent + '%';
  document.getElementById('avgProfitItem').textContent = '৳' + formatNumber(ov.avg_profit_per_item) + ' avg/item';
}

function updateProfitPeriods(data) {
  const periods = data.periods;
  
  document.getElementById('todayProfit').textContent = '৳' + formatNumber(periods.today.profit);
  document.getElementById('todayProfitOrders').textContent = periods.today.orders + ' orders';
  
  document.getElementById('weekProfit').textContent = '৳' + formatNumber(periods.week.profit);
  document.getElementById('weekProfitOrders').textContent = periods.week.orders + ' orders';
  
  document.getElementById('monthProfit').textContent = '৳' + formatNumber(periods.month.profit);
  document.getElementById('monthProfitOrders').textContent = periods.month.orders + ' orders';
  
  document.getElementById('yearProfit').textContent = '৳' + formatNumber(periods.year.profit);
  document.getElementById('yearProfitOrders').textContent = periods.year.orders + ' orders';
}

function updateProfitCharts(data) {
  // Profit Trend Chart (Line)
  const trendCtx = document.getElementById('profitTrendChart').getContext('2d');
  if (profitTrendChart) profitTrendChart.destroy();
  
  profitTrendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
      labels: data.profit_trend.map(d => d.label),
      datasets: [
        {
          label: 'Profit',
          data: data.profit_trend.map(d => d.profit),
          borderColor: '#22c55e',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 2,
          pointBackgroundColor: '#22c55e'
        },
        {
          label: 'Revenue',
          data: data.profit_trend.map(d => d.revenue),
          borderColor: '#3b82f6',
          backgroundColor: 'transparent',
          borderDash: [5, 5],
          tension: 0.4,
          pointRadius: 0
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: { usePointStyle: true }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => '৳' + formatNumber(value)
          }
        }
      }
    }
  });
  
  // Profit by Category (Doughnut)
  const catCtx = document.getElementById('profitByCategoryChart').getContext('2d');
  if (profitByCategoryChart) profitByCategoryChart.destroy();
  
  const catColors = [
    '#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
    '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1'
  ];
  
  if (data.profit_by_category.length > 0) {
    profitByCategoryChart = new Chart(catCtx, {
      type: 'doughnut',
      data: {
        labels: data.profit_by_category.map(c => c.name),
        datasets: [{
          data: data.profit_by_category.map(c => c.profit),
          backgroundColor: catColors.slice(0, data.profit_by_category.length),
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              usePointStyle: true,
              font: { size: 11 }
            }
          }
        }
      }
    });
  } else {
    catCtx.font = '14px Arial';
    catCtx.fillStyle = '#94a3b8';
    catCtx.textAlign = 'center';
    catCtx.fillText('No profit data yet', catCtx.canvas.width / 2, catCtx.canvas.height / 2);
  }
  
  // Revenue vs Profit vs Investment (Monthly Bar)
  const compCtx = document.getElementById('revenueProfitChart').getContext('2d');
  if (revenueProfitChart) revenueProfitChart.destroy();
  
  if (data.monthly_comparison.length > 0) {
    revenueProfitChart = new Chart(compCtx, {
      type: 'bar',
      data: {
        labels: data.monthly_comparison.map(m => m.label),
        datasets: [
          {
            label: 'Revenue',
            data: data.monthly_comparison.map(m => m.revenue),
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
            borderRadius: 4
          },
          {
            label: 'Investment',
            data: data.monthly_comparison.map(m => m.investment),
            backgroundColor: 'rgba(245, 158, 11, 0.7)',
            borderRadius: 4
          },
          {
            label: 'Profit',
            data: data.monthly_comparison.map(m => m.profit),
            backgroundColor: 'rgba(34, 197, 94, 0.9)',
            borderRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: { usePointStyle: true }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => '৳' + formatNumber(value)
            }
          }
        }
      }
    });
  }
}

function updateProfitTables(data) {
  // Most Profitable Items
  const profitableHtml = data.most_profitable_items.map((item, i) => `
    <tr>
      <td><span class="badge ${i < 3 ? 'bg-warning text-dark' : 'bg-secondary'}">${i + 1}</span></td>
      <td><strong>${escapeHtml(item.name)}</strong></td>
      <td class="text-center">${item.units_sold}</td>
      <td class="text-end text-success fw-bold">৳${formatNumber(item.profit)}</td>
      <td class="text-end"><span class="badge bg-info">${item.margin}%</span></td>
    </tr>
  `).join('');
  document.getElementById('mostProfitableItems').innerHTML = profitableHtml || '<tr><td colspan="5" class="text-center text-muted py-3">No profit data yet</td></tr>';
  
  // Most Sold Items
  const soldHtml = data.most_sold_items.map((item, i) => `
    <tr>
      <td><span class="badge ${i < 3 ? 'bg-primary' : 'bg-secondary'}">${i + 1}</span></td>
      <td><strong>${escapeHtml(item.name)}</strong></td>
      <td class="text-center">${item.units_sold}</td>
      <td class="text-end">৳${formatNumber(item.revenue)}</td>
      <td class="text-end text-success fw-bold">৳${formatNumber(item.profit)}</td>
    </tr>
  `).join('');
  document.getElementById('mostSoldItems').innerHTML = soldHtml || '<tr><td colspan="5" class="text-center text-muted py-3">No sales data yet</td></tr>';
  
  // Recent Profit Records
  const recordsHtml = data.recent_records.map(rec => `
    <tr>
      <td><span class="badge bg-success">#${rec.order_id}</span></td>
      <td><strong>${escapeHtml(rec.product_name)}</strong></td>
      <td class="text-center">${rec.quantity}</td>
      <td class="text-end">৳${rec.selling_price.toFixed(0)}</td>
      <td class="text-end">৳${rec.buying_price.toFixed(0)}</td>
      <td class="text-end">৳${formatNumber(rec.revenue)}</td>
      <td class="text-end text-warning">৳${formatNumber(rec.investment)}</td>
      <td class="text-end text-success fw-bold">৳${formatNumber(rec.profit)}</td>
      <td><small>${new Date(rec.date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</small></td>
    </tr>
  `).join('');
  document.getElementById('profitRecordsTable').innerHTML = recordsHtml || '<tr><td colspan="9" class="text-center text-muted py-3">No profit records yet</td></tr>';
}

// Export Profit Report as PDF
function exportProfitPDF() {
  if (!profitsData) {
    showAlert('Please wait for profit data to load', 'warning');
    return;
  }
  
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p', 'mm', 'a4');
  const pageWidth = doc.internal.pageSize.getWidth();
  
  // Header
  doc.setFillColor(34, 197, 94);
  doc.rect(0, 0, pageWidth, 40, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(24);
  doc.setFont('helvetica', 'bold');
  doc.text('Green Bites', pageWidth / 2, 18, { align: 'center' });
  doc.setFontSize(14);
  doc.setFont('helvetica', 'normal');
  doc.text('Profit & Analytics Report', pageWidth / 2, 28, { align: 'center' });
  doc.setFontSize(10);
  doc.text('Generated: ' + new Date().toLocaleDateString(), pageWidth / 2, 36, { align: 'center' });
  
  let yPos = 55;
  doc.setTextColor(0, 0, 0);
  
  // Profit Summary
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Profit Summary', 15, yPos);
  yPos += 10;
  
  const ov = profitsData.overview;
  const summaryData = [
    ['Total Profit', 'TK ' + formatNumber(ov.total_profit)],
    ['Total Revenue', 'TK ' + formatNumber(ov.total_revenue)],
    ['Total Investment', 'TK ' + formatNumber(ov.total_investment)],
    ['Profit Margin', ov.profit_margin_percent + '%'],
    ['Total Orders', ov.total_orders.toString()],
    ['Items Sold', ov.total_items_sold.toString()]
  ];
  
  doc.autoTable({
    startY: yPos,
    head: [['Metric', 'Value']],
    body: summaryData,
    theme: 'grid',
    headStyles: { fillColor: [34, 197, 94] },
    margin: { left: 15, right: 15 },
    columnStyles: { 1: { halign: 'right' } }
  });
  
  yPos = doc.lastAutoTable.finalY + 15;
  
  // Most Profitable Items
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Most Profitable Items', 15, yPos);
  yPos += 10;
  
  const profitableData = profitsData.most_profitable_items.slice(0, 10).map((item, i) => [
    i + 1,
    item.name,
    item.units_sold,
    'TK ' + formatNumber(item.profit),
    item.margin + '%'
  ]);
  
  doc.autoTable({
    startY: yPos,
    head: [['#', 'Item Name', 'Units Sold', 'Profit', 'Margin']],
    body: profitableData,
    theme: 'grid',
    headStyles: { fillColor: [34, 197, 94] },
    margin: { left: 15, right: 15 }
  });
  
  // Footer
  const pageCount = doc.internal.getNumberOfPages();
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i);
    doc.setFontSize(9);
    doc.setTextColor(128, 128, 128);
    doc.text('Green Bites Canteen - Profit Report - Confidential', pageWidth / 2, 290, { align: 'center' });
  }
  
  doc.save('GreenBites_Profit_Report_' + new Date().toISOString().slice(0, 10) + '.pdf');
  showAlert('Profit report exported successfully!', 'success');
}
</script>
</body>
</html>

<?php
session_set_cookie_params(['path' => '/']);
session_start();
require_once __DIR__ . '/../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
      font-size: 1.5rem;
      font-weight: 700;
      color: #4ade80;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      display: flex;
      align-items: center;
      gap: 10px;
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
  </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <i class="bi bi-leaf"></i>
    Green Bites
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
      <span class="badge bg-success"><?php echo $stats['menu_items']; ?></span>
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
    </a>
    
    <div class="nav-section">Settings</div>
    <a href="#" class="nav-link" data-section="categories">
      <i class="bi bi-tag"></i>
      Categories
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
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon green"><i class="bi bi-egg-fried"></i></div>
          <div class="stat-value"><?php echo $stats['menu_items']; ?></div>
          <div class="stat-label">Menu Items</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-bag-check"></i></div>
          <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange"><i class="bi bi-clock-history"></i></div>
          <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
          <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="bi bi-people"></i></div>
          <div class="stat-value"><?php echo $stats['total_users']; ?></div>
          <div class="stat-label">Registered Users</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="bi bi-currency-dollar"></i></div>
          <div class="stat-value">৳<?php echo number_format($stats['total_revenue'], 0); ?></div>
          <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-calendar-check"></i></div>
          <div class="stat-value"><?php echo $stats['today_orders']; ?></div>
          <div class="stat-label">Today's Orders</div>
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
                  <th>Price</th>
                  <th>Description</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($menuItems as $item): ?>
                <tr data-id="<?php echo $item['id']; ?>" data-category="<?php echo $item['category_id']; ?>">
                  <td>
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/50'); ?>" 
                         class="menu-item-img" alt="<?php echo htmlspecialchars($item['title']); ?>">
                  </td>
                  <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                  <td><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></td>
                  <td><strong>৳<?php echo number_format($item['price'], 0); ?></strong></td>
                  <td class="text-muted"><?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 50)); ?>...</td>
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
                  <th>Customer</th>
                  <th>Items</th>
                  <th>Total</th>
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
                  <td>
                    <select class="form-select form-select-sm status-select" data-order-id="<?php echo $order['id']; ?>" style="width: 130px;">
                      <option value="Pending" <?php echo ($order['status'] ?? '') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                      <option value="Processing" <?php echo ($order['status'] ?? '') == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                      <option value="Completed" <?php echo ($order['status'] ?? '') == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                      <option value="Cancelled" <?php echo ($order['status'] ?? '') == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                  </td>
                  <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                  <td>
                    <button class="btn-action edit" onclick="viewOrder(<?php echo $order['id']; ?>)" title="View Details">
                      <i class="bi bi-eye"></i>
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
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="complaintsTableBody">
              <!-- Loaded via AJAX -->
            </tbody>
          </table>
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
            <div class="col-md-6 mb-3">
              <label class="form-label">Price (৳) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="price" id="menuPrice" min="0" step="0.01" required>
            </div>
            <div class="col-md-6 mb-3">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script>
const csrfToken = '<?php echo $csrf_token; ?>';
const menuData = <?php echo json_encode($menuItems); ?>;
const ordersData = <?php echo json_encode($recentOrders); ?>;

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

// Menu Item Functions
function openAddMenuModal() {
  document.getElementById('menuModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Menu Item';
  document.getElementById('menuForm').reset();
  document.getElementById('menuItemId').value = '';
}

function editMenuItem(id) {
  const item = menuData.find(m => m.id == id);
  if (!item) return;
  
  document.getElementById('menuModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Menu Item';
  document.getElementById('menuItemId').value = item.id;
  document.getElementById('menuName').value = item.title;
  document.getElementById('menuCategory').value = item.category_id;
  document.getElementById('menuPrice').value = item.price;
  document.getElementById('menuImage').value = item.image_url || '';
  document.getElementById('menuDescription').value = item.description || '';
  
  new bootstrap.Modal(document.getElementById('menuModal')).show();
}

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

// Order Status Update
document.querySelectorAll('.status-select').forEach(select => {
  select.addEventListener('change', function() {
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
  });
});

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
      tbody.innerHTML = result.complaints.map(c => `
        <tr>
          <td>${c.id}</td>
          <td><strong>${c.name}</strong></td>
          <td>${c.email}</td>
          <td>${c.message.substring(0, 100)}...</td>
          <td>${c.image_path ? `<a href="../${c.image_path}" target="_blank">View</a>` : '-'}</td>
          <td>${new Date(c.created_at).toLocaleDateString()}</td>
        </tr>
      `).join('');
    }
  } catch (err) {
    console.error(err);
  }
}

// Export PDF - Beautiful Order Report
function exportOrdersPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('l', 'mm', 'a4'); // Landscape for better table view
  
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  
  // Add Watermark
  doc.setTextColor(240, 240, 240);
  doc.setFontSize(60);
  doc.setFont('helvetica', 'bold');
  doc.text('GREEN BITES', pageWidth / 2, pageHeight / 2, { angle: 35, align: 'center' });
  doc.setFontSize(40);
  doc.text('GREEN BITES', 50, 80, { angle: 35 });
  doc.text('GREEN BITES', 200, 180, { angle: 35 });
  
  // Reset text color
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
</script>
</body>
</html>

<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch user's orders
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
mysqli_stmt_close($stmt);

// Get total spent
$totalSpent = 0;
foreach ($orders as $order) {
    $totalSpent += $order['total_price'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - Green Bites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .orders-container {
      max-width: 1000px;
      margin: 100px auto 50px;
      padding: 0 15px;
    }
    .orders-header {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: #fff;
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(22, 163, 74, 0.2);
    }
    .orders-title {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .orders-subtitle {
      opacity: 0.9;
    }
    .orders-stats {
      display: flex;
      gap: 30px;
      margin-top: 20px;
    }
    .stat-box {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 15px 25px;
      text-align: center;
    }
    .stat-box-value {
      font-size: 1.5rem;
      font-weight: 700;
    }
    .stat-box-label {
      font-size: 0.85rem;
      opacity: 0.85;
    }
    .order-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
      overflow: hidden;
      transition: all 0.3s;
    }
    .order-card:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      transform: translateY(-2px);
    }
    .order-card-header {
      background: #f8fafc;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #e5e7eb;
    }
    .order-id {
      font-weight: 700;
      color: #1f2937;
    }
    .order-date {
      color: #6b7280;
      font-size: 0.9rem;
    }
    .order-status {
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .status-pending {
      background: #fef3c7;
      color: #d97706;
    }
    .status-processing {
      background: #dbeafe;
      color: #2563eb;
    }
    .status-completed {
      background: #d1fae5;
      color: #059669;
    }
    .status-cancelled {
      background: #fee2e2;
      color: #dc2626;
    }
    .order-card-body {
      padding: 20px;
    }
    .order-items-list {
      margin-bottom: 15px;
    }
    .order-item-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #f3f4f6;
    }
    .order-item-row:last-child {
      border-bottom: none;
    }
    .order-item-name {
      color: #1f2937;
      font-weight: 500;
    }
    .order-item-qty {
      color: #6b7280;
      font-size: 0.9rem;
    }
    .order-item-price {
      font-weight: 600;
      color: #16a34a;
    }
    .order-total-row {
      display: flex;
      justify-content: space-between;
      padding-top: 15px;
      border-top: 2px solid #16a34a;
      font-weight: 700;
      font-size: 1.1rem;
    }
    .order-total-value {
      color: #16a34a;
    }
    .order-details {
      margin-top: 15px;
      padding: 12px;
      background: #f8fafc;
      border-radius: 10px;
      font-size: 0.9rem;
    }
    .order-details-label {
      font-weight: 600;
      color: #4b5563;
      margin-bottom: 5px;
    }
    .order-details-value {
      color: #6b7280;
    }
    .empty-orders {
      text-align: center;
      padding: 60px 20px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    .empty-orders-icon {
      font-size: 4rem;
      color: #d1d5db;
      margin-bottom: 20px;
    }
    .empty-orders h4 {
      color: #6b7280;
      margin-bottom: 10px;
    }
    .filter-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .filter-tab {
      padding: 8px 20px;
      border-radius: 20px;
      border: 2px solid #e5e7eb;
      background: #fff;
      color: #6b7280;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    .filter-tab:hover {
      border-color: #16a34a;
      color: #16a34a;
    }
    .filter-tab.active {
      background: #16a34a;
      border-color: #16a34a;
      color: #fff;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="orders-container">
  <!-- Header -->
  <div class="orders-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div>
        <h1 class="orders-title"><i class="bi bi-bag-check me-2"></i>My Orders</h1>
        <p class="orders-subtitle mb-0">Track and manage your order history</p>
      </div>
      <a href="lunch.php" class="btn btn-light">
        <i class="bi bi-plus-lg me-1"></i>New Order
      </a>
    </div>
    <div class="orders-stats">
      <div class="stat-box">
        <div class="stat-box-value"><?php echo count($orders); ?></div>
        <div class="stat-box-label">Total Orders</div>
      </div>
      <div class="stat-box">
        <div class="stat-box-value">৳<?php echo number_format($totalSpent, 0); ?></div>
        <div class="stat-box-label">Total Spent</div>
      </div>
    </div>
  </div>

  <?php if (empty($orders)): ?>
    <!-- Empty State -->
    <div class="empty-orders">
      <div class="empty-orders-icon">
        <i class="bi bi-bag-x"></i>
      </div>
      <h4>No orders yet</h4>
      <p class="text-muted mb-4">You haven't placed any orders. Start ordering delicious food!</p>
      <a href="lunch.php" class="btn btn-success btn-lg">
        <i class="bi bi-egg-fried me-2"></i>Browse Menu
      </a>
    </div>
  <?php else: ?>
    <!-- Filter Tabs -->
    <div class="filter-tabs">
      <button class="filter-tab active" data-filter="all">All Orders</button>
      <button class="filter-tab" data-filter="pending">Pending</button>
      <button class="filter-tab" data-filter="processing">Processing</button>
      <button class="filter-tab" data-filter="completed">Completed</button>
      <button class="filter-tab" data-filter="cancelled">Cancelled</button>
    </div>

    <!-- Orders List -->
    <div id="ordersList">
      <?php foreach ($orders as $order): 
        $items = json_decode($order['items'], true) ?: [];
        $status = strtolower($order['status'] ?? 'pending');
        $statusClass = 'status-' . $status;
      ?>
        <div class="order-card" data-status="<?php echo $status; ?>">
          <div class="order-card-header">
            <div>
              <span class="order-id">Order #<?php echo $order['id']; ?></span>
              <span class="order-date ms-3">
                <i class="bi bi-calendar3 me-1"></i>
                <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?>
              </span>
            </div>
            <span class="order-status <?php echo $statusClass; ?>">
              <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
            </span>
          </div>
          <div class="order-card-body">
            <div class="order-items-list">
              <?php foreach ($items as $item): ?>
                <div class="order-item-row">
                  <div>
                    <span class="order-item-name"><?php echo htmlspecialchars($item['title'] ?? $item['name'] ?? 'Item'); ?></span>
                    <span class="order-item-qty">× <?php echo $item['quantity'] ?? 1; ?></span>
                  </div>
                  <span class="order-item-price">
                    ৳<?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0); ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
            
            <div class="order-total-row">
              <span>Total</span>
              <span class="order-total-value">৳<?php echo number_format($order['total_price'], 0); ?></span>
            </div>

            <?php if (!empty($order['student_id']) || !empty($order['special_instructions'])): ?>
              <div class="order-details">
                <?php if (!empty($order['student_id'])): ?>
                  <div class="mb-2">
                    <span class="order-details-label"><i class="bi bi-person-badge me-1"></i>Student ID:</span>
                    <span class="order-details-value"><?php echo htmlspecialchars($order['student_id']); ?></span>
                  </div>
                <?php endif; ?>
                <?php if (!empty($order['special_instructions'])): ?>
                  <div>
                    <span class="order-details-label"><i class="bi bi-chat-text me-1"></i>Instructions:</span>
                    <span class="order-details-value"><?php echo htmlspecialchars($order['special_instructions']); ?></span>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Filter tabs functionality
  const filterTabs = document.querySelectorAll('.filter-tab');
  const orderCards = document.querySelectorAll('.order-card');

  filterTabs.forEach(tab => {
    tab.addEventListener('click', function() {
      // Remove active from all tabs
      filterTabs.forEach(t => t.classList.remove('active'));
      // Add active to clicked tab
      this.classList.add('active');

      const filter = this.dataset.filter;

      orderCards.forEach(card => {
        if (filter === 'all' || card.dataset.status === filter) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
});
</script>
</body>
</html>

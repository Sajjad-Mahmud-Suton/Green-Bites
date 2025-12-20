<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                        GREEN BITES - MY ORDERS                            ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../config/bootstrap.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . AUTH_URL . '/login.php');
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
  <link rel="icon" type="image/svg+xml" href="<?php echo IMAGES_URL; ?>/logo-icon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo CSS_URL; ?>/style.css">
  <!-- jsPDF Library for PDF Generation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
              <?php if (!empty($order['bill_number'])): ?>
                <span class="badge bg-success ms-2"><?php echo htmlspecialchars($order['bill_number']); ?></span>
              <?php endif; ?>
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

            <!-- Download Bill Button -->
            <div class="mt-3 text-end">
              <button class="btn btn-outline-success btn-sm download-bill-btn" 
                      data-order-id="<?php echo $order['id']; ?>"
                      data-bill-number="<?php echo htmlspecialchars($order['bill_number'] ?? ''); ?>"
                      data-order-date="<?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?>"
                      data-order-status="<?php echo ucfirst($order['status'] ?? 'Pending'); ?>"
                      data-order-total="<?php echo $order['total_price']; ?>"
                      data-order-items='<?php echo htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8'); ?>'
                      data-student-id="<?php echo htmlspecialchars($order['student_id'] ?? ''); ?>"
                      data-instructions="<?php echo htmlspecialchars($order['special_instructions'] ?? ''); ?>"
                      data-user-name="<?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Customer'); ?>"
                      data-user-email="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                <i class="bi bi-download me-1"></i>Download Bill (PDF)
              </button>
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

  // PDF Bill Download functionality
  document.querySelectorAll('.download-bill-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      generateOrderPDF(this);
    });
  });
});

// Generate PDF Bill with watermark
function generateOrderPDF(button) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  
  // Get order data from button attributes
  const orderId = button.dataset.orderId;
  const billNumber = button.dataset.billNumber || 'GB-' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '-' + orderId.padStart(4, '0');
  const orderDate = button.dataset.orderDate;
  const orderStatus = button.dataset.orderStatus;
  const orderTotal = parseFloat(button.dataset.orderTotal);
  const items = JSON.parse(button.dataset.orderItems || '[]');
  const studentId = button.dataset.studentId;
  const instructions = button.dataset.instructions;
  const userName = button.dataset.userName;
  const userEmail = button.dataset.userEmail;
  
  // Current date/time for bill generation
  const now = new Date();
  const generatedAt = now.toLocaleString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });

  // Page dimensions
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();

  // Add Text Watermarks
  doc.setTextColor(240, 240, 240);
  doc.setFontSize(50);
  doc.setFont('helvetica', 'bold');
  doc.text('GREEN BITES', pageWidth / 2, pageHeight / 2, { angle: 45, align: 'center' });
  doc.setFontSize(25);
  doc.text('GREEN BITES', 30, 100, { angle: 45 });
  doc.text('GREEN BITES', 120, 230, { angle: 45 });
  doc.text('GREEN BITES', 50, 200, { angle: 45 });
  doc.text('GREEN BITES', 130, 100, { angle: 45 });

  // Reset for content
  doc.setTextColor(0, 0, 0);

  // Header - Green background
  doc.setFillColor(34, 197, 94);
  doc.rect(0, 0, pageWidth, 45, 'F');

  // Logo/Brand Name
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(28);
  // Header Text
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(28);
  doc.setFont('helvetica', 'bold');
  doc.text('GREEN BITES', pageWidth / 2, 18, { align: 'center' });
  
  doc.setFontSize(11);
  doc.setFont('helvetica', 'normal');
  doc.text('Campus Canteen - Fresh & Healthy Food', pageWidth / 2, 28, { align: 'center' });
  
  // Customer Copy badge
  doc.setFillColor(255, 255, 255);
  doc.roundedRect(pageWidth / 2 - 25, 32, 50, 8, 2, 2, 'F');
  doc.setFontSize(9);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(34, 197, 94);
  doc.text('CUSTOMER COPY', pageWidth / 2, 38, { align: 'center' });

  // Reset text color
  doc.setTextColor(0, 0, 0);
  
  let y = 55;

  // Bill Number - Prominent display
  doc.setFillColor(34, 197, 94);
  doc.roundedRect(pageWidth / 2 - 40, y - 5, 80, 12, 3, 3, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.text('Bill No: ' + billNumber, pageWidth / 2, y + 3, { align: 'center' });
  doc.setTextColor(0, 0, 0);
  y += 15;

  // Bill Title
  doc.setFontSize(16);
  doc.setFont('helvetica', 'bold');
  doc.text('ORDER INVOICE', pageWidth / 2, y, { align: 'center' });
  y += 10;

  // Order Info Box
  doc.setFillColor(248, 250, 252);
  doc.roundedRect(15, y, pageWidth - 30, 35, 3, 3, 'F');
  
  doc.setFontSize(10);
  doc.setFont('helvetica', 'bold');
  doc.text('Order ID:', 20, y + 10);
  doc.text('Order Date:', 20, y + 20);
  doc.text('Status:', 20, y + 30);
  
  doc.setFont('helvetica', 'normal');
  doc.text('#' + orderId, 55, y + 10);
  doc.text(orderDate, 55, y + 20);
  
  // Status with color
  if (orderStatus.toLowerCase() === 'completed') {
    doc.setTextColor(34, 197, 94);
  } else if (orderStatus.toLowerCase() === 'cancelled') {
    doc.setTextColor(239, 68, 68);
  } else {
    doc.setTextColor(245, 158, 11);
  }
  doc.text(orderStatus, 55, y + 30);
  doc.setTextColor(0, 0, 0);
  
  // Customer info on right side
  doc.setFont('helvetica', 'bold');
  doc.text('Customer:', 110, y + 10);
  doc.text('Email:', 110, y + 20);
  if (studentId) {
    doc.text('Student ID:', 110, y + 30);
  }
  
  doc.setFont('helvetica', 'normal');
  doc.text(userName, 145, y + 10);
  doc.text(userEmail || 'N/A', 145, y + 20);
  if (studentId) {
    doc.text(studentId, 145, y + 30);
  }
  
  y += 45;

  // Items Table Header
  doc.setFillColor(34, 197, 94);
  doc.rect(15, y, pageWidth - 30, 10, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(10);
  doc.text('Item', 20, y + 7);
  doc.text('Qty', 120, y + 7);
  doc.text('Price', 145, y + 7);
  doc.text('Total', 175, y + 7);
  
  y += 15;
  doc.setTextColor(0, 0, 0);
  doc.setFont('helvetica', 'normal');

  // Items List
  let subtotal = 0;
  items.forEach((item, index) => {
    const itemName = item.title || item.name || 'Item';
    const qty = item.quantity || 1;
    const price = item.price || 0;
    const itemTotal = price * qty;
    subtotal += itemTotal;

    // Alternating row background
    if (index % 2 === 0) {
      doc.setFillColor(249, 250, 251);
      doc.rect(15, y - 5, pageWidth - 30, 10, 'F');
    }

    doc.text(itemName.substring(0, 30), 20, y);
    doc.text(qty.toString(), 125, y);
    doc.text('TK ' + price.toFixed(0), 145, y);
    doc.text('TK ' + itemTotal.toFixed(0), 175, y);
    y += 10;
  });

  y += 5;

  // Totals Section
  doc.setDrawColor(200, 200, 200);
  doc.line(15, y, pageWidth - 15, y);
  y += 10;

  doc.setFont('helvetica', 'normal');
  doc.text('Subtotal:', 140, y);
  doc.text('TK ' + subtotal.toFixed(0), 175, y);
  y += 8;

  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setFillColor(34, 197, 94);
  doc.rect(130, y - 5, pageWidth - 145, 12, 'F');
  doc.setTextColor(255, 255, 255);
  doc.text('TOTAL:', 140, y + 3);
  doc.text('TK ' + orderTotal.toFixed(0), 175, y + 3);
  
  doc.setTextColor(0, 0, 0);
  y += 20;

  // Special Instructions (if any)
  if (instructions) {
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.text('Special Instructions:', 20, y);
    doc.setFont('helvetica', 'normal');
    doc.text(instructions.substring(0, 80), 20, y + 8);
    y += 20;
  }

  // Footer Section
  y = pageHeight - 50;
  
  doc.setDrawColor(34, 197, 94);
  doc.setLineWidth(0.5);
  doc.line(15, y, pageWidth - 15, y);
  y += 10;

  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(100, 100, 100);
  doc.text('Thank you for ordering from Green Bites!', pageWidth / 2, y, { align: 'center' });
  y += 6;
  doc.text('For queries: +8801968-161494 | sajjadmahmudsuton@gmail.com', pageWidth / 2, y, { align: 'center' });
  y += 6;
  doc.text('Green Bites Campus Canteen, Bangladesh', pageWidth / 2, y, { align: 'center' });
  y += 10;
  
  doc.setFontSize(8);
  doc.text('Bill Generated: ' + generatedAt, pageWidth / 2, y, { align: 'center' });
  y += 5;
  doc.text('This is a computer generated bill.', pageWidth / 2, y, { align: 'center' });

  // Save PDF
  const fileName = 'GreenBites_Order_' + orderId + '_Bill.pdf';
  doc.save(fileName);
}
</script>
</body>
</html>

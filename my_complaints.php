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
 * ║  FILE: my_complaints.php                                                  ║
 * ║  DESCRIPTION: User's complaint history page                               ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'] ?? '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch user's complaints (by user_id or email)
$stmt = mysqli_prepare($conn, "SELECT * FROM complaints WHERE user_id = ? OR email = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'is', $user_id, $user_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$complaints = [];
while ($row = mysqli_fetch_assoc($result)) {
    $complaints[] = $row;
}
mysqli_stmt_close($stmt);

// Count by status
$statusCounts = [
    'all' => count($complaints),
    'pending' => 0,
    'seen' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'closed' => 0
];
foreach ($complaints as $c) {
    $status = $c['status'] ?? 'pending';
    if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Complaints - Green Bites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .complaints-container {
      max-width: 1000px;
      margin: 100px auto 50px;
      padding: 0 15px;
    }
    .complaints-header {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: #fff;
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(220, 38, 38, 0.2);
    }
    .complaints-title {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .complaints-subtitle {
      opacity: 0.9;
    }
    .complaints-stats {
      display: flex;
      gap: 20px;
      margin-top: 20px;
      flex-wrap: wrap;
    }
    .stat-box {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 12px 20px;
      text-align: center;
      min-width: 80px;
    }
    .stat-box-value {
      font-size: 1.3rem;
      font-weight: 700;
    }
    .stat-box-label {
      font-size: 0.8rem;
      opacity: 0.85;
    }
    .complaint-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
      overflow: hidden;
      transition: all 0.3s;
    }
    .complaint-card:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      transform: translateY(-2px);
    }
    .complaint-card-header {
      background: #f8fafc;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #e5e7eb;
      flex-wrap: wrap;
      gap: 10px;
    }
    .complaint-id {
      font-weight: 700;
      color: #1f2937;
    }
    .complaint-date {
      color: #6b7280;
      font-size: 0.9rem;
    }
    .complaint-status {
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .status-pending {
      background: #fef3c7;
      color: #d97706;
    }
    .status-seen {
      background: #dbeafe;
      color: #2563eb;
    }
    .status-in_progress {
      background: #e0e7ff;
      color: #4f46e5;
    }
    .status-resolved {
      background: #d1fae5;
      color: #059669;
    }
    .status-closed {
      background: #f3f4f6;
      color: #6b7280;
    }
    .complaint-card-body {
      padding: 20px;
    }
    .complaint-message {
      color: #374151;
      line-height: 1.6;
      margin-bottom: 15px;
    }
    .complaint-image {
      margin-top: 15px;
    }
    .complaint-image img {
      max-width: 200px;
      border-radius: 10px;
      cursor: pointer;
      transition: transform 0.3s;
    }
    .complaint-image img:hover {
      transform: scale(1.05);
    }
    .admin-response {
      margin-top: 20px;
      padding: 15px;
      background: linear-gradient(135deg, #ecfdf5, #d1fae5);
      border-radius: 12px;
      border-left: 4px solid #16a34a;
    }
    .admin-response-header {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      color: #16a34a;
      margin-bottom: 8px;
    }
    .admin-response-text {
      color: #374151;
      line-height: 1.6;
    }
    .admin-response-date {
      font-size: 0.85rem;
      color: #6b7280;
      margin-top: 8px;
    }
    .empty-complaints {
      text-align: center;
      padding: 60px 20px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    .empty-complaints-icon {
      font-size: 4rem;
      color: #d1d5db;
      margin-bottom: 20px;
    }
    .empty-complaints h4 {
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
      border-color: #ef4444;
      color: #ef4444;
    }
    .filter-tab.active {
      background: #ef4444;
      border-color: #ef4444;
      color: #fff;
    }
    .timeline-indicator {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 0.85rem;
      color: #9ca3af;
      margin-top: 10px;
    }
    .timeline-indicator i {
      color: #16a34a;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="complaints-container">
  <!-- Header -->
  <div class="complaints-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div>
        <h1 class="complaints-title"><i class="bi bi-chat-square-text me-2"></i>My Complaints</h1>
        <p class="complaints-subtitle mb-0">Track the status of your submitted complaints</p>
      </div>
      <a href="index.php#complaintSection" class="btn btn-light">
        <i class="bi bi-plus-lg me-1"></i>New Complaint
      </a>
    </div>
    <div class="complaints-stats">
      <div class="stat-box">
        <div class="stat-box-value"><?php echo $statusCounts['all']; ?></div>
        <div class="stat-box-label">Total</div>
      </div>
      <div class="stat-box">
        <div class="stat-box-value"><?php echo $statusCounts['pending']; ?></div>
        <div class="stat-box-label">Pending</div>
      </div>
      <div class="stat-box">
        <div class="stat-box-value"><?php echo $statusCounts['in_progress']; ?></div>
        <div class="stat-box-label">In Progress</div>
      </div>
      <div class="stat-box">
        <div class="stat-box-value"><?php echo $statusCounts['resolved']; ?></div>
        <div class="stat-box-label">Resolved</div>
      </div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="filter-tabs">
    <button class="filter-tab active" data-filter="all">All (<?php echo $statusCounts['all']; ?>)</button>
    <button class="filter-tab" data-filter="pending">Pending (<?php echo $statusCounts['pending']; ?>)</button>
    <button class="filter-tab" data-filter="seen">Seen (<?php echo $statusCounts['seen']; ?>)</button>
    <button class="filter-tab" data-filter="in_progress">In Progress (<?php echo $statusCounts['in_progress']; ?>)</button>
    <button class="filter-tab" data-filter="resolved">Resolved (<?php echo $statusCounts['resolved']; ?>)</button>
    <button class="filter-tab" data-filter="closed">Closed (<?php echo $statusCounts['closed']; ?>)</button>
  </div>

  <!-- Complaints List -->
  <?php if (empty($complaints)): ?>
  <div class="empty-complaints">
    <div class="empty-complaints-icon">
      <i class="bi bi-chat-square-text"></i>
    </div>
    <h4>No Complaints Yet</h4>
    <p class="text-muted mb-4">You haven't submitted any complaints. We hope everything is going well!</p>
    <a href="index.php" class="btn btn-success">
      <i class="bi bi-arrow-left me-1"></i>Back to Home
    </a>
  </div>
  <?php else: ?>
    <?php foreach ($complaints as $complaint): 
      $status = $complaint['status'] ?? 'pending';
      $statusLabels = [
        'pending' => 'Pending',
        'seen' => 'Seen by Admin',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed'
      ];
      $statusLabel = $statusLabels[$status] ?? 'Pending';
    ?>
    <div class="complaint-card" data-status="<?php echo $status; ?>">
      <div class="complaint-card-header">
        <div>
          <span class="complaint-id">Complaint #<?php echo $complaint['id']; ?></span>
          <span class="complaint-date ms-3">
            <i class="bi bi-calendar3 me-1"></i>
            <?php echo date('M d, Y - h:i A', strtotime($complaint['created_at'])); ?>
          </span>
        </div>
        <span class="complaint-status status-<?php echo $status; ?>">
          <?php 
            $icons = ['pending' => 'clock', 'seen' => 'eye', 'in_progress' => 'gear', 'resolved' => 'check-circle', 'closed' => 'x-circle'];
            echo '<i class="bi bi-' . ($icons[$status] ?? 'circle') . ' me-1"></i>';
          ?>
          <?php echo $statusLabel; ?>
        </span>
      </div>
      <div class="complaint-card-body">
        <div class="complaint-message">
          <?php echo nl2br(htmlspecialchars($complaint['message'])); ?>
        </div>
        
        <?php if (!empty($complaint['image_path'])): ?>
        <div class="complaint-image">
          <a href="<?php echo htmlspecialchars($complaint['image_path']); ?>" target="_blank">
            <img src="<?php echo htmlspecialchars($complaint['image_path']); ?>" alt="Complaint Image">
          </a>
          <small class="d-block text-muted mt-1"><i class="bi bi-image me-1"></i>Attached Image (click to view)</small>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($complaint['admin_response'])): ?>
        <div class="admin-response">
          <div class="admin-response-header">
            <i class="bi bi-reply-fill"></i>
            Admin Response
          </div>
          <div class="admin-response-text">
            <?php echo nl2br(htmlspecialchars($complaint['admin_response'])); ?>
          </div>
          <?php if (!empty($complaint['responded_at'])): ?>
          <div class="admin-response-date">
            <i class="bi bi-clock me-1"></i>
            Responded on <?php echo date('M d, Y - h:i A', strtotime($complaint['responded_at'])); ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="timeline-indicator">
          <i class="bi bi-check-circle-fill"></i> Submitted
          <?php if ($complaint['is_seen'] || $status !== 'pending'): ?>
          <span class="mx-2">—</span>
          <i class="bi bi-check-circle-fill"></i> Seen
          <?php endif; ?>
          <?php if (in_array($status, ['in_progress', 'resolved', 'closed'])): ?>
          <span class="mx-2">—</span>
          <i class="bi bi-check-circle-fill"></i> Processing
          <?php endif; ?>
          <?php if (in_array($status, ['resolved', 'closed'])): ?>
          <span class="mx-2">—</span>
          <i class="bi bi-check-circle-fill"></i> <?php echo $status === 'resolved' ? 'Resolved' : 'Closed'; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filter functionality
document.querySelectorAll('.filter-tab').forEach(tab => {
  tab.addEventListener('click', function() {
    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    
    const filter = this.dataset.filter;
    
    // Filter complaints
    document.querySelectorAll('.complaint-card').forEach(card => {
      if (filter === 'all' || card.dataset.status === filter) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  });
});
</script>
</body>
</html>

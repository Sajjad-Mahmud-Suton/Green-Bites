<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                       GREEN BITES - PROFILE PAGE                          ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

require_once __DIR__ . '/config/security.php';
initSecureSession();

require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = mysqli_prepare($conn, "SELECT id, full_name, username, email, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Generate CSRF token using security function
$csrf_token = generateCSRFToken();

// Count user orders
$orderStmt = mysqli_prepare($conn, "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
mysqli_stmt_bind_param($orderStmt, 'i', $user_id);
mysqli_stmt_execute($orderStmt);
$orderResult = mysqli_stmt_get_result($orderStmt);
$orderData = mysqli_fetch_assoc($orderResult);
$orderCount = $orderData['order_count'] ?? 0;
mysqli_stmt_close($orderStmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Green Bites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .profile-container {
      max-width: 800px;
      margin: 100px auto 50px;
      padding: 0 15px;
    }
    .profile-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    .profile-header {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: #fff;
      padding: 40px 30px;
      text-align: center;
    }
    .profile-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: #fff;
      color: #16a34a;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 3.5rem;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }
    .profile-name {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .profile-username {
      opacity: 0.9;
      font-size: 1rem;
    }
    .profile-stats {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-top: 25px;
    }
    .stat-item {
      text-align: center;
    }
    .stat-value {
      font-size: 1.8rem;
      font-weight: 700;
    }
    .stat-label {
      font-size: 0.85rem;
      opacity: 0.85;
    }
    .profile-body {
      padding: 30px;
    }
    .info-section {
      margin-bottom: 30px;
    }
    .info-section-title {
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .info-row {
      display: flex;
      padding: 15px 0;
      border-bottom: 1px solid #f3f4f6;
    }
    .info-row:last-child {
      border-bottom: none;
    }
    .info-label {
      width: 140px;
      font-weight: 500;
      color: #6b7280;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .info-value {
      flex: 1;
      color: #1f2937;
      font-weight: 500;
    }
    .edit-form {
      display: none;
    }
    .edit-form.active {
      display: block;
    }
    .view-mode.hidden {
      display: none;
    }
    .form-control:focus {
      border-color: #16a34a;
      box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
    }
    .btn-edit {
      background: #f3f4f6;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      color: #4b5563;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-edit:hover {
      background: #e5e7eb;
      color: #1f2937;
    }
    .quick-links {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
    }
    .quick-link-card {
      background: linear-gradient(135deg, #f0fdf4, #dcfce7);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      text-decoration: none;
      color: #1f2937;
      transition: all 0.3s;
      border: 2px solid transparent;
    }
    .quick-link-card:hover {
      border-color: #16a34a;
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(22, 163, 74, 0.15);
    }
    .quick-link-icon {
      font-size: 2rem;
      color: #16a34a;
      margin-bottom: 10px;
    }
    .quick-link-title {
      font-weight: 600;
      margin-bottom: 5px;
    }
    .quick-link-desc {
      font-size: 0.85rem;
      color: #6b7280;
    }
    .alert-toast {
      position: fixed;
      top: 100px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="profile-container">
  <!-- Toast Messages -->
  <div id="alertContainer"></div>

  <div class="profile-card">
    <!-- Profile Header -->
    <div class="profile-header">
      <div class="profile-avatar">
        <i class="bi bi-person-fill"></i>
      </div>
      <div class="profile-name" id="displayName"><?php echo htmlspecialchars($user['full_name']); ?></div>
      <div class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></div>
      
      <div class="profile-stats">
        <div class="stat-item">
          <div class="stat-value"><?php echo $orderCount; ?></div>
          <div class="stat-label">Orders</div>
        </div>
        <div class="stat-item">
          <div class="stat-value"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
          <div class="stat-label">Member Since</div>
        </div>
      </div>
    </div>

    <!-- Profile Body -->
    <div class="profile-body">
      <!-- Personal Information Section -->
      <div class="info-section">
        <div class="info-section-title">
          <span><i class="bi bi-person-lines-fill me-2"></i>Personal Information</span>
          <button class="btn-edit" id="editProfileBtn">
            <i class="bi bi-pencil me-1"></i>Edit
          </button>
        </div>

        <!-- View Mode -->
        <div id="viewMode" class="view-mode">
          <div class="info-row">
            <div class="info-label">
              <i class="bi bi-person"></i>Full Name
            </div>
            <div class="info-value" id="viewFullName"><?php echo htmlspecialchars($user['full_name']); ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">
              <i class="bi bi-at"></i>Username
            </div>
            <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">
              <i class="bi bi-envelope"></i>Email
            </div>
            <div class="info-value" id="viewEmail"><?php echo htmlspecialchars($user['email']); ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">
              <i class="bi bi-calendar"></i>Joined
            </div>
            <div class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
          </div>
        </div>

        <!-- Edit Mode -->
        <form id="editProfileForm" class="edit-form">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="full_name" id="editFullName" 
                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="editEmail" 
                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Username <small class="text-muted">(cannot be changed)</small></label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
          </div>
          <hr>
          <p class="text-muted small"><i class="bi bi-info-circle me-1"></i>Leave password fields empty if you don't want to change it.</p>
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" class="form-control" name="current_password" id="currentPassword" 
                   placeholder="Enter current password to make changes">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">New Password</label>
              <input type="password" class="form-control" name="new_password" id="newPassword" 
                     placeholder="Leave empty to keep current">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" name="confirm_password" id="confirmPassword" 
                     placeholder="Confirm new password">
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success" id="saveProfileBtn">
              <i class="bi bi-check-lg me-1"></i>Save Changes
            </button>
            <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn">
              Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- Quick Links -->
      <div class="info-section">
        <div class="info-section-title">
          <span><i class="bi bi-lightning me-2"></i>Quick Links</span>
        </div>
        <div class="quick-links">
          <a href="my_orders.php" class="quick-link-card">
            <div class="quick-link-icon"><i class="bi bi-bag-check"></i></div>
            <div class="quick-link-title">My Orders</div>
            <div class="quick-link-desc">View your order history</div>
          </a>
          <a href="lunch.php" class="quick-link-card">
            <div class="quick-link-icon"><i class="bi bi-egg-fried"></i></div>
            <div class="quick-link-title">Browse Menu</div>
            <div class="quick-link-desc">Order delicious food</div>
          </a>
          <a href="index.php#complaintsSection" class="quick-link-card">
            <div class="quick-link-icon"><i class="bi bi-chat-dots"></i></div>
            <div class="quick-link-title">Submit Complaint</div>
            <div class="quick-link-desc">Report an issue</div>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const editBtn = document.getElementById('editProfileBtn');
  const cancelBtn = document.getElementById('cancelEditBtn');
  const viewMode = document.getElementById('viewMode');
  const editForm = document.getElementById('editProfileForm');
  const alertContainer = document.getElementById('alertContainer');

  // Toggle edit mode
  editBtn.addEventListener('click', function() {
    viewMode.classList.add('hidden');
    editForm.classList.add('active');
    editBtn.style.display = 'none';
  });

  cancelBtn.addEventListener('click', function() {
    viewMode.classList.remove('hidden');
    editForm.classList.remove('active');
    editBtn.style.display = 'inline-block';
    // Reset form
    editForm.reset();
    document.getElementById('editFullName').value = document.getElementById('viewFullName').textContent;
    document.getElementById('editEmail').value = document.getElementById('viewEmail').textContent;
  });

  // Show alert
  function showAlert(message, type = 'success') {
    const alertId = 'alert-' + Date.now();
    const alertHtml = `
      <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show alert-toast" role="alert">
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
    alertContainer.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
      const alert = document.getElementById(alertId);
      if (alert) alert.remove();
    }, 5000);
  }

  // Handle form submit
  editForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validate passwords match
    if (newPassword && newPassword !== confirmPassword) {
      showAlert('New passwords do not match!', 'danger');
      return;
    }

    const saveBtn = document.getElementById('saveProfileBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    try {
      const formData = new FormData(editForm);

      const response = await fetch('api/update_profile.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const result = await response.json();

      if (result.success) {
        // Update displayed values
        document.getElementById('viewFullName').textContent = formData.get('full_name');
        document.getElementById('viewEmail').textContent = formData.get('email');
        document.getElementById('displayName').textContent = formData.get('full_name');

        // Switch back to view mode
        viewMode.classList.remove('hidden');
        editForm.classList.remove('active');
        editBtn.style.display = 'inline-block';

        // Clear password fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';

        showAlert('Profile updated successfully!', 'success');
      } else {
        showAlert(result.message || 'Failed to update profile.', 'danger');
      }
    } catch (err) {
      console.error(err);
      showAlert('Network error. Please try again.', 'danger');
    } finally {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Changes';
    }
  });
});
</script>
</body>
</html>

<?php
// Forgot password page
session_start();
require_once 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Green Bites</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, #22c55e 0, #16a34a 25%, #0f172a 80%);
            display: flex;
            flex-direction: column;
        }
        .auth-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 5rem;
            padding-bottom: 3rem;
        }
        .auth-card {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.45);
            overflow: hidden;
            max-width: 460px;
            width: 100%;
        }
        .auth-card-header {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            padding: 1.5rem 1.75rem;
        }
        .auth-card-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        .auth-card-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        .auth-card-body {
            padding: 1.75rem;
        }
        .form-control {
            border-radius: 999px;
            padding: 0.7rem 1rem;
            border-color: #e5e7eb;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 0.12rem rgba(34, 197, 94, 0.25);
        }
        .btn-auth {
            border-radius: 999px;
            padding: 0.65rem 1rem;
            font-weight: 600;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            box-shadow: 0 12px 25px rgba(34, 197, 94, 0.45);
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }
        .btn-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 35px rgba(34, 197, 94, 0.55);
            opacity: 0.95;
        }
        .link-muted {
            color: #6b7280;
            text-decoration: none;
        }
        .link-muted:hover {
            color: #16a34a;
            text-decoration: underline;
        }
        .auth-footer-text {
            font-size: 0.9rem;
        }
        .alert-auth {
            border-radius: 999px;
            padding: 0.5rem 0.9rem;
            font-size: 0.88rem;
        }
        .token-box {
            background: #f1f5f9;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.85rem;
            word-break: break-all;
        }
        .toast-container-auth {
            position: fixed;
            top: 5.25rem;
            right: 1.25rem;
            z-index: 1080;
        }
        .toast-auth {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 18px 35px rgba(15,23,42,0.35);
            border: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        .toast-auth-success {
            border-left: 4px solid #16a34a;
        }
        .toast-auth-error {
            border-left: 4px solid #dc2626;
        }
        .toast-auth span.icon {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        @media (max-width: 576px) {
            .auth-card {
                margin: 0 1rem;
            }
            .auth-wrapper {
                padding-top: 4.5rem;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="toast-container-auth" id="toastContainer"></div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <h1><i class="bi bi-key me-2"></i>Forgot Password</h1>
            <p>Enter your registered email to reset your password</p>
        </div>
        <div class="auth-card-body">
            <div id="alertPlaceholder"></div>

            <form id="forgotForm" novalidate>
                <input type="hidden" name="csrf_token" id="csrfToken" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-envelope text-success"></i>
                        </span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="form-text">We will send a password reset link to this email if it is registered.</div>
                </div>

                <button type="submit" class="btn btn-auth w-100" id="forgotBtn">
                    <span class="btn-text">Send Reset Link</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Sending...
                    </span>
                </button>
            </form>

            <div class="mt-3 small text-muted" id="resetTokenInfo" style="display:none;">
                <div class="mb-1 fw-semibold text-success">Development token (for testing only):</div>
                <div class="token-box" id="resetTokenBox"></div>
                <div class="mt-1">
                    Use this token in the reset URL:
                    <code>reset_password.php?token=&lt;token&gt;</code>
                </div>
            </div>

            <div class="text-center mt-3 auth-footer-text">
                Remembered your password?
                <a href="login.php" class="link-muted fw-semibold">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const forgotForm = document.getElementById('forgotForm');
  const forgotBtn = document.getElementById('forgotBtn');
  const alertPlaceholder = document.getElementById('alertPlaceholder');
  const toastContainer = document.getElementById('toastContainer');
  const resetTokenInfo = document.getElementById('resetTokenInfo');
  const resetTokenBox = document.getElementById('resetTokenBox');

  function setLoading(isLoading) {
    const text = forgotBtn.querySelector('.btn-text');
    const spinner = forgotBtn.querySelector('.btn-spinner');
    if (isLoading) {
      forgotBtn.disabled = true;
      text.classList.add('d-none');
      spinner.classList.remove('d-none');
    } else {
      forgotBtn.disabled = false;
      text.classList.remove('d-none');
      spinner.classList.add('d-none');
    }
  }

  function showAlert(message, type = 'danger') {
    alertPlaceholder.innerHTML = `
      <div class="alert alert-${type} alert-auth d-flex align-items-center" role="alert">
        <i class="bi ${type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'} me-2"></i>
        <span>${message}</span>
      </div>
    `;
  }

  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-auth ' + (type === 'success' ? 'toast-auth-success' : 'toast-auth-error');
    toast.innerHTML = `
      <span class="icon">${type === 'success' ? '✅' : '⚠️'}</span>
      <span>${message}</span>
    `;
    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('opacity-0');
      setTimeout(() => toast.remove(), 250);
    }, 3000);
  }

  forgotForm.addEventListener('submit', function (e) {
    e.preventDefault();
    alertPlaceholder.innerHTML = '';

    const email = forgotForm.email.value.trim();
    if (!email) {
      showAlert('Please enter your email address.');
      return;
    }

    setLoading(true);
    resetTokenInfo.style.display = 'none';
    resetTokenBox.textContent = '';

    const formData = new FormData(forgotForm);

    fetch('auth/forgot_password.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          showToast(json.message || 'If this email exists, a reset link has been sent.', 'success');
          showAlert('A password reset link has been sent to your email (if it is registered).', 'success');
          if (json.token) {
            resetTokenBox.textContent = json.token;
            resetTokenInfo.style.display = 'block';
          }
        } else {
          showAlert(json.message || 'Unable to process request. Please try again.');
        }
      })
      .catch(() => {
        showAlert('Network error. Please try again.');
      })
      .finally(() => {
        setLoading(false);
      });
  });
</script>
</body>
</html>



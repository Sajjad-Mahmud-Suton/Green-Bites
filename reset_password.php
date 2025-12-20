<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                   GREEN BITES - RESET PASSWORD PAGE                       ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

require_once __DIR__ . '/config/security.php';
initSecureSession();

require_once 'db.php';

// Generate CSRF token using security function
$csrf_token = generateCSRFToken();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Green Bites</title>
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
        .password-strength {
            height: 6px;
            border-radius: 999px;
            background-color: #e5e7eb;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #ef4444, #22c55e);
            transition: width 0.2s ease;
        }
        .password-strength-text {
            font-size: 0.8rem;
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
            <h1><i class="bi bi-shield-lock me-2"></i>Reset Password</h1>
            <p>Choose a new, strong password for your Green Bites account</p>
        </div>
        <div class="auth-card-body">
            <div id="alertPlaceholder"></div>

            <form id="resetForm" novalidate>
                <input type="hidden" name="csrf_token" id="csrfToken" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="token" id="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="mb-2">
                    <label class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-lock text-success"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" id="new_password" name="new_password" placeholder="Create a strong password" required minlength="8">
                    </div>
                    <div class="mt-2 password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-strength-text mt-1" id="passwordStrengthText">Password strength: <span class="fw-semibold">Weak</span></div>
                    <div class="form-text">Minimum 8 characters with uppercase, lowercase and numbers recommended.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-lock-fill text-success"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="Repeat your new password" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn btn-auth w-100" id="resetBtn">
                    <span class="btn-text">Reset Password</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Updating...
                    </span>
                </button>
            </form>

            <div class="text-center mt-3 auth-footer-text">
                Back to
                <a href="login.php" class="link-muted fw-semibold">Login</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const resetForm = document.getElementById('resetForm');
  const resetBtn = document.getElementById('resetBtn');
  const alertPlaceholder = document.getElementById('alertPlaceholder');
  const toastContainer = document.getElementById('toastContainer');
  const passwordInput = document.getElementById('new_password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const tokenInput = document.getElementById('token');
  const strengthBar = document.getElementById('passwordStrengthBar');
  const strengthText = document.getElementById('passwordStrengthText');

  function setLoading(isLoading) {
    const text = resetBtn.querySelector('.btn-text');
    const spinner = resetBtn.querySelector('.btn-spinner');
    if (isLoading) {
      resetBtn.disabled = true;
      text.classList.add('d-none');
      spinner.classList.remove('d-none');
    } else {
      resetBtn.disabled = false;
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

  function evaluatePasswordStrength(password) {
    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    let width = (score / 5) * 100;
    strengthBar.style.width = width + '%';

    let label = 'Weak';
    let color = '#ef4444';

    if (score >= 4) {
      label = 'Strong';
      color = '#16a34a';
    } else if (score === 3) {
      label = 'Medium';
      color = '#f59e0b';
    }

    strengthBar.style.background = color;
    strengthText.innerHTML = 'Password strength: <span class="fw-semibold" style="color:' + color + ';">' + label + '</span>';
  }

  passwordInput.addEventListener('input', function () {
    evaluatePasswordStrength(passwordInput.value);
  });

  resetForm.addEventListener('submit', function (e) {
    e.preventDefault();
    alertPlaceholder.innerHTML = '';

    const token = tokenInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (!token) {
      showAlert('Reset token is missing. Please use the link from your email again.');
      return;
    }

    if (!password || !confirmPassword) {
      showAlert('Please enter and confirm your new password.');
      return;
    }

    if (password !== confirmPassword) {
      showAlert('Passwords do not match.');
      return;
    }

    if (password.length < 8) {
      showAlert('Password must be at least 8 characters long.');
      return;
    }

    setLoading(true);

    const formData = new FormData(resetForm);

    fetch('auth/reset_password.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          showToast(json.message || 'Password reset successful.', 'success');
          showAlert(json.message || 'Password reset successful.', 'success');
          setTimeout(() => {
            window.location.href = json.redirect || 'login.php';
          }, 900);
        } else {
          showAlert(json.message || 'Unable to reset password. Please try again.');
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



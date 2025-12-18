<?php
// Login page
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
    <title>Login - Green Bites</title>
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
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.45);
            overflow: hidden;
            max-width: 420px;
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
        .spinner-border-sm {
            --bs-spinner-border-width: 0.18em;
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
            <h1><i class="bi bi-box-arrow-in-right me-2"></i>Welcome Back</h1>
            <p>Login to continue ordering your favorite meals</p>
        </div>
        <div class="auth-card-body">
            <div id="alertPlaceholder"></div>

            <form id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" id="csrfToken" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-envelope text-success"></i>
                        </span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="form-text">Use the email you registered with Green Bites.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-lock text-success"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required minlength="8">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="forgot_password.php" class="link-muted small">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-auth w-100" id="loginBtn">
                    <span class="btn-text">Login</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Processing...
                    </span>
                </button>
            </form>

            <div class="text-center mt-3 auth-footer-text">
                Don't have an account?
                <a href="signup.php" class="link-muted fw-semibold">Sign up here</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const loginForm = document.getElementById('loginForm');
  const loginBtn = document.getElementById('loginBtn');
  const alertPlaceholder = document.getElementById('alertPlaceholder');
  const toastContainer = document.getElementById('toastContainer');

  function setLoading(isLoading) {
    const text = loginBtn.querySelector('.btn-text');
    const spinner = loginBtn.querySelector('.btn-spinner');
    if (isLoading) {
      loginBtn.disabled = true;
      text.classList.add('d-none');
      spinner.classList.remove('d-none');
    } else {
      loginBtn.disabled = false;
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

  loginForm.addEventListener('submit', function (e) {
    e.preventDefault();
    alertPlaceholder.innerHTML = '';

    const email = loginForm.email.value.trim();
    const password = loginForm.password.value;

    if (!email || !password) {
      showAlert('Please fill in both email and password.');
      return;
    }

    setLoading(true);

    const formData = new FormData(loginForm);

    fetch('auth/login.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          showToast(json.message || 'Login successful.', 'success');
          setTimeout(() => {
            window.location.href = json.redirect || 'index.php';
          }, 800);
        } else {
          showAlert(json.message || 'Unable to login. Please try again.');
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



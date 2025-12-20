<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GREEN BITES - SIGNUP PAGE VIEW                         ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../../config/bootstrap.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Green Bites</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="<?php echo IMAGES_URL; ?>/logo-icon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/style.css">
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
        .username-status {
            font-size: 0.8rem;
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
            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <img src="images/logo-icon.svg" alt="Green Bites" style="height: 50px; width: auto; filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));">
            </div>
            <h1 class="mb-1"><span style="color: #4ade80;">Green</span> <span style="color: #fff;">Bites</span></h1>
            <p>Join Green Bites and enjoy a smarter canteen experience</p>
        </div>
        <div class="auth-card-body">
            <div id="alertPlaceholder"></div>

            <form id="signupForm" novalidate>
                <input type="hidden" name="csrf_token" id="csrfToken" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-person text-success"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="full_name" name="full_name" placeholder="Enter your name" required>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-at text-success"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Enter your username" required minlength="3">
                    </div>
                    <div id="usernameStatus" class="username-status mt-1"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-envelope text-success"></i>
                        </span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-lock text-success"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Create a strong password" required minlength="8">
                    </div>
                    <div class="mt-2 password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-strength-text mt-1" id="passwordStrengthText">Password strength: <span class="fw-semibold">Weak</span></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-lock-fill text-success"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn btn-auth w-100" id="signupBtn">
                    <span class="btn-text">Sign Up</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Creating account...
                    </span>
                </button>
            </form>

            <div class="text-center mt-3 auth-footer-text">
                Already have an account?
                <a href="login.php" class="link-muted fw-semibold">Login here</a>
            </div>
        </div>
    </div>
    </div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const signupForm = document.getElementById('signupForm');
  const signupBtn = document.getElementById('signupBtn');
  const alertPlaceholder = document.getElementById('alertPlaceholder');
  const toastContainer = document.getElementById('toastContainer');
  const usernameInput = document.getElementById('username');
  const usernameStatus = document.getElementById('usernameStatus');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const strengthBar = document.getElementById('passwordStrengthBar');
  const strengthText = document.getElementById('passwordStrengthText');

  let usernameCheckTimeout = null;

  function setLoading(isLoading) {
    const text = signupBtn.querySelector('.btn-text');
    const spinner = signupBtn.querySelector('.btn-spinner');
    if (isLoading) {
      signupBtn.disabled = true;
      text.classList.add('d-none');
      spinner.classList.remove('d-none');
    } else {
      signupBtn.disabled = false;
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

  usernameInput.addEventListener('input', function () {
    const username = usernameInput.value.trim();
    usernameStatus.textContent = '';
    usernameStatus.className = 'username-status mt-1';

    if (username.length < 3) {
      return;
    }

    if (usernameCheckTimeout) {
      clearTimeout(usernameCheckTimeout);
    }

    usernameCheckTimeout = setTimeout(() => {
      const formData = new FormData();
      formData.append('username', username);
      formData.append('csrf_token', document.getElementById('csrfToken').value);

      usernameStatus.innerHTML = '<span class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Checking username...</span>';

      fetch('auth/check_username.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      })
        .then(res => res.json())
        .then(json => {
          if (json && json.available) {
            usernameStatus.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Username is available</span>';
          } else {
            usernameStatus.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Username is already taken</span>';
          }
        })
        .catch(() => {
          usernameStatus.innerHTML = '<span class="text-muted"><i class="bi bi-exclamation-circle me-1"></i>Could not verify username</span>';
        });
    }, 400);
  });

  signupForm.addEventListener('submit', function (e) {
    e.preventDefault();
    alertPlaceholder.innerHTML = '';

    const fullName = signupForm.full_name.value.trim();
    const username = signupForm.username.value.trim();
    const email = signupForm.email.value.trim();
    const password = signupForm.password.value;
    const confirmPassword = signupForm.confirm_password.value;

    if (!fullName || !username || !email || !password || !confirmPassword) {
      showAlert('Please fill in all required fields.');
      return;
    }

    if (password !== confirmPassword) {
      showAlert('Passwords do not match.');
      return;
    }

    setLoading(true);

    const formData = new FormData(signupForm);

    fetch('auth/register.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          showToast(json.message || 'Account created successfully.', 'success');
          setTimeout(() => {
            window.location.href = json.redirect || 'login.php';
          }, 900);
        } else {
          showAlert(json.message || 'Unable to create account. Please try again.');
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



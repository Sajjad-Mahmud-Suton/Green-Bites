<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                        GREEN BITES - SIGNUP PAGE                          ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

require_once __DIR__ . '/config/security.php';
initSecureSession();

require_once 'db.php';

// Generate CSRF token using security function
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Green Bites</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="images/logo-icon.svg">
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
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            z-index: 10;
        }
        .password-toggle:hover {
            color: #22c55e;
        }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .form-control {
            padding-right: 40px;
        }
        .otp-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.25);
        }
        .otp-input.filled {
            background-color: #f0fdf4;
            border-color: #22c55e;
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
                        <div class="password-wrapper flex-grow-1">
                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Create a strong password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
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
                        <div class="password-wrapper flex-grow-1">
                            <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-auth w-100" id="signupBtn">
                    <span class="btn-text"><i class="bi bi-envelope-arrow-up me-2"></i>Sign Up & Verify Email</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Sending verification code...
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

<!-- OTP Verification Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none;">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #22c55e, #16a34a); border: none;">
        <h5 class="modal-title"><i class="bi bi-envelope-check me-2"></i>Verify Your Email</h5>
      </div>
      <div class="modal-body text-center p-4">
        <div class="mb-3">
          <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #22c55e20, #16a34a20); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
            <i class="bi bi-envelope-paper text-success" style="font-size: 2.5rem;"></i>
          </div>
        </div>
        <h5 class="mb-2">Check Your Email</h5>
        <p class="text-muted mb-4">We've sent a 6-digit verification code to<br><strong id="otpEmailDisplay"></strong></p>
        
        <div id="otpAlertPlaceholder"></div>
        
        <div class="mb-4">
          <label class="form-label fw-semibold">Enter Verification Code</label>
          <div class="d-flex justify-content-center gap-2" id="otpInputContainer">
            <input type="text" class="form-control otp-input text-center" maxlength="1" style="width: 50px; height: 55px; font-size: 1.5rem; font-weight: 600; border-radius: 12px;" data-index="0">
            <input type="text" class="form-control otp-input text-center" maxlength="1" style="width: 50px; height: 55px; font-size: 1.5rem; font-weight: 600; border-radius: 12px;" data-index="1">
            <input type="text" class="form-control otp-input text-center" maxlength="1" style="width: 50px; height: 55px; font-size: 1.5rem; font-weight: 600; border-radius: 12px;" data-index="2">
            <input type="text" class="form-control otp-input text-center" maxlength="1" style="width: 50px; height: 55px; font-size: 1.5rem; font-weight: 600; border-radius: 12px;" data-index="3">
            <input type="text" class="form-control otp-input text-center" maxlength="1" style="width: 50px; height: 55px; font-size: 1.5rem; font-weight: 600; border-radius: 12px;" data-index="4">
            <input type="text" class="form-control otp-input text-center" maxlength="1" style="width: 50px; height: 55px; font-size: 1.5rem; font-weight: 600; border-radius: 12px;" data-index="5">
          </div>
        </div>
        
        <div class="mb-3">
          <p class="text-muted small mb-2">Code expires in <span id="otpTimer" class="fw-bold text-success">10:00</span></p>
          <button type="button" class="btn btn-link text-success p-0" id="resendOtpBtn" disabled onclick="resendOTP()">
            <i class="bi bi-arrow-clockwise me-1"></i>Resend Code
          </button>
        </div>
        
        <button type="button" class="btn btn-auth w-100" id="verifyOtpBtn" onclick="verifyOTP()">
          <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Verify & Create Account</span>
          <span class="btn-spinner d-none">
            <span class="spinner-border spinner-border-sm me-1"></span>
            Verifying...
          </span>
        </button>
        
        <button type="button" class="btn btn-link text-muted mt-3" onclick="closeOtpModal()">
          <i class="bi bi-arrow-left me-1"></i>Go Back & Edit Details
        </button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Password toggle function
  function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('bi-eye-slash');
      icon.classList.add('bi-eye');
    }
  }

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

    // Password strength check
    if (password.length < 8) {
      showAlert('Password must be at least 8 characters.');
      return;
    }

    setLoading(true);

    // Send OTP to email
    const formData = new FormData();
    formData.append('email', email);
    formData.append('full_name', fullName);
    formData.append('username', username);

    fetch('auth/send_otp.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          // Show OTP modal
          showOtpModal(email);
        } else {
          showAlert(json.message || 'Failed to send verification code.');
        }
      })
      .catch(() => {
        showAlert('Network error. Please try again.');
      })
      .finally(() => {
        setLoading(false);
      });
  });

  // OTP Modal Functions
  let otpModal = null;
  let otpTimerInterval = null;
  let otpTimeLeft = 120; // 2 minutes in seconds

  function showOtpModal(email) {
    document.getElementById('otpEmailDisplay').textContent = email;
    clearOtpInputs();
    startOtpTimer();
    
    otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
    otpModal.show();
    
    // Focus first input
    setTimeout(() => {
      document.querySelector('.otp-input[data-index="0"]').focus();
    }, 300);
  }

  function closeOtpModal() {
    if (otpModal) {
      otpModal.hide();
    }
    clearInterval(otpTimerInterval);
  }

  function clearOtpInputs() {
    document.querySelectorAll('.otp-input').forEach(input => {
      input.value = '';
      input.classList.remove('filled');
    });
    document.getElementById('otpAlertPlaceholder').innerHTML = '';
  }

  function startOtpTimer() {
    otpTimeLeft = 120;
    document.getElementById('resendOtpBtn').disabled = true;
    
    clearInterval(otpTimerInterval);
    otpTimerInterval = setInterval(() => {
      otpTimeLeft--;
      const minutes = Math.floor(otpTimeLeft / 60);
      const seconds = otpTimeLeft % 60;
      document.getElementById('otpTimer').textContent = 
        `${minutes}:${seconds.toString().padStart(2, '0')}`;
      
      if (otpTimeLeft <= 0) {
        clearInterval(otpTimerInterval);
        document.getElementById('otpTimer').textContent = 'Expired';
        document.getElementById('otpTimer').classList.remove('text-success');
        document.getElementById('otpTimer').classList.add('text-danger');
        document.getElementById('resendOtpBtn').disabled = false;
      }
    }, 1000);
  }

  function resendOTP() {
    const email = signupForm.email.value.trim();
    const fullName = signupForm.full_name.value.trim();
    
    document.getElementById('resendOtpBtn').disabled = true;
    document.getElementById('resendOtpBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
    
    const formData = new FormData();
    formData.append('email', email);
    formData.append('full_name', fullName);
    
    fetch('auth/send_otp.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          showOtpAlert('New code sent successfully!', 'success');
          clearOtpInputs();
          startOtpTimer();
          document.getElementById('otpTimer').classList.remove('text-danger');
          document.getElementById('otpTimer').classList.add('text-success');
        } else {
          showOtpAlert(json.message || 'Failed to resend code.', 'danger');
          document.getElementById('resendOtpBtn').disabled = false;
        }
      })
      .catch(() => {
        showOtpAlert('Network error. Please try again.', 'danger');
        document.getElementById('resendOtpBtn').disabled = false;
      })
      .finally(() => {
        document.getElementById('resendOtpBtn').innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Resend Code';
      });
  }

  function showOtpAlert(message, type) {
    document.getElementById('otpAlertPlaceholder').innerHTML = `
      <div class="alert alert-${type} py-2 rounded-pill" role="alert">
        <small>${message}</small>
      </div>
    `;
  }

  function getOtpValue() {
    let otp = '';
    document.querySelectorAll('.otp-input').forEach(input => {
      otp += input.value;
    });
    return otp;
  }

  function verifyOTP() {
    const otp = getOtpValue();
    
    if (otp.length !== 6) {
      showOtpAlert('Please enter the complete 6-digit code.', 'danger');
      return;
    }
    
    const verifyBtn = document.getElementById('verifyOtpBtn');
    const btnText = verifyBtn.querySelector('.btn-text');
    const btnSpinner = verifyBtn.querySelector('.btn-spinner');
    
    verifyBtn.disabled = true;
    btnText.classList.add('d-none');
    btnSpinner.classList.remove('d-none');
    
    const formData = new FormData();
    formData.append('otp', otp);
    formData.append('full_name', signupForm.full_name.value.trim());
    formData.append('username', signupForm.username.value.trim());
    formData.append('email', signupForm.email.value.trim());
    formData.append('password', signupForm.password.value);
    
    fetch('auth/verify_otp.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          showOtpAlert('✅ ' + json.message, 'success');
          setTimeout(() => {
            window.location.href = json.redirect || 'login.php';
          }, 1500);
        } else {
          showOtpAlert(json.message || 'Invalid verification code.', 'danger');
          verifyBtn.disabled = false;
          btnText.classList.remove('d-none');
          btnSpinner.classList.add('d-none');
        }
      })
      .catch(() => {
        showOtpAlert('Network error. Please try again.', 'danger');
        verifyBtn.disabled = false;
        btnText.classList.remove('d-none');
        btnSpinner.classList.add('d-none');
      });
  }

  // OTP Input Handlers
  document.querySelectorAll('.otp-input').forEach((input, index) => {
    input.addEventListener('input', function(e) {
      const value = e.target.value;
      
      // Only allow numbers
      e.target.value = value.replace(/[^0-9]/g, '');
      
      if (e.target.value) {
        e.target.classList.add('filled');
        // Move to next input
        const nextInput = document.querySelector(`.otp-input[data-index="${index + 1}"]`);
        if (nextInput) {
          nextInput.focus();
        }
      } else {
        e.target.classList.remove('filled');
      }
      
      // Auto-verify when all fields are filled
      if (getOtpValue().length === 6) {
        verifyOTP();
      }
    });
    
    input.addEventListener('keydown', function(e) {
      // Handle backspace
      if (e.key === 'Backspace' && !e.target.value) {
        const prevInput = document.querySelector(`.otp-input[data-index="${index - 1}"]`);
        if (prevInput) {
          prevInput.focus();
          prevInput.value = '';
          prevInput.classList.remove('filled');
        }
      }
    });
    
    // Handle paste
    input.addEventListener('paste', function(e) {
      e.preventDefault();
      const pastedData = (e.clipboardData || window.clipboardData).getData('text');
      const digits = pastedData.replace(/[^0-9]/g, '').substring(0, 6);
      
      digits.split('').forEach((digit, i) => {
        const targetInput = document.querySelector(`.otp-input[data-index="${i}"]`);
        if (targetInput) {
          targetInput.value = digit;
          targetInput.classList.add('filled');
        }
      });
      
      // Focus last filled or next empty
      const nextEmpty = document.querySelector('.otp-input:not(.filled)');
      if (nextEmpty) {
        nextEmpty.focus();
      } else if (digits.length === 6) {
        verifyOTP();
      }
    });
  });
</script>
</body>
</html>



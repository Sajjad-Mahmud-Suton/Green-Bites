<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../db.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password, full_name FROM admins WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_username'] = $admin['username'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Green Bites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
      font-family: 'Segoe UI', system-ui, sans-serif;
      padding: 20px;
    }
    .login-container {
      width: 100%;
      max-width: 420px;
    }
    .login-card {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
      overflow: hidden;
    }
    .login-header {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: #fff;
      text-align: center;
      padding: 40px 30px;
    }
    .login-logo {
      width: 80px;
      height: 80px;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 2.5rem;
    }
    .login-header h1 {
      font-size: 1.8rem;
      font-weight: 700;
      margin: 0 0 5px;
    }
    .login-header p {
      opacity: 0.9;
      margin: 0;
    }
    .login-body {
      padding: 40px 30px;
    }
    .form-floating {
      margin-bottom: 20px;
    }
    .form-floating .form-control {
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 16px;
      height: 60px;
      font-size: 1rem;
    }
    .form-floating .form-control:focus {
      border-color: #16a34a;
      box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.1);
    }
    .form-floating label {
      padding: 16px;
      color: #64748b;
    }
    .btn-login {
      width: 100%;
      padding: 16px;
      font-size: 1.1rem;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: #fff;
      transition: all 0.3s;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(22, 163, 74, 0.3);
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: #64748b;
      text-decoration: none;
    }
    .back-link:hover {
      color: #16a34a;
    }
    .alert {
      border-radius: 12px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">
          <i class="bi bi-shield-lock"></i>
        </div>
        <h1>Admin Login</h1>
        <p>Green Bites Control Panel</p>
      </div>
      <div class="login-body">
        <?php if ($error): ?>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
        
        <form method="POST">
          <div class="form-floating">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
            <label for="username"><i class="bi bi-person me-2"></i>Username or Email</label>
          </div>
          <div class="form-floating">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
          </div>
          <button type="submit" class="btn btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
          </button>
        </form>
        
        <a href="../index.php" class="back-link">
          <i class="bi bi-arrow-left me-1"></i>Back to Website
        </a>
      </div>
    </div>
  </div>
</body>
</html>

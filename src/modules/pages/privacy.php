<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                     GREEN BITES - PRIVACY POLICY                          ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../config/bootstrap.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privacy Policy - Green Bites</title>
  <link rel="icon" type="image/svg+xml" href="<?php echo IMAGES_URL; ?>/logo-icon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo CSS_URL; ?>/style.css">
  <style>
    .legal-hero {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      padding: 100px 0 60px;
      color: #fff;
      text-align: center;
    }
    .legal-hero h1 {
      font-size: 2.5rem;
      font-weight: 700;
    }
    .legal-container {
      max-width: 900px;
      margin: -40px auto 60px;
      padding: 0 15px;
    }
    .legal-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      padding: 50px;
    }
    .legal-section {
      margin-bottom: 35px;
    }
    .legal-section h2 {
      font-size: 1.4rem;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    .legal-section h2 i {
      color: #22c55e;
      margin-right: 10px;
    }
    .legal-section p, .legal-section li {
      color: #6b7280;
      line-height: 1.8;
    }
    .legal-section ul {
      padding-left: 20px;
    }
    .legal-section li {
      margin-bottom: 8px;
    }
    .update-date {
      background: #f0fdf4;
      color: #16a34a;
      padding: 10px 20px;
      border-radius: 10px;
      font-size: 0.9rem;
      display: inline-block;
      margin-bottom: 30px;
    }
    .highlight-box {
      background: #fef3c7;
      border-left: 4px solid #f59e0b;
      padding: 15px 20px;
      border-radius: 0 10px 10px 0;
      margin: 20px 0;
    }
    .highlight-box p {
      color: #92400e;
      margin: 0;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<section class="legal-hero">
  <div class="container">
    <h1><i class="bi bi-shield-lock me-2"></i>Privacy Policy</h1>
    <p class="lead mb-0">Your privacy is important to us</p>
  </div>
</section>

<div class="legal-container">
  <div class="legal-card">
    <div class="update-date">
      <i class="bi bi-calendar3 me-2"></i>Last Updated: December 2025
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-info-circle-fill"></i>Introduction</h2>
      <p>
        Green Bites ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains 
        how we collect, use, disclose, and safeguard your information when you use our website and services.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-collection-fill"></i>Information We Collect</h2>
      <p>We collect information that you provide directly to us:</p>
      <ul>
        <li><strong>Account Information:</strong> Name, email address, phone number, password</li>
        <li><strong>Order Information:</strong> Items ordered, order history, delivery preferences</li>
        <li><strong>Payment Information:</strong> Payment method details (processed securely)</li>
        <li><strong>Communication Data:</strong> Complaints, feedback, and support inquiries</li>
        <li><strong>Device Information:</strong> IP address, browser type, operating system</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-gear-fill"></i>How We Use Your Information</h2>
      <p>We use the collected information to:</p>
      <ul>
        <li>Process and fulfill your food orders</li>
        <li>Create and manage your user account</li>
        <li>Send order confirmations and updates</li>
        <li>Respond to your inquiries and complaints</li>
        <li>Improve our services and user experience</li>
        <li>Send promotional offers (with your consent)</li>
        <li>Prevent fraud and ensure security</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-share-fill"></i>Information Sharing</h2>
      <p>We do not sell your personal information. We may share your information only:</p>
      <ul>
        <li>With service providers who assist in our operations</li>
        <li>To comply with legal obligations or court orders</li>
        <li>To protect our rights and prevent fraud</li>
        <li>With your explicit consent</li>
      </ul>
    </div>

    <div class="highlight-box">
      <p><i class="bi bi-exclamation-triangle me-2"></i><strong>Important:</strong> We never share your personal data with third parties for marketing purposes without your explicit consent.</p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-lock-fill"></i>Data Security</h2>
      <p>
        We implement appropriate security measures to protect your personal information, including:
      </p>
      <ul>
        <li>Encrypted data transmission (SSL/TLS)</li>
        <li>Secure password hashing</li>
        <li>Regular security audits</li>
        <li>Access controls and authentication</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-cookie"></i>Cookies</h2>
      <p>
        We use cookies and similar technologies to enhance your browsing experience, remember your preferences, 
        and analyze site traffic. You can control cookies through your browser settings.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-person-check-fill"></i>Your Rights</h2>
      <p>You have the right to:</p>
      <ul>
        <li>Access your personal information</li>
        <li>Correct inaccurate data</li>
        <li>Request deletion of your account</li>
        <li>Opt-out of promotional communications</li>
        <li>Request a copy of your data</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-clock-history"></i>Data Retention</h2>
      <p>
        We retain your personal information for as long as your account is active or as needed to provide services. 
        Order history is kept for 2 years for record-keeping purposes. You can request account deletion at any time.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-envelope-fill"></i>Contact Us</h2>
      <p>
        If you have questions about this Privacy Policy or wish to exercise your rights, contact us at:
      </p>
      <p>
        <strong>Email:</strong> sajjadmahmudsuton@gmail.com<br>
        <strong>Phone:</strong> +880 1968-161494<br>
        <strong>Address:</strong> Green Bites Campus, Dhaka, Bangladesh
      </p>
    </div>
    
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

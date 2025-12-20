<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    GREEN BITES - TERMS & CONDITIONS                       ║
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
  <title>Terms & Conditions - Green Bites</title>
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
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<section class="legal-hero">
  <div class="container">
    <h1><i class="bi bi-file-text me-2"></i>Terms & Conditions</h1>
    <p class="lead mb-0">Please read these terms carefully before using our service</p>
  </div>
</section>

<div class="legal-container">
  <div class="legal-card">
    <div class="update-date">
      <i class="bi bi-calendar3 me-2"></i>Last Updated: December 2025
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-1-circle-fill"></i>Acceptance of Terms</h2>
      <p>
        By accessing and using Green Bites website and services, you accept and agree to be bound by these Terms and Conditions. 
        If you do not agree to these terms, please do not use our services.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-2-circle-fill"></i>Service Description</h2>
      <p>
        Green Bites is an online food ordering platform for campus canteen services. We provide:
      </p>
      <ul>
        <li>Online menu browsing and food ordering</li>
        <li>Order tracking and history management</li>
        <li>Customer support and complaint handling</li>
        <li>User account management</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-3-circle-fill"></i>User Account</h2>
      <p>To use our ordering services, you must:</p>
      <ul>
        <li>Create an account with accurate and complete information</li>
        <li>Be at least 13 years of age</li>
        <li>Maintain the security of your account credentials</li>
        <li>Notify us immediately of any unauthorized account access</li>
        <li>Be responsible for all activities under your account</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-4-circle-fill"></i>Ordering & Payment</h2>
      <ul>
        <li>All prices are listed in Bangladeshi Taka (BDT/TK)</li>
        <li>Prices are subject to change without prior notice</li>
        <li>Orders are confirmed once payment is completed or cash on delivery is selected</li>
        <li>We reserve the right to refuse or cancel orders at our discretion</li>
        <li>Menu availability may vary based on stock and operating hours</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-5-circle-fill"></i>Order Cancellation</h2>
      <p>
        Orders can be cancelled within 5 minutes of placement. After this period, cancellation may not be possible 
        as food preparation may have begun. Refunds for cancelled orders will be processed according to our Refund Policy.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-6-circle-fill"></i>User Conduct</h2>
      <p>Users agree not to:</p>
      <ul>
        <li>Provide false or misleading information</li>
        <li>Use the service for any illegal purpose</li>
        <li>Attempt to gain unauthorized access to our systems</li>
        <li>Interfere with the proper functioning of the website</li>
        <li>Harass or abuse our staff or other users</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-7-circle-fill"></i>Limitation of Liability</h2>
      <p>
        Green Bites shall not be liable for any indirect, incidental, or consequential damages arising from the use 
        of our services. Our liability is limited to the amount paid for the specific order in question.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-8-circle-fill"></i>Changes to Terms</h2>
      <p>
        We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting 
        on this page. Continued use of our services constitutes acceptance of the modified terms.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-envelope-fill"></i>Contact Us</h2>
      <p>
        If you have any questions about these Terms & Conditions, please contact us at:
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

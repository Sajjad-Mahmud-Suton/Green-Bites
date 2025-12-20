<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                         GREEN BITES - FAQ PAGE                            ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

// Load bootstrap (paths, security, db)
require_once __DIR__ . '/../../../config/bootstrap.php';

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
  <title>FAQ - Green Bites</title>
  <link rel="icon" type="image/svg+xml" href="<?php echo IMAGES_URL; ?>/logo-icon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo CSS_URL; ?>/style.css">
  <style>
    .faq-hero {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      padding: 100px 0 60px;
      color: #fff;
      text-align: center;
    }
    .faq-hero h1 {
      font-size: 2.5rem;
      font-weight: 700;
    }
    .faq-container {
      max-width: 800px;
      margin: -40px auto 60px;
      padding: 0 15px;
    }
    .faq-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      padding: 40px;
    }
    .faq-item {
      border-bottom: 1px solid #e5e7eb;
      padding: 20px 0;
    }
    .faq-item:last-child {
      border-bottom: none;
    }
    .faq-question {
      font-weight: 600;
      color: #1f2937;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .faq-question:hover {
      color: #22c55e;
    }
    .faq-question i {
      transition: transform 0.3s ease;
    }
    .faq-question.active i {
      transform: rotate(180deg);
    }
    .faq-answer {
      padding-top: 15px;
      color: #6b7280;
      display: none;
      line-height: 1.7;
    }
    .faq-answer.show {
      display: block;
    }
    .faq-category {
      background: #f0fdf4;
      color: #16a34a;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<section class="faq-hero">
  <div class="container">
    <h1><i class="bi bi-question-circle me-2"></i>Frequently Asked Questions</h1>
    <p class="lead mb-0">Find answers to common questions about Green Bites</p>
  </div>
</section>

<div class="faq-container">
  <div class="faq-card">
    
    <span class="faq-category"><i class="bi bi-cart me-1"></i>Ordering</span>
    
    <div class="faq-item">
      <div class="faq-question">
        How do I place an order?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        Simply browse our menu, select the items you want, add them to your cart, and proceed to checkout. 
        You'll need to create an account or log in to complete your order. Once confirmed, your order will be prepared fresh!
      </div>
    </div>
    
    <div class="faq-item">
      <div class="faq-question">
        What payment methods do you accept?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        Currently, we accept cash on delivery. We're working on adding bKash, Nagad, and card payment options soon. 
        Stay tuned for updates!
      </div>
    </div>
    
    <div class="faq-item">
      <div class="faq-question">
        Can I modify or cancel my order?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        You can request order modification or cancellation within 5 minutes of placing the order. 
        After that, the kitchen may have already started preparing your food. Please contact us immediately if you need assistance.
      </div>
    </div>

    <span class="faq-category mt-4"><i class="bi bi-clock me-1"></i>Timing & Delivery</span>
    
    <div class="faq-item">
      <div class="faq-question">
        What are your operating hours?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        We're open Saturday to Thursday, 8:00 AM to 9:00 PM. We're closed on Fridays and public holidays. 
        During exam periods, we may have extended hours - check our announcements!
      </div>
    </div>
    
    <div class="faq-item">
      <div class="faq-question">
        How long does order preparation take?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        Most orders are ready within 10-20 minutes depending on the items and kitchen load. 
        You'll receive a notification when your order is ready for pickup.
      </div>
    </div>

    <span class="faq-category mt-4"><i class="bi bi-person me-1"></i>Account</span>
    
    <div class="faq-item">
      <div class="faq-question">
        How do I create an account?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        Click on "Login" in the navigation bar, then select "Sign up here". Fill in your details including 
        name, email, and password. Verify your email and you're ready to order!
      </div>
    </div>
    
    <div class="faq-item">
      <div class="faq-question">
        I forgot my password. What should I do?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        Click on "Forgot Password" on the login page. Enter your registered email address and we'll send you 
        a password reset link. Check your spam folder if you don't see it in your inbox.
      </div>
    </div>

    <span class="faq-category mt-4"><i class="bi bi-exclamation-circle me-1"></i>Issues & Support</span>
    
    <div class="faq-item">
      <div class="faq-question">
        How do I file a complaint?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        Go to the Complaints section on our homepage, fill in the form with your issue details, and submit. 
        Our team will review and respond within 24 hours. You can also call us directly for urgent matters.
      </div>
    </div>
    
    <div class="faq-item">
      <div class="faq-question">
        What if I receive the wrong order?
        <i class="bi bi-chevron-down"></i>
      </div>
      <div class="faq-answer">
        We apologize for any inconvenience! Please report the issue immediately through our complaint form or 
        contact us directly. We'll either replace your order or provide a refund as per our refund policy.
      </div>
    </div>
    
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.querySelectorAll('.faq-question').forEach(question => {
  question.addEventListener('click', () => {
    const answer = question.nextElementSibling;
    const isOpen = answer.classList.contains('show');
    
    // Close all answers
    document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('show'));
    document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('active'));
    
    // Open clicked one if it wasn't open
    if (!isOpen) {
      answer.classList.add('show');
      question.classList.add('active');
    }
  });
});
</script>
</body>
</html>

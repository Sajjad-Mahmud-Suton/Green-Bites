<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Refund Policy - Green Bites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
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
    .refund-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    .refund-table th, .refund-table td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
    }
    .refund-table th {
      background: #f0fdf4;
      color: #16a34a;
      font-weight: 600;
    }
    .refund-table tr:hover {
      background: #f9fafb;
    }
    .badge-yes {
      background: #dcfce7;
      color: #16a34a;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .badge-no {
      background: #fee2e2;
      color: #dc2626;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .badge-partial {
      background: #fef3c7;
      color: #d97706;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .steps-list {
      counter-reset: step-counter;
      list-style: none;
      padding-left: 0;
    }
    .steps-list li {
      counter-increment: step-counter;
      position: relative;
      padding-left: 50px;
      margin-bottom: 20px;
    }
    .steps-list li::before {
      content: counter(step-counter);
      position: absolute;
      left: 0;
      top: 0;
      width: 35px;
      height: 35px;
      background: #22c55e;
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<section class="legal-hero">
  <div class="container">
    <h1><i class="bi bi-arrow-return-left me-2"></i>Refund Policy</h1>
    <p class="lead mb-0">Our commitment to customer satisfaction</p>
  </div>
</section>

<div class="legal-container">
  <div class="legal-card">
    <div class="update-date">
      <i class="bi bi-calendar3 me-2"></i>Last Updated: December 2025
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-info-circle-fill"></i>Overview</h2>
      <p>
        At Green Bites, we strive to ensure complete customer satisfaction. If you're not happy with your order, 
        we're here to help. This policy outlines the conditions under which refunds may be issued.
      </p>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-table"></i>Refund Eligibility</h2>
      
      <table class="refund-table">
        <thead>
          <tr>
            <th>Situation</th>
            <th>Refund</th>
            <th>Timeline</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Order cancelled within 5 minutes</td>
            <td><span class="badge-yes">Full Refund</span></td>
            <td>Immediate</td>
          </tr>
          <tr>
            <td>Wrong order delivered</td>
            <td><span class="badge-yes">Full Refund / Replacement</span></td>
            <td>Same day</td>
          </tr>
          <tr>
            <td>Food quality issue</td>
            <td><span class="badge-yes">Full Refund</span></td>
            <td>Within 24 hours</td>
          </tr>
          <tr>
            <td>Missing items in order</td>
            <td><span class="badge-partial">Partial Refund</span></td>
            <td>Same day</td>
          </tr>
          <tr>
            <td>Order cancelled after 5 minutes</td>
            <td><span class="badge-partial">50% Refund</span></td>
            <td>24-48 hours</td>
          </tr>
          <tr>
            <td>Change of mind (after pickup)</td>
            <td><span class="badge-no">No Refund</span></td>
            <td>N/A</td>
          </tr>
          <tr>
            <td>Delay more than 30 minutes</td>
            <td><span class="badge-partial">10% Discount (next order)</span></td>
            <td>Applied automatically</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-list-ol"></i>How to Request a Refund</h2>
      
      <ul class="steps-list">
        <li>
          <strong>Report the Issue</strong><br>
          Go to the Complaints section on our website or contact us directly within 24 hours of your order.
        </li>
        <li>
          <strong>Provide Details</strong><br>
          Include your Order ID, description of the issue, and any photos if applicable (for quality issues).
        </li>
        <li>
          <strong>Review Process</strong><br>
          Our team will review your request within 24 hours and contact you for any additional information.
        </li>
        <li>
          <strong>Resolution</strong><br>
          Once approved, refunds will be processed within 3-5 business days to your original payment method.
        </li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-x-circle-fill"></i>Non-Refundable Situations</h2>
      <p>Refunds will not be issued in the following cases:</p>
      <ul>
        <li>Orders picked up and consumed without reported issues</li>
        <li>Incorrect address or contact information provided by customer</li>
        <li>Failure to pick up order within 30 minutes of ready notification</li>
        <li>Issues not reported within 24 hours of order</li>
        <li>Promotional or discounted items (unless defective)</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-credit-card-fill"></i>Refund Methods</h2>
      <p>Depending on your original payment method, refunds will be processed as follows:</p>
      <ul>
        <li><strong>Cash on Delivery:</strong> Cash refund at counter or store credit</li>
        <li><strong>bKash/Nagad:</strong> Refund to same account within 3-5 days</li>
        <li><strong>Card Payment:</strong> Refund to original card within 5-7 days</li>
        <li><strong>Store Credit:</strong> Immediate credit to your Green Bites account</li>
      </ul>
    </div>

    <div class="legal-section">
      <h2><i class="bi bi-envelope-fill"></i>Contact Us</h2>
      <p>
        For refund requests or questions about this policy, please contact us:
      </p>
      <p>
        <strong>Email:</strong> sajjadmahmudsuton@gmail.com<br>
        <strong>Phone:</strong> +880 1968-161494<br>
        <strong>Hours:</strong> Saturday - Thursday, 8:00 AM - 9:00 PM
      </p>
    </div>
    
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

<!-- Footer Include -->
<footer class="footer-modern">
    <!-- Main Footer -->
    <div class="footer-main">
      <div class="container">
        <div class="row g-4 g-lg-5">
          <!-- Brand Section -->
          <div class="col-lg-4 col-md-6">
            <div class="footer-brand">
              <h3 class="footer-logo d-flex align-items-center">
                <img src="images/logo-icon.svg" alt="Green Bites" class="footer-logo-icon me-2"><span class="text-success">Green</span>&nbsp;<span class="text-white">Bites</span>
              </h3>
              <p class="footer-desc">
                Your campus food companion! We serve fresh, healthy, and affordable meals for students. 
                Order online, skip the queue, and enjoy delicious food.
              </p>
              <div class="footer-social">
                <a href="https://www.facebook.com/sajjadmahmudsuton.suton" target="_blank" class="social-btn facebook">
                  <i class="bi bi-facebook"></i>
                </a>
                <a href="https://instagram.com" target="_blank" class="social-btn instagram">
                  <i class="bi bi-instagram"></i>
                </a>
                <a href="https://twitter.com" target="_blank" class="social-btn twitter">
                  <i class="bi bi-twitter-x"></i>
                </a>
                <a href="https://wa.me/8801968161494" target="_blank" class="social-btn whatsapp">
                  <i class="bi bi-whatsapp"></i>
                </a>
              </div>
            </div>
          </div>
          
          <!-- Quick Links -->
          <div class="col-lg-2 col-md-6 col-6">
            <h5 class="footer-title">Quick Links</h5>
            <ul class="footer-links">
              <li><a href="index.php"><i class="bi bi-chevron-right"></i>Home</a></li>
              <li><a href="index.php#aboutusSection"><i class="bi bi-chevron-right"></i>About Us</a></li>
              <li><a href="index.php#complaintsSection"><i class="bi bi-chevron-right"></i>Complaints</a></li>
              <li><a href="my_orders.php"><i class="bi bi-chevron-right"></i>My Orders</a></li>
              <li><a href="profile.php"><i class="bi bi-chevron-right"></i>My Profile</a></li>
            </ul>
          </div>
          
          <!-- Menu Categories -->
          <div class="col-lg-2 col-md-6 col-6">
            <h5 class="footer-title">Our Menu</h5>
            <ul class="footer-links">
              <?php 
              // Get categories for footer - create fresh connection
              $footerConn = @mysqli_connect('localhost', 'root', '', 'green_bites');
              if ($footerConn) {
                $footerCats = @mysqli_query($footerConn, "SELECT id, name FROM categories ORDER BY name LIMIT 5");
                if ($footerCats && mysqli_num_rows($footerCats) > 0) {
                  while ($fcat = mysqli_fetch_assoc($footerCats)) {
                    echo '<li><a href="category.php?id=' . $fcat['id'] . '"><i class="bi bi-chevron-right"></i>' . htmlspecialchars($fcat['name']) . '</a></li>';
                  }
                } else {
                  // Static fallback if no categories
                  echo '<li><a href="category.php?id=1"><i class="bi bi-chevron-right"></i>Drinks</a></li>';
                  echo '<li><a href="category.php?id=2"><i class="bi bi-chevron-right"></i>Breakfast</a></li>';
                  echo '<li><a href="category.php?id=3"><i class="bi bi-chevron-right"></i>Lunch</a></li>';
                }
                mysqli_close($footerConn);
              } else {
                // Fallback static menu
                echo '<li><a href="category.php?id=1"><i class="bi bi-chevron-right"></i>Drinks</a></li>';
                echo '<li><a href="category.php?id=2"><i class="bi bi-chevron-right"></i>Breakfast</a></li>';
                echo '<li><a href="category.php?id=3"><i class="bi bi-chevron-right"></i>Lunch</a></li>';
                echo '<li><a href="category.php?id=4"><i class="bi bi-chevron-right"></i>Snacks</a></li>';
              }
              ?>
            </ul>
          </div>
          
          <!-- Support & Legal -->
          <div class="col-lg-2 col-md-6 col-6">
            <h5 class="footer-title">Support</h5>
            <ul class="footer-links">
              <li><a href="faq.php"><i class="bi bi-chevron-right"></i>FAQ</a></li>
              <li><a href="terms.php"><i class="bi bi-chevron-right"></i>Terms & Conditions</a></li>
              <li><a href="privacy.php"><i class="bi bi-chevron-right"></i>Privacy Policy</a></li>
              <li><a href="refund.php"><i class="bi bi-chevron-right"></i>Refund Policy</a></li>
            </ul>
          </div>
          
          <!-- Contact Info -->
          <div class="col-lg-2 col-md-6 col-6">
            <h5 class="footer-title">Contact Us</h5>
            <ul class="footer-contact">
              <li>
                <i class="bi bi-geo-alt-fill"></i>
                <span>Green Bites Campus<br>Dhaka, Bangladesh</span>
              </li>
              <li>
                <i class="bi bi-telephone-fill"></i>
                <a href="tel:+8801968161494">+880 1968-161494</a>
              </li>
              <li>
                <i class="bi bi-envelope-fill"></i>
                <a href="mailto:sajjadmahmudsuton@gmail.com">sajjadmahmudsuton@gmail.com</a>
              </li>
              <li>
                <i class="bi bi-clock-fill"></i>
                <span>Sat - Thu: 8AM - 9PM</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer Bottom -->
    <div class="footer-bottom">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">&copy; 2025 <strong>Green Bites</strong>. All rights reserved.</p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <p class="mb-0">
              Made with <i class="bi bi-heart-fill text-danger"></i> by 
              <a href="https://www.facebook.com/sajjadmahmudsuton.suton" target="_blank" class="dev-link">Sajjad</a> & 
              <a href="#" class="dev-link">Esha</a>
            </p>
          </div>
        </div>
      </div>
    </div>
</footer>

<style>
/* Modern Footer Styles */
.footer-modern {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  color: #a0aec0;
  margin-top: 0;
}

.footer-main {
  padding: 60px 0 40px;
}

.footer-logo {
  color: #22c55e;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 15px;
}

.footer-desc {
  font-size: 0.95rem;
  line-height: 1.7;
  margin-bottom: 20px;
  color: #94a3b8;
}

.footer-social {
  display: flex;
  gap: 12px;
}

.social-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  text-decoration: none;
}

.social-btn.facebook { background: #1877f2; }
.social-btn.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
.social-btn.twitter { background: #000; }
.social-btn.whatsapp { background: #25d366; }

.social-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  color: #fff;
}

.footer-title {
  color: #fff;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 20px;
  position: relative;
  padding-bottom: 10px;
}

.footer-title::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 40px;
  height: 2px;
  background: #22c55e;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 12px;
}

.footer-links a {
  color: #94a3b8;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
}

.footer-links a i {
  font-size: 0.7rem;
  margin-right: 8px;
  color: #22c55e;
  transition: transform 0.3s ease;
}

.footer-links a:hover {
  color: #22c55e;
  padding-left: 5px;
}

.footer-links a:hover i {
  transform: translateX(3px);
}

.footer-contact {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-contact li {
  display: flex;
  align-items: flex-start;
  margin-bottom: 15px;
  font-size: 0.9rem;
}

.footer-contact li i {
  color: #22c55e;
  margin-right: 12px;
  margin-top: 3px;
  font-size: 1rem;
}

.footer-contact a {
  color: #94a3b8;
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-contact a:hover {
  color: #22c55e;
}

.footer-bottom {
  background: rgba(0,0,0,0.2);
  padding: 20px 0;
  border-top: 1px solid rgba(255,255,255,0.05);
}

.footer-bottom p {
  font-size: 0.9rem;
  color: #64748b;
}

.dev-link {
  color: #22c55e;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.dev-link:hover {
  color: #4ade80;
}

@media (max-width: 768px) {
  .footer-main {
    padding: 40px 0 30px;
  }
  
  .footer-logo {
    font-size: 1.5rem;
  }
  
  .footer-title {
    margin-top: 10px;
    margin-bottom: 15px;
  }
  
  .footer-social {
    justify-content: flex-start;
    margin-bottom: 20px;
  }
}
</style>

<!-- Cart Toast Container -->
<div id="cartToastContainer" class="cart-toast-container"></div>

<!-- Cart Overlay -->
<div id="cartOverlay" class="cart-overlay"></div>

<!-- Cart Slide-out Panel -->
<div id="cartPanel" class="cart-panel">
  <div class="cart-panel-header">
    <h5><i class="bi bi-cart3 me-2"></i>Your Cart</h5>
    <button class="cart-panel-close" id="closeCartPanel">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  
  <div id="emptyCartMessage" class="empty-cart-message">
    <i class="bi bi-cart-x"></i>
    <p>Your cart is empty</p>
    <a href="lunch.php" class="btn btn-success btn-sm">Browse Menu</a>
  </div>
  
  <div id="cartContent" class="cart-panel-content" style="display: none;">
    <div id="cartItemsContainer" class="cart-items-container"></div>
    
    <div class="cart-summary">
      <div class="cart-summary-row">
        <span>Subtotal</span>
        <span id="cartSubtotal">৳0</span>
      </div>
      <div class="cart-summary-row cart-total">
        <span>Total</span>
        <span id="cartTotal">৳0</span>
      </div>
    </div>
  </div>
  
  <div class="cart-panel-footer">
    <button class="btn btn-outline-secondary w-100 mb-2" id="continueShoppingBtn">
      <i class="bi bi-arrow-left me-2"></i>Continue Shopping
    </button>
    <button class="btn btn-success w-100" id="checkoutBtn" disabled>
      <i class="bi bi-bag-check me-2"></i>Proceed to Checkout
    </button>
    <button class="btn btn-link text-danger btn-sm mt-2 w-100" id="clearCartBtn">
      <i class="bi bi-trash me-1"></i>Clear Cart
    </button>
  </div>
</div>

<!-- Order Item Modal (Popup when clicking Order button) -->
<div class="modal fade" id="orderItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content order-item-modal-content">
      <div class="modal-header order-item-modal-header">
        <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i>Add to Cart</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="order-item-details">
          <div class="order-item-image"></div>
          <div class="order-item-info">
            <h4 class="order-item-title">Item Name</h4>
            <p class="order-item-price">৳0</p>
          </div>
        </div>
        
        <div class="order-quantity-section">
          <label class="form-label fw-semibold">Quantity</label>
          <div class="order-quantity-controls">
            <button type="button" class="qty-btn order-qty-minus">
              <i class="bi bi-dash-lg"></i>
            </button>
            <input type="number" class="form-control order-quantity-input" id="orderQuantity" value="1" min="1" max="99">
            <button type="button" class="qty-btn order-qty-plus">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>
        </div>
        
        <div class="order-total-section">
          <span>Total:</span>
          <span class="order-item-total">৳0</span>
        </div>
      </div>
      <div class="modal-footer order-item-modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="button" class="btn btn-success" id="addToCartBtn">
          <i class="bi bi-cart-plus me-2"></i>Add to Cart
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal-content">
      <div class="modal-body text-center py-5">
        <div class="login-modal-icon">
          <i class="bi bi-person-lock"></i>
        </div>
        <h4 class="mt-3 mb-2">Please Login to Order</h4>
        <p class="text-muted mb-4">You need to be logged in to add items to your cart and place orders.</p>
        <div class="d-flex gap-3 justify-content-center">
          <a href="login.php" class="btn btn-success px-4">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
          </a>
          <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
            Close
          </button>
        </div>
        <p class="mt-3 mb-0 small text-muted">
          Don't have an account? <a href="signup.php" class="text-success">Sign up here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content checkout-modal-content">
      <div class="modal-header checkout-modal-header">
        <h5 class="modal-title"><i class="bi bi-bag-check me-2"></i>Checkout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-7">
            <h6 class="checkout-section-title">Order Summary</h6>
            <div class="checkout-items"></div>
            <div class="checkout-total">
              <span>Total Amount</span>
              <span class="checkout-total-amount">৳0</span>
            </div>
          </div>
          <div class="col-md-5">
            <h6 class="checkout-section-title">Additional Info (Optional)</h6>
            <div class="mb-3">
              <label class="form-label">Student ID</label>
              <input type="text" class="form-control" id="checkoutStudentId" placeholder="Enter your student ID">
            </div>
            <div class="mb-3">
              <label class="form-label">Special Instructions</label>
              <textarea class="form-control" id="checkoutInstructions" rows="3" placeholder="Any special requests for your order?"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer checkout-modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-2"></i>Cancel
        </button>
        <button type="button" class="btn btn-success" id="confirmOrderBtn">
          <i class="bi bi-check-circle me-2"></i>Confirm Order
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Order Success Modal -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content success-modal-content">
      <div class="modal-body text-center py-5">
        <div class="success-modal-icon">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <h4 class="mt-3 mb-2 text-success">Order Placed Successfully!</h4>
        <p class="text-muted mb-2">Thank you for your order.</p>
        <p class="mb-4">Order ID: <strong class="order-id-display"></strong></p>
        <div class="d-flex gap-3 justify-content-center">
          <a href="index.php" class="btn btn-success px-4">
            <i class="bi bi-house me-2"></i>Back to Home
          </a>
          <button type="button" class="btn btn-outline-success px-4" data-bs-dismiss="modal">
            Continue Shopping
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Smooth scroll to menu section from carousel
function scrollToMenu(event) {
  event.preventDefault();
  const menuSection = document.getElementById('menuSection');
  if (menuSection) {
    const navHeight = document.querySelector('.navbar')?.offsetHeight || 80;
    const targetPosition = menuSection.offsetTop - navHeight;
    window.scrollTo({
      top: targetPosition,
      behavior: 'smooth'
    });
  }
}
</script>
<script src="js/cart.js"></script>

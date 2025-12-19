<!-- Footer Include -->
<footer class="footer-multi bg-dark text-light py-4 mt-4">
    <div class="container">
      <div class="row g-4">
        <!-- ABOUT -->
        <div class="col-md-4">
          <h5 class="mb-2 text-white">ABOUT</h5>
          <p class="small mb-3">
            Green Bites is built by Sajjad Mahmud Suton & Esha Akter.<br>
            Our aim: healthy, affordable food for students, with quick ordering and complaint support.
          </p>
          <div>
            <a href="https://www.facebook.com/sajjadmahmudsuton.suton" class="me-3 text-light"><i class="bi bi-facebook me-1"></i>Facebook</a>
            <a href="https://instagram.com" class="text-light"><i class="bi bi-instagram me-1"></i>Instagram</a>
          </div>
        </div>
        <!-- QUICK LINKS -->
        <div class="col-md-4">
          <h5 class="mb-2 text-white">QUICK LINKS</h5>
          <ul class="list-unstyled small">
            <li class="mb-1"><a href="index.php" class="footer-link">Home</a></li>
            <li class="mb-1"><a href="drinks.php" class="footer-link">Drinks</a></li>
            <li class="mb-1"><a href="breakfast.php" class="footer-link">Breakfast</a></li>
            <li class="mb-1"><a href="lunch.php" class="footer-link">Lunch</a></li>
            <li class="mb-1"><a href="snacks.php" class="footer-link">Snacks</a></li>
            <li class="mb-1"><a href="index.php#complaintsSection" class="footer-link">Complaint</a></li>
          </ul>
        </div>
        <!-- CONTACT INFO -->
        <div class="col-md-4">
          <h5 class="mb-2 text-white">CONTACT</h5>
          <ul class="list-unstyled small">
            <li class="mb-1"><i class="bi bi-telephone-fill me-2"></i><a href="tel:+8801968-161494">+8801968-161494</a> </li>
            <li class="mb-1"><i class="bi bi-envelope-fill me-2"></i><a href="mailto:sajjadmahmudsuton@gmail.com">sajjadmahmudsuton@gmail.com</a></li>
            <li class="mb-1"><i class="bi bi-geo-alt me-2"></i>Green Bites Campus, Bangladesh</li>
          </ul>
          <span class="small text-muted">For feedback: email or call anytime</span>
        </div>
      </div>
      <hr class="bg-light my-3">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small">
        <span>&copy; 2025 Green Bites. All rights reserved.</span>
        <span>Developed by <span class="text-warning fw-bold">Sajjad & Esha</span></span>
      </div>
    </div>
</footer>

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
<script src="js/cart.js"></script>

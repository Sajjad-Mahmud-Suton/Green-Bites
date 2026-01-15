/**
 * Green Bites Order & Cart System
 * Modern food ordering with login check, order popup, and cart panel
 */

// Cart data stored in localStorage
const CART_KEY = 'greenBitesCart';

// Cart state
let cart = [];
let isLoggedIn = false;
let currentUser = null;

// Initialize cart from localStorage
function initCart() {
  const stored = localStorage.getItem(CART_KEY);
  if (stored) {
    try {
      cart = JSON.parse(stored);
    } catch (e) {
      cart = [];
    }
  }
  updateCartBadge();
}

// Save cart to localStorage
function saveCart() {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}

// Get cart total items count
function getCartItemCount() {
  return cart.reduce((sum, item) => sum + item.quantity, 0);
}

// Get cart total price
function getCartTotal() {
  return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
}

// Update cart badge in user dropdown
function updateCartBadge() {
  const badge = document.getElementById('cartBadge');
  const count = getCartItemCount();
  if (badge) {
    badge.textContent = count;
    badge.style.display = count > 0 ? 'inline-block' : 'none';
  }
}

// Add item to cart
function addToCart(id, title, price, image = '') {
  const itemId = String(id); // Ensure ID is string for consistent comparison
  const existingItem = cart.find(item => String(item.id) === itemId);
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.push({
      id: itemId,
      title: title,
      price: parseFloat(price),
      image: image,
      quantity: 1
    });
  }
  saveCart();
}

// Remove item from cart
function removeFromCart(id) {
  const itemId = String(id); // Ensure ID is string
  cart = cart.filter(item => String(item.id) !== itemId);
  saveCart();
  renderCartPanel();
}

// Update item quantity
async function updateQuantity(id, delta) {
  const itemId = String(id); // Ensure ID is string for consistent comparison
  const item = cart.find(i => String(i.id) === itemId);
  
  if (!item) {
    console.error('Item not found in cart:', itemId);
    return;
  }
  
  const newQuantity = item.quantity + delta;
  
  // If increasing quantity, check stock
  if (delta > 0) {
    try {
      const stockInfo = await checkItemStock(itemId);
      if (stockInfo && newQuantity > stockInfo.quantity) {
        showStockLimitToast(item.title, stockInfo.quantity);
        return;
      }
    } catch (error) {
      console.error('Stock check failed:', error);
      // Continue anyway if stock check fails
    }
  }
  
  if (newQuantity <= 0) {
    removeFromCart(itemId);
  } else {
    item.quantity = newQuantity;
    saveCart();
    renderCartPanel();
  }
}

// Show stock limit toast
function showStockLimitToast(itemTitle, maxStock) {
  const toastContainer = document.getElementById('cartToastContainer');
  if (!toastContainer) {
    alert(`Maximum available stock for "${itemTitle}" is ${maxStock}`);
    return;
  }

  const toast = document.createElement('div');
  toast.className = 'cart-toast cart-toast-warning';
  toast.innerHTML = `
    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
    <span>Only <strong>${maxStock}</strong> of <strong>${itemTitle}</strong> available!</span>
  `;
  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('fade-out');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// Clear entire cart
function clearCart() {
  cart = [];
  saveCart();
  renderCartPanel();
}

/* ═══════════════════════════════════════════════════════════════════════════
   STOCK VALIDATION FUNCTIONS
   Validates cart items against available stock before checkout
   ═══════════════════════════════════════════════════════════════════════════ */

// Check stock for a single item
async function checkItemStock(itemId) {
  try {
    const response = await fetch(`api/check_stock.php?id=${itemId}`);
    const data = await response.json();
    return data.success ? data : null;
  } catch (e) {
    console.error('Stock check error:', e);
    return null;
  }
}

// Validate entire cart against stock
async function validateCartStock() {
  if (cart.length === 0) return true;

  try {
    const response = await fetch('api/validate_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items: cart })
    });

    const result = await response.json();

    if (!result.valid) {
      // Show stock error modal
      showStockErrorModal(result.items);
      return false;
    }

    return true;
  } catch (e) {
    console.error('Cart validation error:', e);
    alert('Could not verify stock availability. Please try again.');
    return false;
  }
}

// Show stock error modal
function showStockErrorModal(items) {
  const invalidItems = items.filter(item => !item.valid);
  
  let errorHtml = `
    <div class="stock-error-modal">
      <div class="alert alert-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Some items have insufficient stock!</strong>
      </div>
      <div class="stock-error-list">
  `;

  invalidItems.forEach(item => {
    errorHtml += `
      <div class="stock-error-item d-flex justify-content-between align-items-center p-2 border-bottom">
        <div>
          <strong>${item.title}</strong>
          <br><small class="text-muted">Requested: ${item.requested}</small>
        </div>
        <div class="text-end">
          <span class="badge ${item.available > 0 ? 'bg-warning text-dark' : 'bg-danger'}">
            ${item.message}
          </span>
        </div>
      </div>
    `;
  });

  errorHtml += `
      </div>
      <div class="mt-3 text-center">
        <p class="text-muted small">Please adjust quantities in your cart or remove unavailable items.</p>
      </div>
    </div>
  `;

  // Create and show modal
  let stockModal = document.getElementById('stockErrorModal');
  if (!stockModal) {
    stockModal = document.createElement('div');
    stockModal.id = 'stockErrorModal';
    stockModal.className = 'modal fade';
    stockModal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Stock Unavailable</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="stockErrorContent"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="openCartPanel(); bootstrap.Modal.getInstance(document.getElementById('stockErrorModal')).hide();">
              <i class="bi bi-cart me-2"></i>Edit Cart
            </button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(stockModal);
  }

  document.getElementById('stockErrorContent').innerHTML = errorHtml;
  const bsModal = new bootstrap.Modal(stockModal);
  bsModal.show();

  // Auto-adjust cart quantities for items with partial stock
  invalidItems.forEach(item => {
    if (item.available > 0) {
      const cartItem = cart.find(c => c.id == item.id);
      if (cartItem) {
        cartItem.maxStock = item.available;
      }
    }
  });
}

// Get max available stock for an item (used when adding to cart)
async function getMaxStock(itemId) {
  const stockInfo = await checkItemStock(itemId);
  return stockInfo ? stockInfo.quantity : 999;
}

// Check if user is logged in
async function checkLoginStatus() {
  try {
    const response = await fetch('auth/check_session.php', {
      credentials: 'same-origin'
    });
    const data = await response.json();
    isLoggedIn = data.logged_in || false;
    currentUser = isLoggedIn ? data : null;
    return isLoggedIn;
  } catch (e) {
    isLoggedIn = false;
    currentUser = null;
    return false;
  }
}

// Show login required modal
function showLoginModal() {
  const modal = document.getElementById('loginRequiredModal');
  if (modal) {
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  }
}

// Show order popup for single item
function showOrderPopup(itemId, itemTitle, itemPrice, itemImage, itemOriginalPrice = null, itemDiscount = 0) {
  const modal = document.getElementById('orderItemModal');
  if (!modal) return;

  const price = parseFloat(itemPrice);
  const originalPrice = parseFloat(itemOriginalPrice) || price;
  const discount = parseInt(itemDiscount) || 0;
  const hasDiscount = discount > 0;

  // Fill modal with item details
  const modalTitle = modal.querySelector('.order-item-title');
  const modalPrice = modal.querySelector('.order-item-price');
  const modalImage = modal.querySelector('.order-item-image');
  const quantityInput = modal.querySelector('#orderQuantity');
  const totalDisplay = modal.querySelector('.order-item-total');

  if (modalTitle) modalTitle.textContent = itemTitle;
  if (modalPrice) {
    if (hasDiscount) {
      modalPrice.innerHTML = `
        <span class="discounted-price">৳${price.toFixed(0)}</span>
        <span class="original-price text-muted text-decoration-line-through ms-2">৳${originalPrice.toFixed(0)}</span>
        <span class="badge bg-danger ms-2">${discount}% OFF</span>
      `;
    } else {
      modalPrice.innerHTML = `<span>৳${price.toFixed(0)}</span>`;
    }
  }
  if (modalImage) {
    if (itemImage) {
      modalImage.innerHTML = `<img src="${itemImage}" alt="${itemTitle}">`;
    } else {
      modalImage.innerHTML = '<i class="bi bi-image text-muted" style="font-size: 3rem;"></i>';
    }
  }
  if (quantityInput) quantityInput.value = 1;
  if (totalDisplay) totalDisplay.textContent = `৳${price.toFixed(0)}`;

  // Store item data in modal
  modal.dataset.itemId = itemId;
  modal.dataset.itemTitle = itemTitle;
  modal.dataset.itemPrice = price;
  modal.dataset.itemOriginalPrice = originalPrice;
  modal.dataset.itemDiscount = discount;
  modal.dataset.itemImage = itemImage || '';

  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}

// Update order popup total when quantity changes
function updateOrderTotal() {
  const modal = document.getElementById('orderItemModal');
  if (!modal) return;

  const price = parseFloat(modal.dataset.itemPrice) || 0;
  const quantity = parseInt(document.getElementById('orderQuantity')?.value) || 1;
  const totalDisplay = modal.querySelector('.order-item-total');
  
  if (totalDisplay) {
    totalDisplay.textContent = `৳${(price * quantity).toFixed(0)}`;
  }
}

// Add to cart from order popup
async function addToCartFromPopup() {
  const modal = document.getElementById('orderItemModal');
  if (!modal) return;

  const itemId = modal.dataset.itemId;
  const itemTitle = modal.dataset.itemTitle;
  const itemPrice = parseFloat(modal.dataset.itemPrice);
  const itemOriginalPrice = parseFloat(modal.dataset.itemOriginalPrice) || itemPrice;
  const itemDiscount = parseInt(modal.dataset.itemDiscount) || 0;
  const itemImage = modal.dataset.itemImage;
  const quantity = parseInt(document.getElementById('orderQuantity')?.value) || 1;

  // Check stock before adding
  const stockInfo = await checkItemStock(itemId);
  if (!stockInfo) {
    alert('Could not verify stock. Please try again.');
    return;
  }

  if (!stockInfo.in_stock) {
    alert(`Sorry, "${itemTitle}" is out of stock!`);
    return;
  }

  // Calculate total quantity (existing in cart + new)
  const existingItem = cart.find(item => item.id === itemId);
  const existingQty = existingItem ? existingItem.quantity : 0;
  const totalQty = existingQty + quantity;

  if (totalQty > stockInfo.quantity) {
    const availableToAdd = stockInfo.quantity - existingQty;
    if (availableToAdd <= 0) {
      alert(`Sorry, you already have the maximum available quantity (${stockInfo.quantity}) of "${itemTitle}" in your cart.`);
    } else {
      alert(`Only ${availableToAdd} more of "${itemTitle}" can be added. Available stock: ${stockInfo.quantity}`);
    }
    return;
  }

  // Add item to cart with specified quantity
  if (existingItem) {
    existingItem.quantity += quantity;
    existingItem.maxStock = stockInfo.quantity;
  } else {
    cart.push({
      id: itemId,
      title: itemTitle,
      price: itemPrice,
      originalPrice: itemOriginalPrice,
      discount: itemDiscount,
      image: itemImage,
      quantity: quantity,
      maxStock: stockInfo.quantity
    });
  }
  saveCart();

  // Close modal
  const bsModal = bootstrap.Modal.getInstance(modal);
  if (bsModal) bsModal.hide();

  // Show success toast
  showAddedToCartToast(itemTitle, quantity);
}

// Show "Added to cart" toast
function showAddedToCartToast(itemTitle, quantity = 1) {
  const toastContainer = document.getElementById('cartToastContainer');
  if (!toastContainer) return;

  const toast = document.createElement('div');
  toast.className = 'cart-toast';
  toast.innerHTML = `
    <i class="bi bi-check-circle-fill text-success me-2"></i>
    <span><strong>${quantity}x ${itemTitle}</strong> added to cart!</span>
  `;
  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('fade-out');
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

// Show success modal after order
function showOrderSuccessModal(orderId, paymentMethod) {
  const modal = document.getElementById('orderSuccessModal');
  if (modal) {
    const orderIdSpan = modal.querySelector('.order-id-display');
    if (orderIdSpan) {
      orderIdSpan.textContent = orderId || '';
    }
    const paymentSpan = modal.querySelector('.payment-method-display');
    if (paymentSpan) {
      paymentSpan.textContent = paymentMethod || 'Pay at Counter';
    }
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  }
}

// Render cart panel content
function renderCartPanel() {
  const cartItemsContainer = document.getElementById('cartItemsContainer');
  const cartSubtotal = document.getElementById('cartSubtotal');
  const cartTotal = document.getElementById('cartTotal');
  const emptyCartMessage = document.getElementById('emptyCartMessage');
  const cartContent = document.getElementById('cartContent');
  const checkoutBtn = document.getElementById('checkoutBtn');

  if (!cartItemsContainer) return;

  if (cart.length === 0) {
    if (emptyCartMessage) emptyCartMessage.style.display = 'flex';
    if (cartContent) cartContent.style.display = 'none';
    if (checkoutBtn) checkoutBtn.disabled = true;
    return;
  }

  if (emptyCartMessage) emptyCartMessage.style.display = 'none';
  if (cartContent) cartContent.style.display = 'block';
  if (checkoutBtn) checkoutBtn.disabled = false;

  cartItemsContainer.innerHTML = cart.map(item => {
    const hasDiscount = item.discount && item.discount > 0;
    const priceDisplay = hasDiscount 
      ? `<span class="cart-item-price">৳${item.price}</span>
         <span class="cart-item-original-price text-muted text-decoration-line-through">৳${item.originalPrice}</span>
         <span class="badge bg-danger cart-discount-badge">${item.discount}% OFF</span>`
      : `<span class="cart-item-price">৳${item.price}</span>`;
    
    return `
    <div class="cart-item" data-id="${item.id}">
      <div class="cart-item-image">
        ${item.image ? `<img src="${item.image}" alt="${item.title}">` : '<i class="bi bi-image text-muted"></i>'}
      </div>
      <div class="cart-item-details">
        <h6 class="cart-item-title">${item.title}</h6>
        ${priceDisplay}
      </div>
      <div class="cart-item-quantity">
        <button class="qty-btn qty-minus" onclick="updateQuantity('${item.id}', -1)">
          <i class="bi bi-dash"></i>
        </button>
        <span class="qty-value">${item.quantity}</span>
        <button class="qty-btn qty-plus" onclick="updateQuantity('${item.id}', 1)">
          <i class="bi bi-plus"></i>
        </button>
      </div>
      <div class="cart-item-total">
        ৳${(item.price * item.quantity).toFixed(0)}
      </div>
      <button class="cart-item-remove" onclick="removeFromCart('${item.id}')">
        <i class="bi bi-trash"></i>
      </button>
    </div>
  `;
  }).join('');

  const subtotal = getCartTotal();
  if (cartSubtotal) cartSubtotal.textContent = `৳${subtotal.toFixed(0)}`;
  if (cartTotal) cartTotal.textContent = `৳${subtotal.toFixed(0)}`;
}

// Open cart panel
function openCartPanel() {
  const panel = document.getElementById('cartPanel');
  const overlay = document.getElementById('cartOverlay');
  if (panel) {
    renderCartPanel();
    panel.classList.add('open');
  }
  if (overlay) overlay.classList.add('show');
  document.body.style.overflow = 'hidden';
}

// Close cart panel
function closeCartPanel() {
  const panel = document.getElementById('cartPanel');
  const overlay = document.getElementById('cartOverlay');
  if (panel) panel.classList.remove('open');
  if (overlay) overlay.classList.remove('show');
  document.body.style.overflow = '';
}

// Open checkout modal
async function openCheckoutModal() {
  closeCartPanel();
  
  const modal = document.getElementById('checkoutModal');
  if (!modal) return;

  // Validate cart stock before showing checkout
  const stockValid = await validateCartStock();
  if (!stockValid) {
    return; // Don't open checkout if stock validation fails
  }

  // Render order summary
  const summaryContainer = modal.querySelector('.checkout-items');
  const totalDisplay = modal.querySelector('.checkout-total-amount');
  
  if (summaryContainer) {
    summaryContainer.innerHTML = cart.map(item => `
      <div class="checkout-item">
        <span class="checkout-item-name">${item.title} × ${item.quantity}</span>
        <span class="checkout-item-price">৳${(item.price * item.quantity).toFixed(0)}</span>
      </div>
    `).join('');
  }

  if (totalDisplay) {
    totalDisplay.textContent = `৳${getCartTotal().toFixed(0)}`;
  }

  // Reset form
  const studentIdInput = modal.querySelector('#checkoutStudentId');
  const instructionsInput = modal.querySelector('#checkoutInstructions');
  if (studentIdInput) studentIdInput.value = '';
  if (instructionsInput) instructionsInput.value = '';

  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}

// Submit order
async function submitOrder() {
  const modal = document.getElementById('checkoutModal');
  const studentId = document.getElementById('checkoutStudentId')?.value.trim() || '';
  const instructions = document.getElementById('checkoutInstructions')?.value.trim() || '';
  const paymentMethod = typeof getSelectedPaymentMethod === 'function' ? getSelectedPaymentMethod() : 'free';
  const confirmBtn = document.getElementById('confirmOrderBtn');
  
  if (cart.length === 0) {
    alert('Your cart is empty!');
    return;
  }

  // Disable button and show loading
  if (confirmBtn) {
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying stock...';
  }

  // Final stock validation before placing order
  const stockValid = await validateCartStock();
  if (!stockValid) {
    if (confirmBtn) {
      confirmBtn.disabled = false;
      confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirm Order';
    }
    // Close checkout modal to let user edit cart
    const bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
    return;
  }

  if (confirmBtn) {
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Placing Order...';
  }

  try {
    const response = await fetch('api/place_order.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        items: cart,
        total_price: getCartTotal(),
        student_id: studentId,
        special_instructions: instructions,
        payment_method: paymentMethod
      })
    });

    const result = await response.json();

    if (result.success) {
      // Close checkout modal
      const bsModal = bootstrap.Modal.getInstance(modal);
      if (bsModal) bsModal.hide();

      // Clear cart
      clearCart();

      // Show success modal
      showOrderSuccessModal(result.order_id, paymentMethod);
    } else {
      alert(result.message || 'Failed to place order. Please try again.');
    }
  } catch (e) {
    console.error('Order error:', e);
    alert('Network error. Please try again.');
  } finally {
    if (confirmBtn) {
      confirmBtn.disabled = false;
      confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirm Order';
    }
  }
}

// Handle order button click - shows popup
async function handleOrderClick(event) {
  const button = event.target.closest('.order-btn');
  if (!button) return;

  event.preventDefault();
  event.stopPropagation();

  const itemId = button.dataset.itemId;
  const itemTitle = button.dataset.itemTitle;
  const itemPrice = button.dataset.itemPrice;
  const itemImage = button.dataset.itemImage || '';
  const itemOriginalPrice = button.dataset.itemOriginalPrice || itemPrice;
  const itemDiscount = button.dataset.itemDiscount || 0;

  // Check login status first
  const loggedIn = await checkLoginStatus();

  if (!loggedIn) {
    showLoginModal();
    return;
  }

  // Show order popup with discount info
  showOrderPopup(itemId, itemTitle, itemPrice, itemImage, itemOriginalPrice, itemDiscount);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
  initCart();

  // Attach click handler to all order buttons
  document.addEventListener('click', function(e) {
    if (e.target.closest('.order-btn')) {
      handleOrderClick(e);
    }
  });

  // Cart menu button click (in user dropdown)
  document.addEventListener('click', function(e) {
    if (e.target.closest('#cartMenuBtn')) {
      e.preventDefault();
      openCartPanel();
    }
  });

  // Close cart panel
  const closeCartBtn = document.getElementById('closeCartPanel');
  if (closeCartBtn) {
    closeCartBtn.addEventListener('click', closeCartPanel);
  }

  // Cart overlay click to close
  const overlay = document.getElementById('cartOverlay');
  if (overlay) {
    overlay.addEventListener('click', closeCartPanel);
  }

  // Continue shopping button
  const continueBtn = document.getElementById('continueShoppingBtn');
  if (continueBtn) {
    continueBtn.addEventListener('click', closeCartPanel);
  }

  // Checkout button
  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', openCheckoutModal);
  }

  // Confirm order button
  const confirmOrderBtn = document.getElementById('confirmOrderBtn');
  if (confirmOrderBtn) {
    confirmOrderBtn.addEventListener('click', submitOrder);
  }

  // Clear cart button
  const clearCartBtn = document.getElementById('clearCartBtn');
  if (clearCartBtn) {
    clearCartBtn.addEventListener('click', function() {
      if (confirm('Are you sure you want to clear your cart?')) {
        clearCart();
      }
    });
  }

  // Order quantity change handlers
  document.addEventListener('click', async function(e) {
    const modal = document.getElementById('orderItemModal');
    if (!modal) return;

    if (e.target.closest('.order-qty-minus')) {
      const input = document.getElementById('orderQuantity');
      if (input && parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        updateOrderTotal();
      }
    }
    if (e.target.closest('.order-qty-plus')) {
      const input = document.getElementById('orderQuantity');
      if (input) {
        const itemId = modal.dataset.itemId;
        const newQty = parseInt(input.value) + 1;
        
        // Check stock before increasing
        const stockInfo = await checkItemStock(itemId);
        if (stockInfo) {
          // Consider existing cart quantity
          const existingItem = cart.find(item => item.id === itemId);
          const cartQty = existingItem ? existingItem.quantity : 0;
          const totalQty = cartQty + newQty;
          
          if (totalQty > stockInfo.quantity) {
            const maxCanAdd = stockInfo.quantity - cartQty;
            showStockLimitToast(modal.dataset.itemTitle, maxCanAdd > 0 ? `You can add ${maxCanAdd} more` : 'Maximum in cart');
            return;
          }
        }
        
        input.value = newQty;
        updateOrderTotal();
      }
    }
  });

  // Add to cart button in order popup
  document.addEventListener('click', function(e) {
    if (e.target.closest('#addToCartBtn')) {
      addToCartFromPopup();
    }
  });

  // Quantity input change
  const quantityInput = document.getElementById('orderQuantity');
  if (quantityInput) {
    quantityInput.addEventListener('change', updateOrderTotal);
  }
  
  // Start stock auto-refresh for menu pages
  startStockAutoRefresh();
});

// Stock Auto-Refresh for Menu Pages
let stockRefreshInterval = null;

function startStockAutoRefresh() {
  // Only run on pages with menu cards
  const menuCards = document.querySelectorAll('.menu-card');
  if (menuCards.length === 0) return;
  
  // Get category ID from URL if available
  const urlParams = new URLSearchParams(window.location.search);
  const categoryId = urlParams.get('id');
  
  // Initial check after 20 seconds, then every 30 seconds
  stockRefreshInterval = setInterval(() => {
    refreshMenuStockStatus(categoryId);
  }, 30000);
}

async function refreshMenuStockStatus(categoryId = null) {
  try {
    let url = 'api/get_menu_items.php';
    if (categoryId) {
      url += '?category_id=' + categoryId;
    }
    
    const response = await fetch(url);
    const result = await response.json();
    
    if (result.success && result.items) {
      updateMenuCardsStock(result.items);
    }
  } catch (error) {
    console.error('Error refreshing stock:', error);
  }
}

function updateMenuCardsStock(items) {
  items.forEach(item => {
    // Find the card for this item (using data attribute or order button)
    const orderBtn = document.querySelector(`[data-item-id="${item.id}"]`);
    if (!orderBtn) return;
    
    const card = orderBtn.closest('.menu-card');
    if (!card) return;
    
    const wasStockout = card.classList.contains('stockout-card');
    const isNowStockout = item.is_stockout;
    
    // Update card classes
    if (isNowStockout && !wasStockout) {
      // Item became stockout
      card.classList.add('stockout-card');
      const img = card.querySelector('.card-img-top');
      if (img) img.classList.add('stockout-image');
      
      // Update badge
      updateStockBadge(card, 'stockout', 0);
      
      // Disable order button
      orderBtn.disabled = true;
      orderBtn.classList.remove('btn-success');
      orderBtn.classList.add('btn-secondary');
      orderBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Out of Stock';
      
      // Show notification
      showStockUpdateToast(item.title, 'is now out of stock');
      
    } else if (!isNowStockout && wasStockout) {
      // Item back in stock
      card.classList.remove('stockout-card');
      const img = card.querySelector('.card-img-top');
      if (img) img.classList.remove('stockout-image');
      
      // Update badge
      if (item.is_low_stock) {
        updateStockBadge(card, 'low', item.quantity);
      } else {
        removeStockBadge(card);
      }
      
      // Enable order button
      orderBtn.disabled = false;
      orderBtn.classList.remove('btn-secondary');
      orderBtn.classList.add('btn-success');
      orderBtn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Order Now';
      
      // Show notification
      showStockUpdateToast(item.title, 'is back in stock!');
      
    } else if (item.is_low_stock) {
      // Update low stock badge
      updateStockBadge(card, 'low', item.quantity);
    }
  });
}

function updateStockBadge(card, type, quantity) {
  let badge = card.querySelector('.stock-badge');
  const imgContainer = card.querySelector('.position-relative');
  
  if (!imgContainer) return;
  
  if (!badge) {
    badge = document.createElement('div');
    badge.className = 'stock-badge';
    imgContainer.appendChild(badge);
  }
  
  if (type === 'stockout') {
    badge.className = 'stock-badge stockout-badge';
    badge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Stockout';
  } else if (type === 'low') {
    badge.className = 'stock-badge low-stock-badge';
    badge.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>Only ${quantity} left!`;
  }
}

function removeStockBadge(card) {
  const badge = card.querySelector('.stock-badge');
  if (badge) badge.remove();
}

function showStockUpdateToast(itemName, message) {
  // Create toast element if it doesn't exist
  let toastContainer = document.getElementById('stockUpdateToastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'stockUpdateToastContainer';
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '1055';
    document.body.appendChild(toastContainer);
  }
  
  const toastId = 'toast-' + Date.now();
  const toastHtml = `
    <div id="${toastId}" class="toast align-items-center text-white bg-info border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          <i class="bi bi-info-circle me-2"></i>
          <strong>${itemName}</strong> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  `;
  
  toastContainer.insertAdjacentHTML('beforeend', toastHtml);
  
  const toastEl = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
  toast.show();
  
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

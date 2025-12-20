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
  const existingItem = cart.find(item => item.id === id);
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.push({
      id: id,
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
  cart = cart.filter(item => item.id !== id);
  saveCart();
  renderCartPanel();
}

// Update item quantity
function updateQuantity(id, delta) {
  const item = cart.find(item => item.id === id);
  if (item) {
    item.quantity += delta;
    if (item.quantity <= 0) {
      removeFromCart(id);
    } else {
      saveCart();
      renderCartPanel();
    }
  }
}

// Clear entire cart
function clearCart() {
  cart = [];
  saveCart();
  renderCartPanel();
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
function showOrderPopup(itemId, itemTitle, itemPrice, itemImage) {
  const modal = document.getElementById('orderItemModal');
  if (!modal) return;

  // Fill modal with item details
  const modalTitle = modal.querySelector('.order-item-title');
  const modalPrice = modal.querySelector('.order-item-price');
  const modalImage = modal.querySelector('.order-item-image');
  const quantityInput = modal.querySelector('#orderQuantity');
  const totalDisplay = modal.querySelector('.order-item-total');

  if (modalTitle) modalTitle.textContent = itemTitle;
  if (modalPrice) modalPrice.textContent = `৳${itemPrice}`;
  if (modalImage) {
    if (itemImage) {
      modalImage.innerHTML = `<img src="${itemImage}" alt="${itemTitle}">`;
    } else {
      modalImage.innerHTML = '<i class="bi bi-image text-muted" style="font-size: 3rem;"></i>';
    }
  }
  if (quantityInput) quantityInput.value = 1;
  if (totalDisplay) totalDisplay.textContent = `৳${itemPrice}`;

  // Store item data in modal
  modal.dataset.itemId = itemId;
  modal.dataset.itemTitle = itemTitle;
  modal.dataset.itemPrice = itemPrice;
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
function addToCartFromPopup() {
  const modal = document.getElementById('orderItemModal');
  if (!modal) return;

  const itemId = modal.dataset.itemId;
  const itemTitle = modal.dataset.itemTitle;
  const itemPrice = parseFloat(modal.dataset.itemPrice);
  const itemImage = modal.dataset.itemImage;
  const quantity = parseInt(document.getElementById('orderQuantity')?.value) || 1;

  // Add item to cart with specified quantity
  const existingItem = cart.find(item => item.id === itemId);
  if (existingItem) {
    existingItem.quantity += quantity;
  } else {
    cart.push({
      id: itemId,
      title: itemTitle,
      price: itemPrice,
      image: itemImage,
      quantity: quantity
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
function showOrderSuccessModal(orderId) {
  const modal = document.getElementById('orderSuccessModal');
  if (modal) {
    const orderIdSpan = modal.querySelector('.order-id-display');
    if (orderIdSpan) {
      orderIdSpan.textContent = orderId || '';
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

  cartItemsContainer.innerHTML = cart.map(item => `
    <div class="cart-item" data-id="${item.id}">
      <div class="cart-item-image">
        ${item.image ? `<img src="${item.image}" alt="${item.title}">` : '<i class="bi bi-image text-muted"></i>'}
      </div>
      <div class="cart-item-details">
        <h6 class="cart-item-title">${item.title}</h6>
        <span class="cart-item-price">৳${item.price}</span>
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
  `).join('');

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
function openCheckoutModal() {
  closeCartPanel();
  
  const modal = document.getElementById('checkoutModal');
  if (!modal) return;

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
  const confirmBtn = document.getElementById('confirmOrderBtn');
  
  if (cart.length === 0) {
    alert('Your cart is empty!');
    return;
  }

  // Disable button and show loading
  if (confirmBtn) {
    confirmBtn.disabled = true;
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
        special_instructions: instructions
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
      showOrderSuccessModal(result.order_id);
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

  // Check login status first
  const loggedIn = await checkLoginStatus();

  if (!loggedIn) {
    showLoginModal();
    return;
  }

  // Show order popup
  showOrderPopup(itemId, itemTitle, itemPrice, itemImage);
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
  document.addEventListener('click', function(e) {
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
        input.value = parseInt(input.value) + 1;
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
});

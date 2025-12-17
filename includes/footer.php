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

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" id="orderForm">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Place Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="orderMenuId">
          <div class="mb-3">
            <label class="form-label">Table Number</label>
            <input type="text" id="orderTableNumber" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select id="orderPayment" class="form-select" required>
              <option value="">Choose...</option>
              <option value="Cash">Cash</option>
              <option value="bKash">bKash</option>
              <option value="Card">Card</option>
            </select> 
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit">Confirm Order</button>
        </div>
      </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="js/user.js"></script>

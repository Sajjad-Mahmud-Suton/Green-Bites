<!-- ╔═══════════════════════════════════════════════════════════════════════════╗
     ║                    EVENT BOOKINGS MANAGEMENT SECTION                     ║
     ╠═══════════════════════════════════════════════════════════════════════════╣
     ║  Features:                                                               ║
     ║  • Add/Edit/Delete event bookings                                        ║
     ║  • Filter by: upcoming, past, this week, this month, etc.                ║
     ║  • Status management and payment tracking                                ║
     ╚═══════════════════════════════════════════════════════════════════════════╝ -->

<div id="events" class="section-tab">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <h4 class="mb-1"><i class="bi bi-calendar-event me-2 text-success"></i>Event Bookings</h4>
      <p class="text-muted mb-0 small">Manage event reservations and bookings</p>
    </div>
    <button class="btn btn-success" onclick="openEventBookingModal()">
      <i class="bi bi-plus-lg me-1"></i>New Booking
    </button>
  </div>

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-1 opacity-75 small">Total Bookings</p>
              <h3 class="mb-0" id="eventTotalBookings">0</h3>
            </div>
            <i class="bi bi-calendar-check fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-1 opacity-75 small">Upcoming Events</p>
              <h3 class="mb-0" id="eventUpcoming">0</h3>
            </div>
            <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-1 opacity-75 small">Today's Events</p>
              <h3 class="mb-0" id="eventToday">0</h3>
            </div>
            <i class="bi bi-calendar-day fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-1 opacity-75 small">Total Revenue</p>
              <h4 class="mb-0" id="eventTotalRevenue">৳0</h4>
            </div>
            <i class="bi bi-cash-stack fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted">Filter by Time</label>
          <select class="form-select" id="eventTimeFilter" onchange="loadEventBookings()">
            <option value="all">All Bookings</option>
            <option value="today">Today</option>
            <option value="upcoming" selected>Upcoming Events</option>
            <option value="this_week">This Week</option>
            <option value="this_month">This Month</option>
            <option value="next_month">Next Month</option>
            <option value="past_week">Past Week</option>
            <option value="past">Past Events</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted">Status</label>
          <select class="form-select" id="eventStatusFilter" onchange="loadEventBookings()">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted">Event Type</label>
          <select class="form-select" id="eventTypeFilter" onchange="loadEventBookings()">
            <option value="">All Types</option>
            <option value="birthday">🎂 Birthday</option>
            <option value="wedding">💒 Wedding</option>
            <option value="corporate">🏢 Corporate</option>
            <option value="anniversary">💑 Anniversary</option>
            <option value="graduation">🎓 Graduation</option>
            <option value="reunion">👨‍👩‍👧‍👦 Reunion</option>
            <option value="other">📅 Other</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted">Search</label>
          <input type="text" class="form-control" id="eventSearch" placeholder="Search events..." oninput="debounceEventSearch()">
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-secondary w-100" onclick="resetEventFilters()">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bookings Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-3">Event Details</th>
              <th>Customer</th>
              <th>Date & Time</th>
              <th>Guests</th>
              <th>Package</th>
              <th>Amount</th>
              <th>Payment</th>
              <th>Status</th>
              <th class="text-end pe-3">Actions</th>
            </tr>
          </thead>
          <tbody id="eventBookingsTable">
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">
                <div class="spinner-border text-success" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Loading bookings...</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Event Booking Modal -->
<div class="modal fade" id="eventBookingModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="eventBookingModalTitle">
          <i class="bi bi-calendar-plus me-2"></i>New Event Booking
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="eventBookingForm">
          <input type="hidden" id="eventBookingId" value="">
          
          <!-- Event Details Section -->
          <h6 class="text-muted mb-3"><i class="bi bi-calendar-event me-2"></i>Event Details</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-8">
              <label class="form-label">Event Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="eventName" required placeholder="e.g., John's Birthday Party">
            </div>
            <div class="col-md-4">
              <label class="form-label">Event Type <span class="text-danger">*</span></label>
              <select class="form-select" id="eventType" required>
                <option value="birthday">🎂 Birthday</option>
                <option value="wedding">💒 Wedding</option>
                <option value="corporate">🏢 Corporate</option>
                <option value="anniversary">💑 Anniversary</option>
                <option value="graduation">🎓 Graduation</option>
                <option value="reunion">👨‍👩‍👧‍👦 Reunion</option>
                <option value="other">📅 Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Event Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="eventDate" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Start Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="eventTime" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">End Time</label>
              <input type="time" class="form-control" id="eventEndTime">
            </div>
            <div class="col-md-4">
              <label class="form-label">Number of Guests <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="eventGuestCount" min="1" value="1" required>
            </div>
            <div class="col-md-8">
              <label class="form-label">Venue</label>
              <input type="text" class="form-control" id="eventVenue" value="Green Bites Restaurant" placeholder="Event venue">
            </div>
          </div>

          <!-- Customer Details Section -->
          <h6 class="text-muted mb-3"><i class="bi bi-person me-2"></i>Customer Details</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label">Customer Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="eventCustomerName" required placeholder="Full name">
            </div>
            <div class="col-md-4">
              <label class="form-label">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" id="eventCustomerPhone" required placeholder="01XXXXXXXXX">
            </div>
            <div class="col-md-4">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="eventCustomerEmail" placeholder="customer@email.com">
            </div>
          </div>

          <!-- Package & Payment Section -->
          <h6 class="text-muted mb-3"><i class="bi bi-box-seam me-2"></i>Package & Payment</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label class="form-label">Package Type</label>
              <select class="form-select" id="eventPackageType">
                <option value="basic">Basic</option>
                <option value="standard" selected>Standard</option>
                <option value="premium">Premium</option>
                <option value="custom">Custom</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Total Amount (৳)</label>
              <input type="number" class="form-control" id="eventTotalAmount" min="0" step="0.01" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Advance Paid (৳)</label>
              <input type="number" class="form-control" id="eventAdvanceAmount" min="0" step="0.01" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Payment Status</label>
              <select class="form-select" id="eventPaymentStatus">
                <option value="pending">Pending</option>
                <option value="partial">Partial</option>
                <option value="paid">Paid</option>
                <option value="refunded">Refunded</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Booking Status</label>
              <select class="form-select" id="eventBookingStatus">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <!-- Additional Details Section -->
          <h6 class="text-muted mb-3"><i class="bi bi-list-check me-2"></i>Additional Details</h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Menu Items</label>
              <textarea class="form-control" id="eventMenuItems" rows="3" placeholder="List of menu items for the event..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Decorations</label>
              <textarea class="form-control" id="eventDecorations" rows="3" placeholder="Decoration requirements..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Special Requirements</label>
              <textarea class="form-control" id="eventSpecialRequirements" rows="3" placeholder="Any special requirements..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Notes</label>
              <textarea class="form-control" id="eventNotes" rows="3" placeholder="Internal notes..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="saveEventBooking()">
          <i class="bi bi-check-lg me-1"></i>Save Booking
        </button>
      </div>
    </div>
  </div>
</div>

<!-- View Event Details Modal -->
<div class="modal fade" id="viewEventModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>Event Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewEventContent">
        <!-- Content loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="editEventFromViewBtn">
          <i class="bi bi-pencil me-1"></i>Edit
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Event Booking Styles */
.event-type-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}
.event-type-birthday { background: #fce7f3; color: #be185d; }
.event-type-wedding { background: #f0fdf4; color: #16a34a; }
.event-type-corporate { background: #eff6ff; color: #2563eb; }
.event-type-anniversary { background: #fef3c7; color: #d97706; }
.event-type-graduation { background: #f3e8ff; color: #7c3aed; }
.event-type-reunion { background: #ecfeff; color: #0891b2; }
.event-type-other { background: #f3f4f6; color: #6b7280; }

.payment-badge {
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}
.payment-pending { background: #fef3c7; color: #b45309; }
.payment-partial { background: #dbeafe; color: #1d4ed8; }
.payment-paid { background: #dcfce7; color: #16a34a; }
.payment-refunded { background: #fee2e2; color: #dc2626; }

.booking-status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}
.status-pending { background: #fef3c7; color: #b45309; }
.status-confirmed { background: #dbeafe; color: #1d4ed8; }
.status-in_progress { background: #fae8ff; color: #a21caf; }
.status-completed { background: #dcfce7; color: #16a34a; }
.status-cancelled { background: #fee2e2; color: #dc2626; }

.event-detail-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 15px;
}
.event-detail-card h6 {
  color: #16a34a;
  border-bottom: 2px solid #dcfce7;
  padding-bottom: 8px;
  margin-bottom: 15px;
}
.event-detail-row {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
  border-bottom: 1px dashed #e5e7eb;
}
.event-detail-row:last-child {
  border-bottom: none;
}
.event-detail-label {
  color: #6b7280;
  font-size: 0.9rem;
}
.event-detail-value {
  font-weight: 600;
  color: #1f2937;
}
</style>

<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Update Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="edit-id">
        <div class="alert d-none mb-3" id="edit-stock-alert" role="alert"></div>
        {{-- <div class="border rounded p-3 mb-3 bg-light" id="edit-stock-panel">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-box-seam fs-5 text-secondary" id="edit-stock-icon"></i>
                <div>
                    <div class="text-muted small">Dealer Area Stock</div>
                    <strong id="edit-stock-status">Select an order</strong>
                    <small class="d-block text-muted" id="edit-stock-help">Inventory availability will appear here.</small>
                </div>
            </div>
        </div> --}}
        {{-- <div class="row g-2 mb-3" id="edit-stock-metrics">
            <div class="col-6">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted fw-semibold text-uppercase">Stock After Movement</small>
                    <div class="fw-bold" id="edit-stock-after">-</div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted fw-semibold text-uppercase">Sales Orders</small>
                    <div class="fw-bold" id="edit-sales-orders">-</div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted fw-semibold text-uppercase">Available</small>
                    <div class="fw-bold" id="edit-available-stock">-</div>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded p-2 h-100">
                    <small class="text-muted fw-semibold text-uppercase">Status</small>
                    <div class="fw-bold" id="edit-inventory-status">-</div>
                </div>
            </div>
        </div> --}}
         <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" id="edit-qty" min="1" step="1">
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select id="edit-payment" class="form-select">
                <option value="cash">Cash</option>
                <option value="voucher">Voucher</option>
                <option value="gcash">GCash</option>
                 <option value="bank_transfer">Bank Transfer</option>
                <option value="credit">Credit</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Delivery Type</label>
            <select id="edit-delivery" class="form-select" disabled>
                <option value="pickup">Pickup</option>
                <option value="delivery">Delivery</option>
            </select>
        </div>

        <div class="mb-3 d-none" id="edit-delivery-fee-wrapper">
            <label class="form-label">Delivery Fee</label>
            <input type="number" class="form-control" id="edit-delivery-fee" min="0" step="0.01" placeholder="0.00">
        </div>

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select id="edit-status" class="form-select">
            <option value="Pending">Pending</option>
            <option value="For Verification">For Pickup Verification</option>
            <option value="For Delivery">For Delivery</option>
            {{-- <option value="SO Created">SO Created</option> --}}
            {{-- <option value="Completed">Completed</option> --}}
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>

        <div class="mb-3 d-none" id="edit-cancellation-remarks-wrapper">
            <label class="form-label" for="edit-cancellation-remarks">Cancellation Remarks <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit-cancellation-remarks" rows="3" maxlength="1000" placeholder="State the reason for cancelling this order."></textarea>
            <div class="form-text">Remarks are required when the order is cancelled.</div>
        </div>
      </div>

      <div class="modal-footer border-0">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success" id="update-btn">
            <i class="bi bi-check-circle"></i> Update
        </button>
      </div>
    </div>
  </div>
</div>

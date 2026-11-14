<?php $this->load->helper('form'); ?>
<div class="mp-page-head">
  <div>
    <h2>Sell Vehicle</h2>
    <div class="mp-page-sub">Record payment and generate a receipt</div>
  </div>
</div>

<div class="mp-card" style="margin-top:24px;">
  <div class="mp-card-head"><h3>Vehicle</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid" style="grid-template-columns: repeat(4, 1fr); gap: 16px;">
      <div class="form-group">
        <label>Make / Model</label>
        <p class="form-control-static" style="font-weight:600;"><?= htmlspecialchars($vehicle->make . ' ' . $vehicle->model); ?></p>
      </div>
      <div class="form-group">
        <label>Year</label>
        <p class="form-control-static"><?= htmlspecialchars($vehicle->year ?: '-'); ?></p>
      </div>
      <div class="form-group">
        <label>VIN</label>
        <p class="form-control-static"><?= htmlspecialchars($vehicle->vin ?: '-'); ?></p>
      </div>
      <div class="form-group">
        <label>Price</label>
        <p class="form-control-static" style="color:var(--mp-primary); font-weight:700;"><?= number_format($vehicle->price, 2); ?></p>
      </div>
    </div>
  </div>
</div>

<?= form_open('automobile/save_sale', ['class' => 'mp-form', 'style' => 'margin-top:24px;']); ?>
<input type="hidden" name="vehicle_id" value="<?= (int) $vehicle->id; ?>">
<input type="hidden" id="vehicle_price" value="<?= (float) $vehicle->price; ?>">

<div class="mp-card">
  <div class="mp-card-head"><h3>Buyer &amp; Payment</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px;">
      <div class="form-group">
        <label>Buyer / Customer Name <span class="text-danger">*</span></label>
        <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($vehicle->customer_name); ?>" placeholder="e.g. John Doe" required>
      </div>
      <div class="form-group">
        <label>Customer ID (optional)</label>
        <input type="number" name="customer_id" class="form-control" value="<?= (int) $vehicle->customer_id; ?>" placeholder="Existing customer ID">
      </div>
      <div class="form-group">
        <label>Payment Method</label>
        <select name="payment_method" class="form-control">
          <option value="Cash">Cash</option>
          <option value="Bank Transfer">Bank Transfer</option>
          <option value="Cheque">Cheque</option>
          <option value="Mobile Money">Mobile Money</option>
          <option value="Card">Card</option>
        </select>
      </div>
      <div class="form-group">
        <label>Amount Paid <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" value="<?= number_format($vehicle->price, 2, '.', ''); ?>" placeholder="0.00" required>
      </div>
      <div class="form-group">
        <label>Balance</label>
        <p class="form-control-static" id="balance" style="font-weight:700;">0.00</p>
      </div>
    </div>
  </div>
</div>

<div class="mp-form-actions" style="margin-top:24px;">
  <button type="submit" class="mp-btn green"><i class="fa fa-check"></i> Complete Sale &amp; Print Receipt</button>
  <a href="<?= base_url('automobile/list'); ?>" class="mp-btn">Cancel</a>
</div>

<?= form_close(); ?>

<script>
  var price = parseFloat(document.getElementById('vehicle_price').value) || 0;
  var amountInput = document.getElementById('amount_paid');
  var balanceEl = document.getElementById('balance');
  function updateBalance() {
    var paid = parseFloat(amountInput.value) || 0;
    balanceEl.textContent = (price - paid).toFixed(2);
  }
  amountInput.addEventListener('input', updateBalance);
  updateBalance();
</script>

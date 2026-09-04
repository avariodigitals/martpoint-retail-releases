<?php $this->load->view('marketing/desktop/_styles'); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Issue Store Credit'); ?></h2>
    <div class="mp-page-sub">Apply credit to a customer account</div>
  </div>
</div>

<form class="form-horizontal" id="store-credit-form" onkeypress="return event.keyCode != 13;">
  <input type="hidden" name="<?= htmlspecialchars($this->security->get_csrf_token_name()); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash()); ?>">

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Store Credit Details</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">

        <div class="mp-form-group">
          <label for="customer_id">Customer <span class="text-danger">*</span></label>
          <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%" required>
            <option value="">-- Select Customer --</option>
            <?= get_customers_select_list('', get_current_store_id()); ?>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="amount">Amount <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0" class="mp-form-control only_currency" id="amount" name="amount" required>
        </div>

        <div class="mp-form-group">
          <label for="source">Source</label>
          <select class="mp-form-control" id="source" name="source">
            <option value="refund">Refund</option>
            <option value="return">Product Return</option>
            <option value="compensation">Compensation</option>
            <option value="manual">Manual Credit</option>
            <option value="promotion">Promotion</option>
            <option value="loyalty_conversion">Loyalty Conversion</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="expiry_days">Expiry (Days)</label>
          <input type="number" min="0" class="mp-form-control" id="expiry_days" name="expiry_days" placeholder="0 = Never expires">
        </div>

        <div class="mp-form-group full">
          <label for="notes">Notes</label>
          <textarea class="mp-form-control" id="notes" name="notes" rows="3"></textarea>
        </div>

      </div>
    </div>
  </div>

  <div class="mp-form-actions">
    <button type="button" class="mp-btn-primary" onclick="save_credit()">Issue Credit</button>
    <a href="<?= base_url('store_credit'); ?>" class="mp-btn-secondary">Back</a>
  </div>
</form>

<script>
function save_credit(){
    var form = $('#store-credit-form').serialize();
    $.post(base_url + 'store_credit/save', form, function(res){
        if(res=='success'){ success_show('Store credit issued'); setTimeout(function(){ window.location = base_url+'store_credit'; }, 1000); }
        else{ error_show('Failed: '+res); }
    });
}
$(".store-credit-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>

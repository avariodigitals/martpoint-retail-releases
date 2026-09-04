<?php $this->load->view('marketing/desktop/_styles'); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Gift Card'); ?></h2>
    <div class="mp-page-sub"><?= isset($card) ? 'Update gift card details' : 'Create a new gift card'; ?></div>
  </div>
</div>

<form class="form-horizontal" id="gift-card-form" onkeypress="return event.keyCode != 13;">
  <input type="hidden" name="<?= htmlspecialchars($this->security->get_csrf_token_name()); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash()); ?>">
  <input type="hidden" id="card_id" name="card_id" value="<?= isset($card) ? htmlspecialchars($card->id) : ''; ?>">

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Gift Card Details</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">

        <div class="mp-form-group">
          <label for="card_number">Card Number</label>
          <input type="text" class="mp-form-control" id="card_number" name="card_number" value="<?= isset($card) ? htmlspecialchars($card->card_number) : ''; ?>" placeholder="Auto-generated if empty">
        </div>

        <div class="mp-form-group">
          <label for="customer_id">Customer (Optional)</label>
          <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%">
            <option value="">-- Select Customer --</option>
            <?= get_customers_select_list(isset($card) ? $card->customer_id : '', get_current_store_id()); ?>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="initial_value">Initial Value <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="0" class="mp-form-control only_currency" id="initial_value" name="initial_value" value="<?= isset($card) ? htmlspecialchars($card->initial_value) : ''; ?>" required>
        </div>

        <div class="mp-form-group">
          <label for="card_type">Card Type</label>
          <select class="mp-form-control" id="card_type" name="card_type">
            <option value="physical" <?= isset($card) && $card->card_type=='physical' ? 'selected' : ''; ?>>Physical</option>
            <option value="digital" <?= isset($card) && $card->card_type=='digital' ? 'selected' : ''; ?>>Digital</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="expiry_days">Expiry (Days)</label>
          <input type="number" min="0" class="mp-form-control" id="expiry_days" name="expiry_days" value="" placeholder="0 = Never expires">
        </div>

        <div class="mp-form-group full">
          <label for="notes">Notes</label>
          <textarea class="mp-form-control" id="notes" name="notes" rows="3"><?= isset($card) ? htmlspecialchars($card->notes) : ''; ?></textarea>
        </div>

      </div>
    </div>
  </div>

  <div class="mp-form-actions">
    <button type="button" class="mp-btn-primary" onclick="save_card()"><?= isset($card) ? 'Update' : 'Save'; ?></button>
    <a href="<?= base_url('gift_cards'); ?>" class="mp-btn-secondary">Back</a>
  </div>
</form>

<script>
function save_card(){
    var form = $('#gift-card-form').serialize();
    var url = base_url + 'gift_cards/save';
    if($('#card_id').val()) url = base_url + 'gift_cards/update';
    $.post(url, form, function(res){
        if(res=='success'){ success_show('Gift card saved'); setTimeout(function(){ window.location = base_url+'gift_cards'; }, 1000); }
        else{ error_show('Failed: '+res); }
    });
}
$(".gift-cards-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>

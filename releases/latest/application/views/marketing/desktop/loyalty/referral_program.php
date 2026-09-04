<?php $this->load->view('marketing/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Referral Program'); ?></h2>
    <div class="mp-page-sub">Referral Program Settings</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Referral Program</h3></div>
  <div class="mp-card-body">
    <form id="referral-form">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash()); ?>">
      <div class="mp-form-grid">

        <div class="mp-form-group">
          <label for="referral_enabled">Enable Referral Program</label>
          <select class="mp-form-control" name="referral_enabled" id="referral_enabled">
            <option value="1" <?= (isset($settings->referral_enabled) && $settings->referral_enabled == 1) ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?= (isset($settings->referral_enabled) && $settings->referral_enabled == 0) ? 'selected' : ''; ?>>No</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="referrer_reward_type">Referrer Reward Type</label>
          <select class="mp-form-control" name="referrer_reward_type" id="referrer_reward_type">
            <option value="points" <?= (isset($settings->referrer_reward_type) && $settings->referrer_reward_type == 'points') ? 'selected' : ''; ?>>Points</option>
            <option value="credit" <?= (isset($settings->referrer_reward_type) && $settings->referrer_reward_type == 'credit') ? 'selected' : ''; ?>>Store Credit</option>
            <option value="discount" <?= (isset($settings->referrer_reward_type) && $settings->referrer_reward_type == 'discount') ? 'selected' : ''; ?>>Discount %</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="referrer_reward_value">Referrer Reward Value</label>
          <input type="number" class="mp-form-control" name="referrer_reward_value" id="referrer_reward_value" value="<?= htmlspecialchars(isset($settings->referrer_reward_value) ? $settings->referrer_reward_value : 100); ?>">
        </div>

        <div class="mp-form-group">
          <label for="new_customer_reward_type">New Customer Reward Type</label>
          <select class="mp-form-control" name="new_customer_reward_type" id="new_customer_reward_type">
            <option value="points" <?= (isset($settings->new_customer_reward_type) && $settings->new_customer_reward_type == 'points') ? 'selected' : ''; ?>>Points</option>
            <option value="credit" <?= (isset($settings->new_customer_reward_type) && $settings->new_customer_reward_type == 'credit') ? 'selected' : ''; ?>>Store Credit</option>
            <option value="discount" <?= (isset($settings->new_customer_reward_type) && $settings->new_customer_reward_type == 'discount') ? 'selected' : ''; ?>>Discount %</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="new_customer_reward_value">New Customer Reward Value</label>
          <input type="number" class="mp-form-control" name="new_customer_reward_value" id="new_customer_reward_value" value="<?= htmlspecialchars(isset($settings->new_customer_reward_value) ? $settings->new_customer_reward_value : 50); ?>">
        </div>

        <div class="mp-form-group">
          <label for="referral_approval_required">Approval Required</label>
          <select class="mp-form-control" name="referral_approval_required" id="referral_approval_required">
            <option value="1" <?= (isset($settings->referral_approval_required) && $settings->referral_approval_required == 1) ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?= (isset($settings->referral_approval_required) && $settings->referral_approval_required == 0) ? 'selected' : ''; ?>>No</option>
          </select>
        </div>

      </div>
      <div class="mp-form-actions" style="margin-top:20px;">
        <button type="button" class="mp-btn-primary" onclick="save_settings()"><i class="fa fa-save"></i> Save Settings</button>
      </div>
    </form>
  </div>
</div>

<script>
function save_settings(){
    var form = $('#referral-form').serialize();
    $.post(base_url + 'loyalty/save_referral_settings', form, function(res){
        if(res=='success'){ success_show('Settings saved'); }
        else{ error_show('Failed: ' + res); console.log('save_referral_settings response:', res); }
    });
}
$(".loyalty-referral-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>

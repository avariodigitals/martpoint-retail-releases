<?php $this->load->view('marketing/desktop/_styles'); ?>
<style>
/* Toggle switch for Priority Service */
.mp-toggle{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0}
.mp-toggle input{opacity:0;width:0;height:0;position:absolute}
.mp-toggle-slider{position:absolute;cursor:pointer;inset:0;background:var(--mp-border);border-radius:24px;transition:.2s}
.mp-toggle-slider::before{content:"";position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.mp-toggle input:checked + .mp-toggle-slider{background:var(--mp-primary)}
.mp-toggle input:checked + .mp-toggle-slider::before{transform:translateX(18px)}
.mp-toggle input:focus-visible + .mp-toggle-slider{box-shadow:0 0 0 3px rgba(0,87,255,.2)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Loyalty Settings'); ?></h2>
    <div class="mp-page-sub">Configure Loyalty Program</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Loyalty Settings</h3></div>
  <div class="mp-card-body">
    <form id="loyalty-settings-form" method="post">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash()); ?>">
      <div class="mp-form-grid">

        <div class="mp-form-group">
          <label for="loyalty_enabled">Enable Loyalty Program</label>
          <select class="mp-form-control" name="loyalty_enabled" id="loyalty_enabled">
            <option value="1" <?= ($settings->loyalty_enabled == 1) ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?= ($settings->loyalty_enabled == 0) ? 'selected' : ''; ?>>No</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="earning_type">Earning Type</label>
          <select class="mp-form-control" name="earning_type" id="earning_type">
            <option value="spend_based" <?= ($settings->earning_type == 'spend_based') ? 'selected' : ''; ?>>Spend Based (e.g. every NGN 1,000 = 1 point)</option>
            <option value="percentage_based" <?= ($settings->earning_type == 'percentage_based') ? 'selected' : ''; ?>>Percentage Based (e.g. 2% of purchase)</option>
            <option value="product_specific" <?= ($settings->earning_type == 'product_specific') ? 'selected' : ''; ?>>Product Specific</option>
            <option value="service_specific" <?= ($settings->earning_type == 'service_specific') ? 'selected' : ''; ?>>Service Specific</option>
          </select>
        </div>

        <div class="mp-form-group spend-based">
          <label for="spend_amount">Spend Amount</label>
          <input type="number" class="mp-form-control" name="spend_amount" id="spend_amount" value="<?= htmlspecialchars($settings->spend_amount ?? ''); ?>" placeholder="1000">
        </div>

        <div class="mp-form-group spend-based">
          <label for="points_earned">Points Earned</label>
          <input type="number" class="mp-form-control" name="points_earned" id="points_earned" value="<?= htmlspecialchars($settings->points_earned ?? ''); ?>" placeholder="Points Earned">
        </div>

        <div class="mp-form-group full percentage-based" style="display:none;">
          <label for="percentage_rate">Percentage Rate (%)</label>
          <input type="number" step="0.01" class="mp-form-control" name="percentage_rate" id="percentage_rate" value="<?= htmlspecialchars($settings->percentage_rate ?? ''); ?>" placeholder="2">
        </div>

        <div class="mp-form-group">
          <label for="redemption_rate">Redemption Rate (Points = NGN)</label>
          <input type="number" step="0.01" class="mp-form-control" name="redemption_rate" id="redemption_rate" value="<?= htmlspecialchars($settings->redemption_rate ?? ''); ?>" placeholder="10">
          <p class="mp-form-hint">e.g. 100 points = NGN 1,000 discount</p>
        </div>

        <div class="mp-form-group">
          <label for="minimum_redemption_points">Minimum Redemption Points</label>
          <input type="number" class="mp-form-control" name="minimum_redemption_points" id="minimum_redemption_points" value="<?= htmlspecialchars($settings->minimum_redemption_points ?? ''); ?>" placeholder="100">
        </div>

        <div class="mp-form-group">
          <label for="maximum_redemption_per_sale">Maximum Redemption Per Sale</label>
          <input type="number" class="mp-form-control" name="maximum_redemption_per_sale" id="maximum_redemption_per_sale" value="<?= htmlspecialchars($settings->maximum_redemption_per_sale ?? ''); ?>" placeholder="0 = Unlimited">
        </div>

        <div class="mp-form-group">
          <label for="allow_partial_redemption">Allow Partial Redemption</label>
          <select class="mp-form-control" name="allow_partial_redemption" id="allow_partial_redemption">
            <option value="1" <?= ($settings->allow_partial_redemption == 1) ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?= ($settings->allow_partial_redemption == 0) ? 'selected' : ''; ?>>No</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="tier_calculation">Tier Calculation Based On</label>
          <select class="mp-form-control" name="tier_calculation" id="tier_calculation">
            <option value="lifetime_spend" <?= ($settings->tier_calculation == 'lifetime_spend') ? 'selected' : ''; ?>>Lifetime Spend</option>
            <option value="points" <?= ($settings->tier_calculation == 'points') ? 'selected' : ''; ?>>Points Balance</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="flexpay_points_timing">PayPlan Points Timing</label>
          <select class="mp-form-control" name="flexpay_points_timing" id="flexpay_points_timing">
            <option value="full_payment" <?= ($settings->flexpay_points_timing == 'full_payment') ? 'selected' : ''; ?>>Only After Full Payment</option>
            <option value="immediately" <?= ($settings->flexpay_points_timing == 'immediately') ? 'selected' : ''; ?>>Immediately After Deposit</option>
            <option value="disabled" <?= ($settings->flexpay_points_timing == 'disabled') ? 'selected' : ''; ?>>Disabled</option>
          </select>
        </div>

      </div>
      <div class="mp-form-actions" style="margin-top:20px;">
        <button type="button" class="mp-btn-primary" onclick="save_settings()"><i class="fa fa-save"></i> Save Settings</button>
      </div>
    </form>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Customer Tiers</h3>
    <button type="button" class="mp-btn-primary" onclick="open_tier_modal()"><i class="fa fa-plus"></i> Add Tier</button>
  </div>
  <div class="mp-card-body">
    <div class="mp-table-wrap">
      <table class="mp-static-table">
        <thead>
          <tr>
            <th>Name</th><th>Min. Spend</th><th>Min. Points</th><th>Discount %</th><th>Bonus Points %</th><th>Priority Service</th><th>Birthday Reward</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tiers as $tier) : ?>
          <tr>
            <td class="row-name"><?= htmlspecialchars($tier->tier_name); ?></td>
            <td class="amt"><?= store_number_format($tier->minimum_spend); ?></td>
            <td><?= htmlspecialchars($tier->minimum_points); ?></td>
            <td><?= htmlspecialchars($tier->discount_percentage); ?>%</td>
            <td><?= htmlspecialchars($tier->bonus_points_percentage); ?>%</td>
            <td><?= $tier->priority_service ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
            <td><?= htmlspecialchars(ucfirst($tier->birthday_reward_type)); ?> (<?= store_number_format($tier->birthday_reward_value); ?>)</td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="edit_tier(<?= (int)$tier->id; ?>,'<?= htmlspecialchars($tier->tier_name, ENT_QUOTES, 'UTF-8'); ?>',<?= (float)$tier->minimum_spend; ?>,<?= (float)$tier->minimum_points; ?>,<?= (float)$tier->discount_percentage; ?>,<?= (float)$tier->bonus_points_percentage; ?>,<?= (int)$tier->priority_service; ?>,'<?= htmlspecialchars($tier->birthday_reward_type, ENT_QUOTES, 'UTF-8'); ?>',<?= (float)$tier->birthday_reward_value; ?>,<?= (int)$tier->sort_order; ?>)"><i class="fa fa-edit"></i></button>
                <button class="mp-delete" title="Delete" onclick="delete_tier(<?= (int)$tier->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($tiers)) : ?>
          <tr><td colspan="8" class="mp-empty-state">
            <div class="mp-empty-icon"><i class="fa fa-sitemap"></i></div>
            <h4>No tiers found</h4>
            <p>Add a customer tier to get started.</p>
          </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Tier Modal -->
<div class="modal fade" id="tier-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Customer Tier</h4>
      </div>
      <div class="modal-body">
        <form id="tier-form">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash()); ?>">
          <input type="hidden" name="tier_id" id="tier_id">
          <div class="mp-form-grid">
            <div class="mp-form-group full">
              <label for="tier_name">Tier Name</label>
              <input type="text" class="mp-form-control" name="tier_name" id="tier_name" required>
            </div>
            <div class="mp-form-group">
              <label for="minimum_spend">Minimum Spend</label>
              <input type="number" class="mp-form-control" name="minimum_spend" id="minimum_spend" value="0">
            </div>
            <div class="mp-form-group">
              <label for="minimum_points">Minimum Points</label>
              <input type="number" class="mp-form-control" name="minimum_points" id="minimum_points" value="0">
            </div>
            <div class="mp-form-group">
              <label for="discount_percentage">Discount %</label>
              <input type="number" step="0.01" class="mp-form-control" name="discount_percentage" id="discount_percentage" value="0">
            </div>
            <div class="mp-form-group">
              <label for="bonus_points_percentage">Bonus Points %</label>
              <input type="number" step="0.01" class="mp-form-control" name="bonus_points_percentage" id="bonus_points_percentage" value="0">
            </div>
            <div class="mp-form-group">
              <label for="birthday_reward_type">Birthday Reward Type</label>
              <select class="mp-form-control" name="birthday_reward_type" id="birthday_reward_type">
                <option value="discount">Discount %</option>
                <option value="voucher">Voucher Amount</option>
                <option value="points">Bonus Points</option>
                <option value="product">Free Product</option>
              </select>
            </div>
            <div class="mp-form-group">
              <label for="birthday_reward_value">Birthday Reward Value</label>
              <input type="number" class="mp-form-control" name="birthday_reward_value" id="birthday_reward_value" value="0">
            </div>
            <div class="mp-form-group">
              <label for="sort_order">Sort Order</label>
              <input type="number" class="mp-form-control" name="sort_order" id="sort_order" value="0">
            </div>
            <div class="mp-form-group full">
              <label style="display:flex!important;align-items:center!important;gap:10px!important;cursor:pointer!important;">
                <span class="mp-toggle">
                  <input type="checkbox" name="priority_service" id="priority_service" value="1">
                  <span class="mp-toggle-slider"></span>
                </span>
                <span>Priority Service</span>
              </label>
              <p class="mp-form-hint">Flag this tier for priority customer service.</p>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="save_tier()">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
function save_settings(){
    var form = $('#loyalty-settings-form').serialize();
    $.post(base_url + 'loyalty/save_settings', form, function(res){
        if(res=='success'){ success_show('Settings saved successfully'); }
        else{ error_show('Failed: ' + res); console.log('save_settings response:', res); }
    });
}
function open_tier_modal(){
    $('#tier-form')[0].reset(); $('#tier_id').val(''); $('#tier-modal').modal('show');
}
function edit_tier(id,name,min_spend,min_points,discount,bonus,priority,reward_type,reward_value,sort){
    $('#tier_id').val(id); $('#tier_name').val(name); $('#minimum_spend').val(min_spend); $('#minimum_points').val(min_points);
    $('#discount_percentage').val(discount); $('#bonus_points_percentage').val(bonus); $('#priority_service').prop('checked', priority==1);
    $('#birthday_reward_type').val(reward_type); $('#birthday_reward_value').val(reward_value); $('#sort_order').val(sort);
    $('#tier-modal').modal('show');
}
function save_tier(){
    var form = $('#tier-form').serialize();
    $.post(base_url + 'loyalty/save_tier', form, function(res){
        if(res=='success'){ success_show('Tier saved'); $('#tier-modal').modal('hide'); location.reload(); }
        else{ error_show('Failed: ' + res); console.log('save_tier response:', res); }
    });
}
function delete_tier(id){
    swal({
        title: "Are you sure?",
        text: "This tier will be deleted.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true
    }, function(isConfirm){
        if(isConfirm){
            $.post(base_url + 'loyalty/delete_tier/'+id, function(res){
                if(res=='success'){ success_show('Tier deleted'); location.reload(); }
                else{ error_show('Failed'); }
            });
        }
    });
}
$('#earning_type').on('change', function(){
    if($(this).val()=='spend_based'){ $('.spend-based').show(); $('.percentage-based').hide(); }
    else if($(this).val()=='percentage_based'){ $('.spend-based').hide(); $('.percentage-based').show(); }
    else{ $('.spend-based').hide(); $('.percentage-based').hide(); }
});
if($('#earning_type').val()=='percentage_based'){ $('.spend-based').hide(); $('.percentage-based').show(); }
$(".loyalty-settings-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>

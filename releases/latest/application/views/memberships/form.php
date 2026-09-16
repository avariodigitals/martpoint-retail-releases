<?php $this->load->view('creator/_styles'); $CI =& get_instance();
$isEdit = !empty($membership);
$selectedItem = $isEdit ? (int)$membership->item_id : (int)($preselect_item ?? 0);
$selectedItemRow = null;
foreach($items as $i){ if((int)$i->id === $selectedItem){ $selectedItemRow = $i; break; } }
$defaultName = $isEdit ? $membership->membership_name : ($selectedItemRow ? $selectedItemRow->item_name : '');
$interval = $isEdit ? $membership->billing_interval : 'monthly';
?>

<div class="mp-page-head">
  <div>
    <h2><?= $isEdit ? 'Billing: ' . htmlspecialchars($membership->membership_name) : 'Set Up Membership Billing'; ?></h2>
    <div class="mp-page-sub"><a href="<?= base_url('memberships'); ?>"><i class="fa fa-arrow-left"></i> Back to Memberships</a> &middot; Choose how often members are billed and whether they get a free trial.</div>
  </div>
  <?php if($isEdit): ?>
  <a href="<?= base_url('creator/edit/' . $membership->item_id); ?>" class="mp-btn"><i class="fa fa-tag"></i> Edit Product &amp; Price</a>
  <?php endif; ?>
</div>

<?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div><?php endif; ?>

<?php if(empty($items)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa fa-star"></i></div>
    <h3>Create the membership product first</h3>
    <p>A membership needs a product with a price so it can be sold on your storefront.</p>
    <a href="<?= base_url('creator/create/membership'); ?>" class="mp-btn mp-btn-primary"><i class="fa fa-plus"></i> New Membership Product</a>
  </div>
<?php else: ?>

<?= form_open(base_url('memberships/save/' . ($membership->id ?? '')), ['method' => 'POST']); ?>
<input type="hidden" name="id" value="<?= $membership->id ?? ''; ?>">

<div class="cr-grid-2">
  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Plan Details</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Membership Product <span class="text-danger">*</span></label>
            <select name="item_id" class="mp-form-control" required>
              <option value="">Select the product this plan belongs to</option>
              <?php foreach($items as $i): ?>
              <option value="<?= $i->id; ?>" <?= ($selectedItem === (int)$i->id) ? 'selected' : ''; ?>><?= htmlspecialchars($i->item_name); ?> &mdash; <?= $CI->currency($i->sales_price); ?></option>
              <?php endforeach; ?>
            </select>
            <p class="mp-form-hint">The product price is what members pay every billing cycle.</p>
          </div>
          <div class="mp-form-group">
            <label>Plan Name <span class="text-danger">*</span></label>
            <input type="text" name="membership_name" class="mp-form-control" value="<?= htmlspecialchars($defaultName); ?>" required placeholder="e.g. Inner Circle, Pro Community">
          </div>
          <div class="mp-form-group">
            <label>Billing Cycle <span class="text-danger">*</span></label>
            <select name="billing_interval" class="mp-form-control" required>
              <option value="weekly" <?= $interval === 'weekly' ? 'selected' : ''; ?>>Every week</option>
              <option value="monthly" <?= $interval === 'monthly' ? 'selected' : ''; ?>>Every month</option>
              <option value="yearly" <?= $interval === 'yearly' ? 'selected' : ''; ?>>Every year</option>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Free Trial (days)</label>
            <input type="number" name="trial_days" min="0" class="mp-form-control" value="<?= $isEdit ? (int)$membership->trial_days : 0; ?>">
            <p class="mp-form-hint">0 = no trial. Members are billed when the trial ends.</p>
          </div>
          <div class="mp-form-group full">
            <label>What members get</label>
            <textarea name="description" class="mp-form-control" rows="4" placeholder="e.g. Access to all courses, monthly live Q&amp;A, private community"><?= $isEdit ? htmlspecialchars($membership->description) : ''; ?></textarea>
            <p class="mp-form-hint">Shown on the storefront pricing card.</p>
          </div>
          <div class="mp-form-group full">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="status" value="1" <?= (!$isEdit || $membership->status) ? 'checked' : ''; ?> style="width:16px;height:16px;"> Live &mdash; new members can subscribe
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Save</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-actions" style="flex-direction:column;align-items:stretch;gap:10px!important;padding:0!important;">
          <button type="submit" class="mp-btn mp-btn-primary" style="width:100%;"><i class="fa fa-check"></i> Save Membership</button>
          <a href="<?= base_url('memberships'); ?>" class="mp-btn" style="width:100%;">Cancel</a>
        </div>
      </div>
    </div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>How it works</h3></div>
      <div class="mp-card-body" style="font-size:13px;color:var(--mp-muted);line-height:1.7;">
        <p style="margin:0 0 8px;">A member pays the product price at checkout and a subscription is created for one billing cycle.</p>
        <p style="margin:0 0 8px;">Their access, renewal date and payment status are tracked under <a href="<?= base_url('creator/members'); ?>">Members</a>.</p>
        <p style="margin:0;">Pause the plan at any time — existing members keep access until their cycle ends.</p>
      </div>
    </div>
  </div>
</div>
<?= form_close(); ?>
<?php endif; ?>

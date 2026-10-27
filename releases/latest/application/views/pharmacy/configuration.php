<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
<div class="pharma-workspace">
  <header class="pharma-hero">
    <div><span class="pharma-kicker"><span class="pharma-pulse"></span> Critical settings · Admin only</span><h1>Pharmacy configuration</h1><p>Choose the workflow that suits your pharmacy.</p></div>
    <a class="pharma-btn pharma-btn-light" href="<?= base_url('pharmacy_workspace/more'.(!empty($pharmacy_mobile) ? '?screen=mobile' : '')); ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Critical settings</a>
  </header>
  <?php if ($mode_message): ?><p class="pharma-mode-notice<?= $mode_error ? ' is-error' : ''; ?>" role="<?= $mode_error ? 'alert' : 'status'; ?>"><?= htmlspecialchars($mode_message, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
  <?php if ($legacy_mode): ?><p class="pharma-mode-notice">Your existing pharmacy setup is preserved. It will stay as it is until an administrator saves a mode below.</p><?php endif; ?>
  <?php if (!$mode_ready): ?><p class="pharma-mode-notice is-error" role="alert">Apply the pharmacy mode database update before changing this setting.</p><?php endif; ?>
  <form method="post" action="<?= base_url('pharmacy_workspace/save_configuration'.(!empty($pharmacy_mobile) ? '?screen=mobile' : '')); ?>" class="pharma-mode-form">
    <input type="hidden" name="<?= htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8'); ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8'); ?>">
    <fieldset<?= !$mode_ready ? ' disabled' : ''; ?>>
      <legend>Pharmacy mode</legend>
      <div class="pharma-mode-grid">
        <label class="pharma-mode-option"><input type="radio" name="pharmacy_mode" value="standard" required<?= $pharmacy_mode === 'standard' ? ' checked' : ''; ?>><span class="pharma-mode-copy"><strong>Standard Pharmacy</strong><small>Default for new pharmacies</small><span>Sell from your own stock, manage patients and prescriptions, and print dispensing receipts. A simple pharmacy website and checkout.</span></span></label>
        <label class="pharma-mode-option"><input type="radio" name="pharmacy_mode" value="advanced" required<?= $pharmacy_mode === 'advanced' ? ' checked' : ''; ?>><span class="pharma-mode-copy"><strong>Advanced Pharmacy</strong><small>For pharmacies with partner fulfilment</small><span>Includes the custom ordering design, partner stock locations, pickup hubs and branch transfer workflows.</span></span></label>
      </div>
      <p class="pharma-mode-hint">Both modes keep the green pharmacy branding. Switching preserves inventory, patients, prescriptions and sales. Existing orders and locations remain saved.</p>
      <button type="submit" class="pharma-btn pharma-btn-primary">Save pharmacy mode</button>
    </fieldset>
  </form>
  <p class="pharma-mode-hint"><a href="<?= base_url('business_profile'); ?>">Business profile and capabilities</a></p>
</div>

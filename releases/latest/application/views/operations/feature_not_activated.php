<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$feature_label   = $feature_label ?? 'This feature';
$feature_key     = $feature_key ?? '';
$industry_label  = $industry_label ?? '';
$back_url        = $back_url ?? base_url('dashboard');
$enable_url      = $enable_url ?? base_url('business_profile');
$icon            = $icon ?? 'fa-lock';
$description     = $description ?? '';
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.mp-feature-locked-card{max-width:640px;margin:40px auto 0;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:20px!important;box-shadow:var(--mp-shadow)!important;padding:48px 40px!important;text-align:center}
.mp-feature-locked-icon{width:80px;height:80px;border-radius:20px;background:var(--mp-bg);color:var(--mp-muted);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:34px}
.mp-feature-locked-status{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.mp-feature-locked-status .mp-status-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;border:1px solid var(--mp-border)}
.mp-feature-locked-status .mp-status-pill--muted{background:var(--mp-bg);color:var(--mp-muted)}
.mp-feature-locked-status .mp-status-pill--neutral{background:rgba(0,87,255,.06);color:var(--mp-primary)}
.mp-feature-locked-title{font-size:22px;font-weight:700;color:var(--mp-text);margin:0 0 12px}
.mp-feature-locked-text{font-size:14px;color:var(--mp-muted);line-height:1.6;margin:0 0 24px;max-width:480px;margin-left:auto;margin-right:auto}
.mp-feature-locked-meta{display:inline-flex;align-items:center;gap:8px;background:var(--mp-bg);border:1px solid var(--mp-border);border-radius:8px;padding:8px 14px;margin-bottom:28px}
.mp-feature-locked-meta .mp-meta-key{font-size:11px;font-weight:700;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.05em}
.mp-feature-locked-meta .mp-meta-val{font-size:12px;font-weight:600;color:var(--mp-ink);background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:6px;padding:3px 8px}
.mp-feature-locked-actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-bottom:28px}
.mp-feature-locked-actions .btn-mp-primary{display:inline-flex;align-items:center;gap:8px;background:var(--mp-primary);color:#fff;border:1px solid var(--mp-primary);border-radius:10px;padding:12px 22px;font-size:14px;font-weight:600;text-decoration:none;transition:all .15s ease}
.mp-feature-locked-actions .btn-mp-primary:hover{background:var(--mp-primary-dark);text-decoration:none;color:#fff}
.mp-feature-locked-actions .btn-mp-ghost{display:inline-flex;align-items:center;gap:8px;background:var(--mp-surface);color:var(--mp-ink);border:1px solid var(--mp-border);border-radius:10px;padding:12px 22px;font-size:14px;font-weight:600;text-decoration:none;transition:all .15s ease}
.mp-feature-locked-actions .btn-mp-ghost:hover{background:var(--mp-bg);text-decoration:none}
.mp-feature-locked-note{font-size:12px;color:var(--mp-muted);line-height:1.5;padding:14px 18px;background:var(--mp-bg);border-radius:10px;text-align:left;display:flex;gap:10px;align-items:flex-start}
.mp-feature-locked-note i{color:var(--mp-primary);flex-shrink:0;margin-top:2px}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($feature_label); ?></h2>
    <div class="mp-page-sub">Not Activated</div>
  </div>
</div>

<div class="mp-feature-locked-card">
  <div class="mp-feature-locked-icon">
    <i class="fa <?= htmlspecialchars($icon); ?>"></i>
  </div>
  <div class="mp-feature-locked-status">
    <span class="mp-status-pill mp-status-pill--muted">
      <i class="fa fa-circle-o"></i> Not Activated
    </span>
    <?php if(!empty($industry_label)): ?>
    <span class="mp-status-pill mp-status-pill--neutral">
      <i class="fa fa-tag"></i> <?= htmlspecialchars($industry_label); ?>
    </span>
    <?php endif; ?>
  </div>
  <h2 class="mp-feature-locked-title"><?= htmlspecialchars($feature_label); ?> isn&rsquo;t turned on for this store</h2>
  <p class="mp-feature-locked-text">
    <?= !empty($description) ? htmlspecialchars($description) : 'This module is part of your business profile but hasn&rsquo;t been flagged as active for the current store. Enable it in Business Profile settings to start using it.'; ?>
  </p>
  <?php if(!empty($feature_key)): ?>
  <div class="mp-feature-locked-meta">
    <span class="mp-meta-key">Feature flag</span>
    <code class="mp-meta-val"><?= htmlspecialchars($feature_key); ?></code>
  </div>
  <?php endif; ?>
  <div class="mp-feature-locked-actions">
    <a href="<?= htmlspecialchars($enable_url); ?>" class="btn-mp-primary">
      <i class="fa fa-toggle-on"></i> Activate in Business Profile
    </a>
    <a href="<?= htmlspecialchars($back_url); ?>" class="btn-mp-ghost">
      <i class="fa fa-arrow-left"></i> Back
    </a>
  </div>
  <div class="mp-feature-locked-note">
    <i class="fa fa-info-circle"></i>
    <span>Only store owners and admins can change business profile flags. If you believe this should already be active, contact your store administrator.</span>
  </div>
</div>

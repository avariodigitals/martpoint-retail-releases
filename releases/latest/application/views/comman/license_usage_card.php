<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$license_usage = function_exists('mp_get_license_usage_summary') ? mp_get_license_usage_summary() : null;
if (!$license_usage) return;
$license_status = $license_usage['status'] ?? 'NOT_ACTIVATED';
$license_colors = array('ACTIVE'=>'#16805a','EXPIRING_SOON'=>'#ad6800','EXPIRED'=>'#b42318','SUSPENDED'=>'#b42318','NOT_ACTIVATED'=>'#667085');
$license_labels = array('ACTIVE'=>'Active','EXPIRING_SOON'=>'Expiring Soon','EXPIRED'=>'Expired','SUSPENDED'=>'Suspended','NOT_ACTIVATED'=>'Not Activated');
$license_color = $license_colors[$license_status] ?? '#667085';
$license_label = $license_labels[$license_status] ?? $license_status;
?>
<style>
.mp-lic-summary{margin:18px 0 22px;padding:18px;border:1px solid var(--mp-border,var(--line,#d8e2dc));border-radius:14px;background:var(--mp-surface,#fff);color:var(--mp-ink,var(--ink,#172033));box-shadow:0 1px 3px rgba(0,0,0,.04)}
.mp-lic-summary *{box-sizing:border-box}.mp-lic-summary-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}.mp-lic-summary h2{margin:0 0 4px;font-size:17px;line-height:1.3;color:inherit}.mp-lic-summary-plan{font-size:13px;color:var(--mp-muted,var(--muted,#667085))}.mp-lic-summary-status{display:flex;align-items:center;gap:9px;flex-wrap:wrap}.mp-lic-summary-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700}.mp-lic-summary-date{font-size:12px;color:var(--mp-muted,var(--muted,#667085))}.mp-lic-summary-detail{font-size:12px;font-weight:700;color:var(--mp-primary,var(--green,#176b45));text-decoration:none}.mp-lic-summary-detail:hover{text-decoration:underline}.mp-lic-summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px}.mp-lic-summary-row{min-width:0}.mp-lic-summary-row-head{display:flex;justify-content:space-between;gap:8px;align-items:baseline;margin-bottom:7px;font-size:12px}.mp-lic-summary-row-head strong{font-weight:700}.mp-lic-summary-row-head span{color:var(--mp-muted,var(--muted,#667085));text-align:right;white-space:nowrap}.mp-lic-summary-track{height:7px;border-radius:99px;background:var(--mp-bg,var(--bg,#edf2ef));overflow:hidden}.mp-lic-summary-fill{height:100%;border-radius:99px}
@media(max-width:600px){.mp-lic-summary{padding:15px;margin:16px 0}.mp-lic-summary-grid{grid-template-columns:1fr 1fr;gap:16px 12px}}@media(max-width:370px){.mp-lic-summary-grid{grid-template-columns:1fr}}
</style>
<section class="mp-lic-summary" aria-label="License and usage">
  <div class="mp-lic-summary-head">
    <div><h2><i class="fa fa-shield" aria-hidden="true"></i> License &amp; Usage</h2><div class="mp-lic-summary-plan">Plan: <?= htmlspecialchars($license_usage['plan_name'] ?: 'Not assigned', ENT_QUOTES, 'UTF-8'); ?></div></div>
    <div class="mp-lic-summary-status">
      <span class="mp-lic-summary-badge" style="background:<?= $license_color; ?>1a;color:<?= $license_color; ?>;"><?= htmlspecialchars($license_label, ENT_QUOTES, 'UTF-8'); ?></span>
      <?php if(!empty($license_usage['has_license']) && !empty($license_usage['end_date'])): ?><span class="mp-lic-summary-date"><?= (int)$license_usage['days_left'] > 0 ? (int)$license_usage['days_left'].' days left' : 'Ended '.show_date($license_usage['end_date']); ?></span><?php endif; ?>
      <?php if(empty($license_usage_mobile) && function_exists('special_access') && special_access()): ?><a class="mp-lic-summary-detail" href="<?= base_url('subscription_license/usage'); ?>">Details <i class="fa fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
    </div>
  </div>
  <div class="mp-lic-summary-grid">
    <?php foreach($license_usage['quotas'] as $quota):
      $used = $quota['unit'] === 'MB' ? number_format((float)$quota['used'], 1).' MB' : number_format((float)$quota['used']);
      $limit = (float)$quota['limit'] > 0 ? ($quota['unit'] === 'MB' ? number_format((float)$quota['limit']).' MB' : number_format((float)$quota['limit'])) : (!empty($license_usage['has_license']) ? 'Unlimited' : '—');
      $pct = max(0, min(100, (float)$quota['pct']));
      $bar_color = $pct >= 100 ? '#b42318' : ($pct >= 80 ? '#ad6800' : 'var(--mp-primary,var(--green,#176b45))');
    ?>
    <div class="mp-lic-summary-row">
      <div class="mp-lic-summary-row-head"><strong><?= htmlspecialchars($quota['label'], ENT_QUOTES, 'UTF-8'); ?></strong><span><?= $used; ?> / <?= $limit; ?></span></div>
      <div class="mp-lic-summary-track" role="progressbar" aria-label="<?= htmlspecialchars($quota['label'], ENT_QUOTES, 'UTF-8'); ?> usage" aria-valuenow="<?= $pct; ?>" aria-valuemin="0" aria-valuemax="100"><div class="mp-lic-summary-fill" style="width:<?= $pct; ?>%;background:<?= $bar_color; ?>;"></div></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

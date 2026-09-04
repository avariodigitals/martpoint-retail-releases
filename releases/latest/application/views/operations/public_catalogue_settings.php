<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$enabled = !empty($settings['enabled']) && $settings['enabled'] == '1';
$show_products = !empty($settings['show_products']) && $settings['show_products'] == '1';
$show_services = !empty($settings['show_services']) && $settings['show_services'] == '1';
$slug = !empty($settings['slug']) ? $settings['slug'] : 'catalogue';
$store_slug = $store_slug ?? '';
?>

<style>
.mp-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-card .mp-card-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--mp-border); }
.mp-card .mp-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--mp-text); }
.mp-card .mp-card-body { padding: 20px; }
.mp-tbl { width: 100%; border-collapse: collapse; }
.mp-tbl th { text-align: left; font-size: 11px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .04em; padding: 10px 16px; border-bottom: 1px solid var(--mp-border); }
.mp-tbl td { padding: 14px 16px; font-size: 13px; color: var(--mp-ink); border-bottom: 1px solid var(--mp-border); }
.mp-tbl tr:last-child td { border-bottom: none; }
.mp-tbl tr:hover td { background: var(--mp-bg); }
.mp-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.mp-pill.ok { background: rgba(5,150,105,.1); color: var(--mp-success); }
.mp-pill.default { background: rgba(120,113,108,.1); color: var(--mp-muted); }
.mp-info-banner { display: flex; gap: 12px; align-items: flex-start; background: rgba(0,87,255,.05); border: 1px solid rgba(0,87,255,.15); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: var(--mp-ink); font-size: 13px; }
.mp-info-banner svg { flex-shrink: 0; margin-top: 2px; color: var(--mp-primary); }
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Configure your public-facing catalogue</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="<?= base_url('store/' . $store_slug . '/catalogue'); ?>" class="mp-qa-btn green" target="_blank" rel="noopener">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      Preview
    </a>
  </div>
</div>

<!-- Settings Card -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Public Catalogue Settings</h3>
  </div>
  <div class="mp-card-body">
    <div class="mp-info-banner">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      <div>Configure your public browsing catalogue. These settings are managed from <strong>Settings &rarr; Business Profile &rarr; Templates &amp; Labels</strong>.</div>
    </div>

    <table class="mp-static-table">
      <thead>
        <tr><th>Setting</th><th>Value</th></tr>
      </thead>
      <tbody>
        <tr><td>Enabled</td><td><span class="mp-pill <?= $enabled ? 'ok' : 'default'; ?>"><?= $enabled ? 'Yes' : 'No'; ?></span></td></tr>
        <tr><td>Slug</td><td><?= htmlspecialchars($slug); ?></td></tr>
        <tr><td>Show Products</td><td><span class="mp-pill <?= $show_products ? 'ok' : 'default'; ?>"><?= $show_products ? 'Yes' : 'No'; ?></span></td></tr>
        <tr><td>Show Services</td><td><span class="mp-pill <?= $show_services ? 'ok' : 'default'; ?>"><?= $show_services ? 'Yes' : 'No'; ?></span></td></tr>
      </tbody>
    </table>

    <div class="mp-form-actions" style="margin-top:20px;">
      <a href="<?= base_url('business_profile'); ?>" class="mp-qa-btn blue">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Edit in Business Profile
      </a>
      <a href="<?= base_url('store/' . $store_slug . '/catalogue'); ?>" class="mp-qa-btn green" target="_blank" rel="noopener">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Preview Public Catalogue
      </a>
    </div>
  </div>
</div>

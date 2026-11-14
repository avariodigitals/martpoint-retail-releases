<!DOCTYPE html>
<html>
<head>
<?php include APPPATH . "views/comman/code_css.php"; ?>
<style>
  .mp-mkt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
    margin-top: 16px;
  }
  .mp-mkt-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 16px;
    text-decoration: none;
    color: #1E293B;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow .15s ease, transform .15s ease;
  }
  .mp-mkt-card:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    transform: translateY(-1px);
    text-decoration: none;
    color: #1E293B;
  }
  .mp-mkt-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
  }
  .mp-mkt-icon.primary { background: #DBEAFE; color: #0057FF; }
  .mp-mkt-icon.blue    { background: #EFF6FF; color: #2563EB; }
  .mp-mkt-icon.green   { background: #D1FAE5; color: #059669; }
  .mp-mkt-icon.orange  { background: #FFEDD5; color: #EA580C; }
  .mp-mkt-icon.red     { background: #FEF2F2; color: #DC2626; }
  .mp-mkt-icon.purple  { background: #F3E8FF; color: #7C3AED; }
  .mp-mkt-icon.teal    { background: #CCFBF1; color: #0F766E; }
  .mp-mkt-icon.yellow  { background: #FFFBEB; color: #D97706; }
  .mp-mkt-text { flex: 1; min-width: 0; }
  .mp-mkt-title { font-weight: 600; font-size: 15px; }
  .mp-mkt-desc { font-size: 13px; color: #64748B; margin-top: 2px; }
  .mp-mkt-arrow { color: #94A3B8; flex-shrink: 0; }
  .mp-mkt-empty {
    padding: 48px 16px;
    text-align: center;
    color: #64748B;
    font-size: 15px;
  }
  @media (max-width: 480px) {
    .mp-mkt-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php include APPPATH . "views/sidebar.php"; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?=$page_title;?> <small>Coupons, Loyalty, Gift Cards &amp; Store Credit</small></h1>
  </section>

  <section class="content">
    <?php include APPPATH . "views/comman/code_flashdata.php"; ?>

    <?php if(!empty($marketing_items)): ?>
      <div class="mp-mkt-grid">
        <?php foreach($marketing_items as $item): ?>
          <a href="<?= base_url($item['url_desktop']); ?>" class="mp-mkt-card">
            <div class="mp-mkt-icon <?= htmlspecialchars($item['color']); ?>">
              <i class="fa <?= htmlspecialchars($item['icon']); ?>"></i>
            </div>
            <div class="mp-mkt-text">
              <div class="mp-mkt-title"><?= htmlspecialchars($item['title']); ?></div>
              <div class="mp-mkt-desc"><?= htmlspecialchars($item['desc']); ?></div>
            </div>
            <i class="fa fa-chevron-right mp-mkt-arrow"></i>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="mp-mkt-empty">
        <i class="fa fa-ticket" style="font-size:28px;display:block;margin-bottom:8px;color:#CBD5E1;"></i>
        No marketing features are available for your account.
      </div>
    <?php endif; ?>
  </section>
</div>

<?php include APPPATH . "views/footer.php"; ?>
</div>

<?php include APPPATH . "views/comman/code_js_sound.php"; ?>
<?php include APPPATH . "views/comman/code_js.php"; ?>
<script>$(".marketing-active-li").addClass("active");</script>
</body>
</html>

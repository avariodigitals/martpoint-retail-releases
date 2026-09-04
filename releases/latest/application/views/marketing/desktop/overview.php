<?php
$this->load->view('marketing/desktop/_styles');
$items = function_exists('marketing_menu_items') ? marketing_menu_items() : [];
?>
<style>
.mp-mkt-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))!important;gap:16px!important}
.mp-mkt-card{display:flex!important;align-items:center!important;gap:14px!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:20px!important;text-decoration:none!important;color:var(--mp-text)!important;box-shadow:var(--mp-shadow-sm)!important;transition:all .15s ease!important}
.mp-mkt-card:hover{box-shadow:var(--mp-shadow)!important;transform:translateY(-2px)!important;text-decoration:none!important;color:var(--mp-text)!important;border-color:var(--mp-primary)!important}
.mp-mkt-icon{width:48px!important;height:48px!important;border-radius:12px!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:20px!important;flex-shrink:0!important}
.mp-mkt-icon.primary{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-mkt-icon.blue{background:rgba(37,99,235,.1)!important;color:#2563EB!important}
.mp-mkt-icon.green{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-mkt-icon.orange{background:rgba(234,88,12,.1)!important;color:#EA580C!important}
.mp-mkt-icon.red{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.mp-mkt-icon.purple{background:rgba(124,58,237,.1)!important;color:#7C3AED!important}
.mp-mkt-icon.teal{background:rgba(13,148,136,.1)!important;color:#0D9488!important}
.mp-mkt-icon.yellow{background:rgba(217,119,6,.1)!important;color:var(--mp-pay)!important}
.mp-mkt-text{flex:1!important;min-width:0!important}
.mp-mkt-title{font-weight:700!important;font-size:15px!important;color:var(--mp-text)!important}
.mp-mkt-desc{font-size:13px!important;color:var(--mp-muted)!important;margin-top:3px!important}
.mp-mkt-arrow{color:var(--mp-muted)!important;flex-shrink:0!important;transition:transform .15s ease!important}
.mp-mkt-card:hover .mp-mkt-arrow{transform:translateX(3px)!important;color:var(--mp-primary)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Marketing'); ?></h2>
    <div class="mp-page-sub">Coupons, Loyalty, Gift Cards &amp; Store Credit</div>
  </div>
</div>

<?php if(!empty($items)): ?>
  <div class="mp-mkt-grid">
    <?php foreach($items as $item): ?>
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
  <div class="mp-card">
    <div class="mp-empty-state">
      <div class="mp-empty-icon"><i class="fa fa-ticket"></i></div>
      <h4>No marketing features available</h4>
      <p>Contact your administrator to enable marketing modules for your account.</p>
    </div>
  </div>
<?php endif; ?>
<script>
$(".marketing-overview-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>

<!-- Help Center — desktop content for mp_layout -->
<style>
  .mp-help-wrap { padding: 24px; }
  .mp-help-wrap h2 { margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #0F172A; }
  .mp-help-wrap .lead { color: #64748B; margin: 0 0 24px; font-size: 15px; }
  .mp-folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-bottom: 24px; }
  .mp-folder-card { display: flex; align-items: center; gap: 16px; padding: 22px; background: #fff; border: 1px solid #E2E8F0; border-radius: 16px; color: #0F172A; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: transform .15s, box-shadow .15s; }
  .mp-folder-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,87,255,0.08); border-color: #0057FF; }
  .mp-folder-card .icon { width: 52px; height: 52px; border-radius: 14px; background: rgba(0,87,255,0.08); color: #0057FF; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
  .mp-folder-card .text { flex: 1; }
  .mp-folder-card .title { font-weight: 700; font-size: 17px; margin-bottom: 4px; }
  .mp-folder-card .desc { font-size: 13px; color: #64748B; line-height: 1.4; }
  .mp-folder-card.locked { opacity: .65; background: #F8FAFC; cursor: default; }
  .mp-folder-card.locked .icon { background: #E2E8F0; color: #57534E; }
  .mp-folder-card.locked .badge { display: inline-block; margin-left: 8px; padding: 2px 8px; border-radius: 999px; background: #E2E8F0; color: #57534E; font-size: 10px; font-weight: 600; text-transform: uppercase; }
  .mp-support-cta { display: inline-flex; align-items: center; gap: 10px; padding: 14px 22px; background: #0057FF; color: #fff; border-radius: 12px; text-decoration: none; font-weight: 600; }
  .mp-support-cta:hover { background: #0044CC; }
</style>

<div class="mp-help-wrap">
  <h2>Help Center</h2>
  <p class="lead">Guides and support resources for MartPoint.</p>

  <div class="mp-folder-grid">
    <a href="<?= base_url('docs/product-design/customer-guide/index.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-users"></i></div>
      <div class="text">
        <div class="title">Customer Guide</div>
        <div class="desc">User manual, sales, purchase, products, services, and reports.</div>
      </div>
    </a>

    <a href="<?= base_url('docs/product-design/customer-guide/cashier-guide.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-calculator"></i></div>
      <div class="text">
        <div class="title">Cashier Guide</div>
        <div class="desc">POS, payment, receipts, holds, returns, and password changes.</div>
      </div>
    </a>

    <div class="mp-folder-card locked">
      <div class="icon"><i class="fa fa-cogs"></i></div>
      <div class="text">
        <div class="title">Technical Guide <span class="badge">Internal</span></div>
        <div class="desc">Installation and technology documentation for internal use only.</div>
      </div>
    </div>
  </div>

  <h3 style="font-size:17px;font-weight:700;color:#0F172A;margin:0 0 14px;">Detailed customer guides</h3>
  <div class="mp-folder-grid" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); margin-bottom: 28px;">
    <a href="<?= base_url('docs/product-design/customer-guide/user-manual.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-book"></i></div>
      <div class="text">
        <div class="title">User Manual</div>
      </div>
    </a>
    <a href="<?= base_url('docs/product-design/customer-guide/sales.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-shopping-cart"></i></div>
      <div class="text">
        <div class="title">Sales</div>
      </div>
    </a>
    <a href="<?= base_url('docs/product-design/customer-guide/purchase.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-cart-arrow-down"></i></div>
      <div class="text">
        <div class="title">Purchase</div>
      </div>
    </a>
    <a href="<?= base_url('docs/product-design/customer-guide/products.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-cubes"></i></div>
      <div class="text">
        <div class="title">Products &amp; Catalogue</div>
      </div>
    </a>
    <a href="<?= base_url('docs/product-design/customer-guide/services.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-scissors"></i></div>
      <div class="text">
        <div class="title">Services</div>
      </div>
    </a>
    <a href="<?= base_url('docs/product-design/customer-guide/reports.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-pie-chart"></i></div>
      <div class="text">
        <div class="title">Reports</div>
      </div>
    </a>
    <a href="<?= base_url('docs/product-design/customer-guide/fashion-scenario.html'); ?>" target="_blank" rel="noopener" class="mp-folder-card">
      <div class="icon"><i class="fa fa-female"></i></div>
      <div class="text">
        <div class="title">Fashion Use Case</div>
      </div>
    </a>
  </div>

  <a href="<?= base_url('dashboard/support'); ?>" class="mp-support-cta"><i class="fa fa-life-ring"></i> Go to Support</a>
</div>

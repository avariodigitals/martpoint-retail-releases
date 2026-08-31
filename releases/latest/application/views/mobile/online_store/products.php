<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Online Products</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .search-bar { display: flex; gap: 8px; margin-bottom: 16px; }
    .search-bar input { flex: 1; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; outline: none; }
    .search-bar button { padding: 0 16px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-size: 16px; }
    .product-card { display: flex; align-items: flex-start; gap: 14px; background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 14px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .product-thumb { width: 52px; height: 52px; border-radius: 12px; object-fit: cover; background: #F1F5F9; flex-shrink: 0; }
    .product-info { flex: 1; min-width: 0; }
    .product-name { font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .product-meta { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .product-price { font-weight: 700; font-size: 14px; margin-top: 4px; }
    .product-badges { display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
    .badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; }
    .badge-online { background: #D1FAE5; color: #065F46; }
    .badge-offline { background: #FEF2F2; color: #991B1B; }
    .badge-featured { background: #EFF6FF; color: var(--mp-primary); }
    .product-actions { display: flex; gap: 8px; margin-top: 10px; align-items: center; flex-wrap: wrap; }
    .product-actions input { width: 90px; padding: 8px 10px; border-radius: 10px; border: 1px solid var(--mp-border); font-size: 13px; }
    .product-actions button { padding: 8px 12px; border-radius: 10px; border: 1px solid var(--mp-border); background: #fff; font-size: 12px; font-weight: 600; cursor: pointer; }
    .product-actions button.online { background: #D1FAE5; color: #065F46; border-color: #D1FAE5; }
    .product-actions button.offline { background: #FEF2F2; color: #991B1B; border-color: #FEF2F2; }
    .product-actions button.featured { background: #EFF6FF; color: var(--mp-primary); border-color: #EFF6FF; }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
    .toast { position: fixed; top: 16px; left: 16px; right: 16px; padding: 14px; border-radius: 12px; text-align: center; color: #fff; font-weight: 600; z-index: 1000; display: none; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Online Products</h1>
        </div>
      </div>

      <form class="search-bar" method="get" action="<?= base_url('mobile/online_store/products'); ?>">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search products...">
        <button type="submit"><i class="fa fa-search"></i></button>
      </form>

      <?php if(!empty($products)): ?>
        <?php foreach($products as $p): ?>
          <div class="product-card" data-id="<?= (int)$p->id; ?>">
            <?php if(!empty($p->item_image) && file_exists($p->item_image)): ?>
              <img src="<?= base_url($p->item_image); ?>" alt="" class="product-thumb">
            <?php else: ?>
              <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:20px;"><i class="fa fa-image"></i></div>
            <?php endif; ?>
            <div class="product-info">
              <div class="product-name"><?= htmlspecialchars($p->item_name); ?></div>
              <div class="product-meta"><?= htmlspecialchars($p->category_name ?: '-'); ?> · Stock: <?= (int)$p->stock; ?></div>
              <div class="product-price">Sales: <?= store_number_format($p->sales_price); ?></div>
              <?php if($can_edit): ?>
                <div class="product-actions">
                  <input type="number" step="0.01" value="<?= $p->online_price > 0 ? $p->online_price : ''; ?>" placeholder="Online price" onchange="updatePrice(<?= (int)$p->id; ?>, this.value)">
                  <button class="<?= $p->publish_online ? 'online' : 'offline'; ?>" onclick="toggleOnline(<?= (int)$p->id; ?>, this)"><?= $p->publish_online ? 'Online' : 'Offline'; ?></button>
                  <button class="<?= $p->is_featured ? 'featured' : ''; ?>" onclick="toggleFeatured(<?= (int)$p->id; ?>, this, <?= $p->is_featured ? 1 : 0; ?>)"><?= $p->is_featured ? 'Featured' : 'Feature'; ?></button>
                </div>
              <?php else: ?>
                <div class="product-badges">
                  <?php if($p->publish_online): ?>
                    <span class="badge badge-online">Online</span>
                  <?php else: ?>
                    <span class="badge badge-offline">Offline</span>
                  <?php endif; ?>
                  <?php if($p->is_featured): ?>
                    <span class="badge badge-featured">Featured</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No products found.</div>
      <?php endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function showToast(msg, isError){
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.background = isError ? '#EF4444' : '#10B981';
      t.style.display = 'block';
      setTimeout(() => t.style.display = 'none', 2500);
    }
    function csrfField(){
      <?php if($this->security): ?>
      return {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
      <?php else: ?>
      return {};
      <?php endif; ?>
    }
    function updatePrice(id, price){
      const fd = new FormData();
      fd.append('product_id', id);
      fd.append('online_price', price);
      for(const [k,v] of Object.entries(csrfField())) fd.append(k, v);
      fetch('<?= base_url('online_store/update_online_price'); ?>', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => showToast(d.message, d.status !== 'success'))
        .catch(() => showToast('Price update failed', true));
    }
    function toggleOnline(id, btn){
      btn.disabled = true;
      const fd = new FormData();
      fd.append('product_id', id);
      for(const [k,v] of Object.entries(csrfField())) fd.append(k, v);
      fetch('<?= base_url('online_store/toggle_product_online'); ?>', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
          if(d.status === 'success'){
            btn.textContent = d.publish_online ? 'Online' : 'Offline';
            btn.className = d.publish_online ? 'online' : 'offline';
            showToast(d.publish_online ? 'Product is now online' : 'Product removed from online store');
          } else {
            showToast(d.message, true);
          }
          btn.disabled = false;
        })
        .catch(() => { showToast('Toggle failed', true); btn.disabled = false; });
    }
    function toggleFeatured(id, btn, current){
      // Not available without a dedicated endpoint; reload to show feedback
      showToast('Featured toggle needs backend support. Price & online status saved.', true);
    }
  </script>
</body>
</html>
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
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-indigo: #4F46E5; --safe-bottom: env(safe-area-inset-bottom, 0px); }
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
    .filter-form { margin-bottom: 12px; }
    .filter-bar { display: flex; flex-direction: column; gap: 10px; }
    .search-row { display: flex; gap: 8px; }
    .search-row input { flex: 1; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; outline: none; }
    .search-row button { padding: 0 16px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-size: 16px; }
    .sync-btn { width: 100%; padding: 12px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 12px; }
    .sync-btn:active { background: var(--mp-primary-dark); }
    .sync-btn:disabled { opacity: .6; }
    /* mp-select — custom inline select (no native dropdown shoot-out) */
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-trigger .chev { color: var(--mp-muted); margin-left: 8px; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .product-card { display: flex; align-items: flex-start; gap: 14px; background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 14px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); transition: border-color .15s; }
    .product-card.selected { border-color: var(--mp-primary); background: #F0F7FF; }
    .product-check { width: 22px; height: 22px; flex-shrink: 0; margin-top: 2px; cursor: pointer; -webkit-appearance: none; appearance: none; border: 2px solid var(--mp-border); border-radius: 6px; background: #fff; position: relative; transition: all .15s; }
    .product-check:checked { background: var(--mp-primary); border-color: var(--mp-primary); }
    .product-check:checked::after { content: '\f00c'; font-family: FontAwesome; color: #fff; font-size: 12px; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); }
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
    .badge-new { background: #E0E7FF; color: var(--mp-indigo); }
    .product-actions { display: flex; gap: 8px; margin-top: 10px; align-items: center; flex-wrap: wrap; }
    .product-actions input { width: 90px; padding: 8px 10px; border-radius: 10px; border: 1px solid var(--mp-border); font-size: 13px; }
    .product-actions button { padding: 8px 12px; border-radius: 10px; border: 1px solid var(--mp-border); background: #fff; font-size: 12px; font-weight: 600; cursor: pointer; }
    .product-actions button.online { background: #D1FAE5; color: #065F46; border-color: #D1FAE5; }
    .product-actions button.offline { background: #FEF2F2; color: #991B1B; border-color: #FEF2F2; }
    .product-actions button.featured { background: #EFF6FF; color: var(--mp-primary); border-color: #EFF6FF; }
    .product-actions button.new-arrival { background: #E0E7FF; color: var(--mp-indigo); border-color: #E0E7FF; }
    .product-actions button.new-arrival.active { background: var(--mp-indigo); color: #fff; border-color: var(--mp-indigo); }
    .product-actions button.featured.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
    .toast { position: fixed; top: 16px; left: 16px; right: 16px; padding: 14px; border-radius: 12px; text-align: center; color: #fff; font-weight: 600; z-index: 1000; display: none; }
    /* Batch action bar */
    .batch-bar { position: fixed; bottom: 0; left: 0; right: 0; z-index: 500; background: #fff; border-top: 1px solid var(--mp-border); box-shadow: 0 -4px 12px rgba(0,0,0,0.1); transform: translateY(100%); transition: transform .25s ease; padding-bottom: var(--safe-bottom); }
    .batch-bar.show { transform: translateY(0); }
    .batch-bar-inner { max-width: 430px; margin: 0 auto; padding: 12px 16px; }
    .batch-bar-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .batch-bar-count { font-size: 14px; font-weight: 700; color: var(--mp-primary); }
    .batch-bar-clear { border: none; background: transparent; color: var(--mp-muted); font-size: 13px; cursor: pointer; padding: 4px 8px; }
    .batch-bar-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .batch-btn { padding: 10px 6px; border-radius: 10px; border: 1px solid var(--mp-border); background: #fff; font-size: 11px; font-weight: 600; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; color: var(--mp-ink); }
    .batch-btn i { font-size: 16px; }
    .batch-btn:active { transform: scale(.96); }
    .batch-btn.green { color: #065F46; border-color: #10B981; }
    .batch-btn.red { color: #991B1B; border-color: #EF4444; }
    .batch-btn.indigo { color: #4338CA; border-color: #6366F1; }
    .batch-btn.blue { color: var(--mp-primary); border-color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } .batch-bar-inner { max-width: 100%; } .batch-bar-actions { grid-template-columns: repeat(6, 1fr); } }
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

      <form class="filter-form" id="filterForm" method="get" action="<?= base_url('mobile/online_store/products'); ?>">
        <div class="filter-bar">
          <div class="search-row">
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search products...">
            <button type="submit"><i class="fa fa-search"></i></button>
          </div>
          <?php if(!empty($categories)): ?>
          <select class="mp-select" id="categoryFilter" name="category">
            <option value="0" <?= empty($category_id) ? 'selected' : ''; ?>>All Categories</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= (int)$cat->id; ?>" <?= ($category_id == (int)$cat->id) ? 'selected' : ''; ?>><?= htmlspecialchars($cat->category_name); ?></option>
            <?php endforeach; ?>
          </select>
          <?php endif; ?>
        </div>
      </form>

      <?php if($can_edit): ?>
      <button class="sync-btn" id="syncBtn" onclick="syncAllOnline()">
        <i class="fa fa-refresh"></i> Sync All Products Online
      </button>
      <?php endif; ?>

      <?php if(!empty($products)): ?>
        <?php if($can_edit): ?>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding:8px 12px;background:#fff;border-radius:12px;border:1px solid var(--mp-border);">
          <input type="checkbox" class="product-check" id="selectAllMobile" style="margin:0;">
          <label for="selectAllMobile" style="font-size:13px;font-weight:600;color:var(--mp-muted);cursor:pointer;">Select All</label>
        </div>
        <?php endif; ?>
        <?php foreach($products as $p): ?>
          <div class="product-card" data-id="<?= (int)$p->id; ?>">
            <?php if($can_edit): ?>
            <input type="checkbox" class="product-check row-check" value="<?= (int)$p->id; ?>" onchange="onRowCheck(this)">
            <?php endif; ?>
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
                  <button class="new-arrival <?= $p->is_new_arrival ? 'active' : ''; ?>" onclick="toggleNewArrival(<?= (int)$p->id; ?>, this)"><i class="fa fa-star-o"></i> <?= $p->is_new_arrival ? 'New' : 'New'; ?></button>
                  <button class="featured <?= $p->is_featured ? 'active' : ''; ?>" onclick="toggleFeatured(<?= (int)$p->id; ?>, this)"><i class="fa fa-thumbs-up"></i> <?= $p->is_featured ? 'Featured' : 'Feature'; ?></button>
                </div>
              <?php else: ?>
                <div class="product-badges">
                  <?php if($p->publish_online): ?>
                    <span class="badge badge-online">Online</span>
                  <?php else: ?>
                    <span class="badge badge-offline">Offline</span>
                  <?php endif; ?>
                  <?php if($p->is_new_arrival): ?>
                    <span class="badge badge-new">New Arrival</span>
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

  <?php if($can_edit): ?>
  <!-- Batch action bar -->
  <div class="batch-bar" id="batchBar">
    <div class="batch-bar-inner">
      <div class="batch-bar-head">
        <span class="batch-bar-count"><i class="fa fa-check-square"></i> <span id="batchCount">0</span> selected</span>
        <button class="batch-bar-clear" onclick="clearSelection()"><i class="fa fa-times"></i> Clear</button>
      </div>
      <div class="batch-bar-actions">
        <button class="batch-btn green" onclick="batchAction('publish')"><i class="fa fa-globe"></i> Publish</button>
        <button class="batch-btn red" onclick="batchAction('unpublish')"><i class="fa fa-ban"></i> Unpublish</button>
        <button class="batch-btn indigo" onclick="batchAction('mark_new')"><i class="fa fa-star"></i> New</button>
        <button class="batch-btn indigo" onclick="batchAction('unmark_new')"><i class="fa fa-star-o"></i> Un-New</button>
        <button class="batch-btn blue" onclick="batchAction('mark_featured')"><i class="fa fa-thumbs-up"></i> Feature</button>
        <button class="batch-btn blue" onclick="batchAction('unmark_featured')"><i class="fa fa-thumbs-o-down"></i> Un-Feature</button>
      </div>
    </div>
  </div>
  <?php endif; ?>
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
    function toggleNewArrival(id, btn){
      btn.disabled = true;
      const fd = new FormData();
      fd.append('product_id', id);
      for(const [k,v] of Object.entries(csrfField())) fd.append(k, v);
      fetch('<?= base_url('online_store/toggle_new_arrival'); ?>', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
          if(d.status === 'success'){
            btn.classList.toggle('active', d.is_new_arrival == 1);
            showToast(d.is_new_arrival ? 'Marked as New Arrival' : 'Removed from New Arrivals');
          } else {
            showToast(d.message, true);
          }
          btn.disabled = false;
        })
        .catch(() => { showToast('Toggle failed', true); btn.disabled = false; });
    }
    function toggleFeatured(id, btn){
      btn.disabled = true;
      const fd = new FormData();
      fd.append('product_id', id);
      for(const [k,v] of Object.entries(csrfField())) fd.append(k, v);
      fetch('<?= base_url('online_store/toggle_featured'); ?>', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
          if(d.status === 'success'){
            btn.classList.toggle('active', d.is_featured == 1);
            showToast(d.is_featured ? 'Marked as Featured' : 'Removed from Featured');
          } else {
            showToast(d.message, true);
          }
          btn.disabled = false;
        })
        .catch(() => { showToast('Toggle failed', true); btn.disabled = false; });
    }
    function syncAllOnline(){
      if(!confirm('This will publish ALL eligible offline products to your online store (respecting your plan quota). Continue?')) return;
      var btn = document.getElementById('syncBtn');
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Syncing...';
      var fd = new FormData();
      fd.append('category_id', <?= (int)($category_id ?? 0); ?>);
      for(const [k,v] of Object.entries(csrfField())) fd.append(k, v);
      fetch('<?= base_url('online_store/sync_all_online'); ?>', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa fa-refresh"></i> Sync All Products Online';
          if(d.status === 'success'){
            showToast(d.message, false);
            setTimeout(() => window.location.reload(), 2000);
          } else {
            showToast(d.message, true);
          }
        })
        .catch(() => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa fa-refresh"></i> Sync All Products Online';
          showToast('Sync failed', true);
        });
    }

    // === Batch selection ===
    function getSelectedIds(){
      return Array.from(document.querySelectorAll('.row-check:checked')).map(function(cb){ return cb.value; });
    }
    function updateBatchBar(){
      var ids = getSelectedIds();
      document.getElementById('batchCount').textContent = ids.length;
      document.getElementById('batchBar').classList.toggle('show', ids.length > 0);
      // Update select-all state
      var allBoxes = document.querySelectorAll('.row-check');
      var allChecked = allBoxes.length > 0 && Array.from(allBoxes).every(function(cb){ return cb.checked; });
      var selAll = document.getElementById('selectAllMobile');
      if(selAll) selAll.checked = allChecked;
    }
    function clearSelection(){
      document.querySelectorAll('.row-check').forEach(function(cb){ cb.checked = false; cb.closest('.product-card').classList.remove('selected'); });
      var selAll = document.getElementById('selectAllMobile');
      if(selAll) selAll.checked = false;
      updateBatchBar();
    }
    function onRowCheck(cb){
      cb.closest('.product-card').classList.toggle('selected', cb.checked);
      updateBatchBar();
    }
    function batchAction(action){
      var ids = getSelectedIds();
      if(ids.length === 0){ showToast('No products selected', true); return; }
      var labels = {
        'publish':'Publish '+ids.length+' products online?',
        'unpublish':'Unpublish '+ids.length+' products?',
        'mark_new':'Mark '+ids.length+' as New Arrival?',
        'unmark_new':'Remove '+ids.length+' from New Arrivals?',
        'mark_featured':'Mark '+ids.length+' as Featured?',
        'unmark_featured':'Remove '+ids.length+' from Featured?'
      };
      if(!confirm(labels[action] || 'Apply to '+ids.length+' products?')) return;
      var fd = new FormData();
      ids.forEach(function(id){ fd.append('product_ids[]', id); });
      fd.append('action', action);
      for(const [k,v] of Object.entries(csrfField())) fd.append(k, v);
      fetch('<?= base_url('online_store/batch_update'); ?>', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
          if(d.status === 'success'){
            showToast(d.message, false);
            clearSelection();
            setTimeout(() => window.location.reload(), 2000);
          } else {
            showToast(d.message, true);
          }
        })
        .catch(() => showToast('Batch update failed', true));
    }
    // Select all
    document.addEventListener('DOMContentLoaded', function(){
      var selAll = document.getElementById('selectAllMobile');
      if(selAll){
        selAll.addEventListener('change', function(){
          var checked = this.checked;
          document.querySelectorAll('.row-check').forEach(function(cb){
            cb.checked = checked;
            cb.closest('.product-card').classList.toggle('selected', checked);
          });
          updateBatchBar();
        });
      }
    });

    // Custom in-app select (no native dropdown shoot-out)
    (function(){
      var form = document.getElementById('filterForm');
      var selects = document.querySelectorAll('select.mp-select');
      selects.forEach(function(sel){
        if(sel.dataset.mpInit) return;
        sel.dataset.mpInit = '1';
        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);

        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        var chev = document.createElement('i');
        chev.className = 'fa fa-chevron-down chev';
        wrap.appendChild(trigger);
        trigger.appendChild(chev);

        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);

        function renderOptions(){
          list.innerHTML = '';
          Array.from(sel.options).forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-option';
            div.textContent = opt.textContent;
            if(idx === sel.selectedIndex) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              updateTrigger();
              list.classList.remove('open');
              form.submit();
            });
            list.appendChild(div);
          });
        }

        function updateTrigger(){
          var opt = sel.options[sel.selectedIndex];
          var label = document.createElement('span');
          label.textContent = opt ? opt.textContent : 'Select';
          trigger.innerHTML = '';
          trigger.appendChild(label);
          trigger.appendChild(chev);
          if(opt && opt.value && opt.value !== '0'){
            trigger.classList.remove('placeholder');
          } else {
            trigger.classList.add('placeholder');
          }
          renderOptions();
        }

        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); });
          list.classList.toggle('open');
        });

        updateTrigger();
      });

      document.addEventListener('click', function(){
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); });
      });
    })();
  </script>
</body>
</html>

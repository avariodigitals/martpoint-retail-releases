<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Purchase History</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .topbar .add { background: var(--mp-primary); color: #fff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; }
    .search-bar { display: flex; gap: 8px; margin-bottom: 12px; }
    .search-bar input { flex: 1; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; }
    .purchase-card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .purchase-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .purchase-meta { font-size: 13px; color: var(--mp-muted); margin: 3px 0; }
    .purchase-meta i { margin-right: 4px; color: var(--mp-primary); width: 16px; }
    .purchase-actions { display: flex; gap: 8px; margin-top: 12px; }
    .action { flex: 1; text-align: center; padding: 9px 0; border-radius: 10px; font-size: 12px; font-weight: 600; text-decoration: none; color: #fff; }
    .action.view { background: #E0E7FF; color: var(--mp-primary); }
    .action.print { background: #FEF3C7; color: #B45309; }
    .action.pdf { background: #FEE2E2; color: #B91C1B; }
    .action.delete { background: #FEF2F2; color: #B91C1B; }
    .empty-state { text-align: center; padding: 32px; color: var(--mp-muted); }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.draft { background: #F1F5F9; color: #334155; }
    .badge.ordered { background: #DBEAFE; color: #1E40AF; }
    .badge.partial { background: #FEF3C7; color: #B45309; }
    .badge.received { background: #D1FAE5; color: #065F46; }
    .badge.unpaid { background: #FEE2E2; color: #B91C1B; }
    .badge.partial-paid { background: #FEF3C7; color: #B45309; }
    .badge.paid { background: #D1FAE5; color: #065F46; }
    .row-between { display: flex; justify-content: space-between; align-items: flex-start; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; }
    .filter-group label { font-size: 12px; color: var(--mp-muted); font-weight: 600; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px;
      background: #fff; font-size: 15px; cursor: pointer;
    }
    .mp-select-options {
      display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200;
      background: #fff; border: 1px solid var(--mp-border); border-radius: 12px;
      max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Purchase History</h1>
        </div>
        <?php if(permissions('purchase_add')): ?>
          <a href="<?= base_url('mobile/purchase_form'); ?>" class="add"><i class="fa fa-plus"></i></a>
        <?php endif; ?>
      </div>

      <div class="search-bar">
        <input type="text" id="purchaseSearch" placeholder="Search code, supplier or reference..." oninput="filterPurchases()">
      </div>

      <div class="filter-row">
        <div class="filter-group">
          <label for="supplierFilter">Supplier</label>
          <select class="mp-select" id="supplierFilter" onchange="filterPurchases()">
            <option value="">All Suppliers</option>
            <?= $suppliers; ?>
          </select>
        </div>
        <div class="filter-group">
          <label for="statusFilter">Status</label>
          <select class="mp-select" id="statusFilter" onchange="filterPurchases()">
            <option value="">All Status</option>
            <?php foreach($statuses as $s): ?>
              <option value="<?= htmlspecialchars($s); ?>"><?= htmlspecialchars($s); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div id="purchaseList">
        <?php if(!empty($records)): ?>
          <?php foreach($records as $r):
            $status_map = ['received' => 'received', 'partially received' => 'partial', 'ordered' => 'ordered'];
            $status_class = $status_map[strtolower($r->purchase_status ?? 'Draft')] ?? 'draft';
            $payment_map = ['paid' => 'paid', 'partial' => 'partial-paid'];
            $payment_class = $payment_map[strtolower($r->payment_status ?? 'Unpaid')] ?? 'unpaid';
          ?>
            <div class="purchase-card" data-search="<?= strtolower(($r->purchase_code ?? '').' '.($r->supplier_name ?? '').' '.($r->reference_no ?? '')); ?>" data-supplier="<?= $r->supplier_id ?? ''; ?>" data-status="<?= htmlspecialchars($r->purchase_status ?? ''); ?>">
              <div class="row-between">
                <div>
                  <div class="purchase-title"><?= $r->purchase_code; ?></div>
                  <div class="purchase-meta"><i class="fa fa-user"></i> <?= $r->supplier_name ?: 'Unknown supplier'; ?></div>
                  <div class="purchase-meta"><i class="fa fa-calendar"></i> <?= show_date($r->purchase_date); ?></div>
                  <?php if(!empty($r->reference_no)): ?>
                    <div class="purchase-meta"><i class="fa fa-hashtag"></i> <?= $r->reference_no; ?></div>
                  <?php endif; ?>
                </div>
                <div style="text-align:right;">
                  <div class="badge <?= $status_class; ?>" style="margin-bottom:4px;"><?= $r->purchase_status; ?></div>
                  <div class="badge <?= $payment_class; ?>"><?= $r->payment_status; ?></div>
                </div>
              </div>
              <div class="purchase-meta" style="margin-top:8px; font-weight:600; color:var(--mp-text);">
                <i class="fa fa-money"></i> Total: <?= store_number_format($r->grand_total); ?> &nbsp;|&nbsp; Paid: <?= store_number_format($r->paid_amount); ?>
              </div>
              <div class="purchase-actions">
                <a href="<?= base_url('mobile/purchase_view/'.$r->id); ?>" class="action view"><i class="fa fa-eye"></i> View</a>
                <?php if(permissions('purchase_edit')): ?>
                  <a href="<?= base_url('mobile/purchase_form/'.$r->id); ?>" class="action print"><i class="fa fa-edit"></i> Edit</a>
                <?php endif; ?>
                <?php if(permissions('purchase_delete')): ?>
                  <a href="javascript:void(0)" class="action delete" onclick="deletePurchase(<?= (int)$r->id; ?>)"><i class="fa fa-trash"></i> Delete</a>
                <?php endif; ?>
                <a href="<?= base_url('purchase/print_invoice/'.$r->id); ?>" target="_blank" class="action pdf"><i class="fa fa-print"></i> Print</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-cart-arrow-down" style="font-size:48px; margin-bottom:12px;"></i>
            <div>No purchases found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'purchase']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function filterPurchases(){
      var q = document.getElementById('purchaseSearch').value.toLowerCase();
      var supplier = document.getElementById('supplierFilter').value;
      var status = document.getElementById('statusFilter').value;
      document.querySelectorAll('.purchase-card').forEach(function(card){
        var matchesSearch = (card.getAttribute('data-search') || '').indexOf(q) >= 0;
        var matchesSupplier = !supplier || (card.getAttribute('data-supplier') || '') === supplier;
        var matchesStatus = !status || (card.getAttribute('data-status') || '') === status;
        card.style.display = (matchesSearch && matchesSupplier && matchesStatus) ? '' : 'none';
      });
    }

    // Custom select setup for filters
    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      var label = document.createElement('span');
      var icon = document.createElement('i'); icon.className = 'fa fa-chevron-down'; icon.style.fontSize = '12px';
      trigger.appendChild(label); trigger.appendChild(icon);
      wrap.appendChild(trigger); wrap.appendChild(opts);
      function setLabel(){
        var txt = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'Select';
        label.textContent = txt;
      }
      Array.from(sel.options).forEach(function(opt, idx){
        var d = document.createElement('div'); d.className = 'mp-option'; d.textContent = opt.text;
        if(idx === sel.selectedIndex) d.classList.add('active');
        d.addEventListener('click', function(e){
          e.stopPropagation();
          sel.selectedIndex = idx;
          setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active');
          opts.classList.remove('open');
          sel.dispatchEvent(new Event('change'));
        });
        opts.appendChild(d);
      });
      setLabel();
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); });
        opts.classList.toggle('open');
      });
    });
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    function deletePurchase(id){
      mpConfirm('Delete this purchase?', function(){
        var formData = new FormData();
        formData.append('q_id', id);
        formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        fetch('<?= base_url('purchase/delete_purchase'); ?>', { method: 'POST', body: formData })
        .then(function(res){ return res.text(); })
        .then(function(text){
          if(text.trim() === 'success'){
            mpSuccess('Purchase deleted.');
            setTimeout(function(){ window.location.reload(); }, 600);
          } else {
            mpError(text.replace(/<[^>]*>/g, '').trim() || 'Delete failed.');
          }
        })
        .catch(function(){ mpError('Network error.'); });
      }, null, {danger: true});
    }
  </script>
</body>
</html>

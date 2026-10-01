<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .section h3 { font-size: 13px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .field:last-child { margin-bottom: 0; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field input[type=text], .field input[type=number], .field input[type=date], .field textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 15px; background: #fff; color: var(--mp-ink); }
    .field textarea { min-height: 70px; resize: vertical; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; cursor: pointer; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .spec-row { display: grid; grid-template-columns: 1fr 1fr 34px; gap: 8px; margin-bottom: 8px; align-items: center; }
    .spec-row input { padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-family: inherit; font-size: 13px; }
    .spec-row .rm { width: 34px; height: 38px; border: none; border-radius: 10px; background: #FEE2E2; color: var(--mp-danger); cursor: pointer; }
    .add-btn { width: 100%; padding: 10px; border-radius: 10px; border: 1px dashed var(--mp-border); background: var(--mp-bg); color: var(--mp-ink); font-size: 13px; font-weight: 600; cursor: pointer; }
    .check { display: flex; align-items: center; gap: 10px; font-size: 14px; padding: 4px 0; }
    .check input { width: 18px; height: 18px; }
    .save-btn { width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 4px; }
    .save-btn:disabled { opacity: 0.6; }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    .hint { font-size: 12px; color: var(--mp-muted); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $edit_order ? base_url('mobile/nylon_order/'.$edit_order->id) : base_url('mobile/nylon_orders'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <form id="oForm" method="post" action="<?= base_url('nylon/order_save'); ?>" onsubmit="return saveOrder(event);" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <?php if($edit_order): ?><input type="hidden" name="id" value="<?= (int)$edit_order->id; ?>"><?php endif; ?>

        <div class="section">
          <h3>Client &amp; Product</h3>
          <div class="field">
            <label><?= htmlspecialchars(mp_label('customer')); ?> *</label>
            <select name="customer_id" class="mp-select" required <?= $edit_order ? 'disabled' : ''; ?>>
              <option value="">Select <?= htmlspecialchars(strtolower(mp_label('customer'))); ?></option>
              <?php foreach($customers as $c): ?>
              <option value="<?= (int)$c->id; ?>" <?= ($edit_order && $edit_order->customer_id == $c->id) || (!$edit_order && $preselect_customer_id == $c->id) ? 'selected' : ''; ?>><?= htmlspecialchars($c->customer_name); ?><?= !empty($c->mobile) ? ' — '.$c->mobile : ''; ?></option>
              <?php endforeach; ?>
            </select>
            <?php if($edit_order): ?><input type="hidden" name="customer_id" value="<?= (int)$edit_order->customer_id; ?>"><?php endif; ?>
          </div>
          <div class="field">
            <label>Product / Film *</label>
            <select name="item_id" id="item_id" class="mp-select" required <?= $edit_order ? 'disabled' : ''; ?>>
              <option value="">Select product</option>
              <?php foreach($products as $p): ?>
              <option value="<?= (int)$p->id; ?>" <?= ($edit_order && $edit_order->item_id == $p->id) ? 'selected' : ''; ?>><?= htmlspecialchars($p->item_name); ?><?= $p->item_class === 'film_roll' ? ' (roll)' : ''; ?></option>
              <?php endforeach; ?>
            </select>
            <?php if($edit_order): ?><input type="hidden" name="item_id" value="<?= (int)$edit_order->item_id; ?>"><?php endif; ?>
            <?php if(empty($products)): ?><div class="hint">No finished goods or film rolls configured yet — add them under Nylon → Products on desktop.</div><?php endif; ?>
          </div>
        </div>

        <div class="section">
          <h3>Specifications</h3>
          <div id="specRows">
            <?php
              $specs = $edit_order ? (is_array($edit_order->specs) ? $edit_order->specs : []) : [];
              foreach($specs as $k => $v):
            ?>
            <div class="spec-row">
              <input type="text" name="spec_label[]" placeholder="Label" value="<?= htmlspecialchars($k); ?>">
              <input type="text" name="spec_value[]" placeholder="Value" value="<?= htmlspecialchars($v); ?>">
              <button type="button" class="rm" onclick="this.parentNode.remove()"><i class="fa fa-times"></i></button>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-btn" id="addSpec"><i class="fa fa-plus"></i> Add Specification</button>
          <div class="field" style="margin-top:12px;">
            <label>Design Reference</label>
            <input type="text" name="design_ref" value="<?= $edit_order ? htmlspecialchars($edit_order->design_ref ?? '') : ''; ?>" placeholder="e.g. SR-VEST-V3">
          </div>
          <label class="check"><input type="checkbox" name="artwork_required" value="1" <?= ($edit_order && $edit_order->artwork_required) ? 'checked' : ''; ?>> Artwork approval required before printing</label>
        </div>

        <div class="section">
          <h3>Quantity &amp; Pricing</h3>
          <div class="grid2">
            <div class="field"><label>Order Qty</label><input type="number" step="any" min="0" name="order_qty" value="<?= $edit_order ? htmlspecialchars($edit_order->order_qty) : ''; ?>"></div>
            <div class="field">
              <label>Selling Unit</label>
              <select name="order_unit_id" class="mp-select">
                <option value="">Base unit</option>
                <?php foreach($units as $u): ?>
                <option value="<?= (int)$u->id; ?>" <?= ($edit_order && $edit_order->order_unit_id == $u->id) ? 'selected' : ''; ?>><?= htmlspecialchars($u->unit_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field"><label>Quoted Price</label><input type="number" step="any" min="0" name="quoted_price" value="<?= $edit_order ? htmlspecialchars($edit_order->quoted_price) : '0'; ?>"></div>
            <div class="field"><label>Total Amount</label><input type="number" step="any" min="0" name="total_amount" value="<?= $edit_order ? htmlspecialchars($edit_order->total_amount) : '0'; ?>"></div>
            <div class="field"><label>Deposit Required</label><input type="number" step="any" min="0" name="deposit_amount" value="<?= $edit_order ? htmlspecialchars($edit_order->deposit_amount) : '0'; ?>"></div>
            <div class="field"><label>Deposit Paid</label><input type="number" step="any" min="0" name="deposit_paid" value="<?= $edit_order ? htmlspecialchars($edit_order->deposit_paid) : '0'; ?>"></div>
            <div class="field"><label>Order Date *</label><input type="date" name="order_date" value="<?= $edit_order ? htmlspecialchars($edit_order->order_date) : date('Y-m-d'); ?>" required></div>
            <div class="field"><label>Due Date</label><input type="date" name="due_date" value="<?= ($edit_order && $edit_order->due_date) ? htmlspecialchars($edit_order->due_date) : ''; ?>"></div>
          </div>
          <?php if($edit_order): ?>
          <div class="field" style="margin-top:14px;">
            <label>Status</label>
            <select name="status" class="mp-select">
              <?php foreach($workflow as $s): ?>
              <option value="<?= $s; ?>" <?= ($edit_order->status === $s) ? 'selected' : ''; ?>><?= htmlspecialchars(Custom_orders_model::status_label($s)); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
        </div>

        <div class="section">
          <h3>Notes</h3>
          <div class="field"><textarea name="notes" placeholder="Delivery notes, colour references, packing instructions…"><?= $edit_order ? htmlspecialchars($edit_order->notes ?? '') : ''; ?></textarea></div>
        </div>

        <button type="submit" class="save-btn" id="saveBtn"><i class="fa fa-check"></i> <?= $edit_order ? 'Update Order' : 'Save Order'; ?></button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var item_specs = <?= json_encode($item_specs); ?>;
    var EDIT_ID = <?= $edit_order ? (int)$edit_order->id : 'null'; ?>;

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 3500); }

    /* ---------- mp-select ---------- */
    function buildSelect(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      var label = document.createElement('span');
      var icon = document.createElement('i'); icon.className = 'fa fa-chevron-down'; icon.style.fontSize = '12px';
      trigger.appendChild(label); trigger.appendChild(icon);
      wrap.appendChild(trigger); wrap.appendChild(opts);
      function setLabel(){ label.textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'Select'; }
      Array.from(sel.options).forEach(function(opt, idx){
        var d = document.createElement('div'); d.className = 'mp-option'; d.textContent = opt.text;
        if(idx === sel.selectedIndex) d.classList.add('active');
        d.addEventListener('click', function(e){
          e.stopPropagation();
          sel.selectedIndex = idx; setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active'); opts.classList.remove('open');
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
    }
    document.querySelectorAll('select.mp-select').forEach(buildSelect);
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    /* ---------- Spec rows ---------- */
    function addSpecRow(lbl, val){
      var row = document.createElement('div'); row.className = 'spec-row';
      row.innerHTML = '<input type="text" name="spec_label[]" placeholder="Label">' +
                      '<input type="text" name="spec_value[]" placeholder="Value">' +
                      '<button type="button" class="rm" onclick="this.parentNode.remove()"><i class="fa fa-times"></i></button>';
      if(lbl) row.children[0].value = lbl;
      if(val) row.children[1].value = val;
      document.getElementById('specRows').appendChild(row);
    }
    document.getElementById('addSpec').addEventListener('click', function(){ addSpecRow(); });

    /* Prefill spec rows from the product's saved spec card */
    var specsTouched = <?= empty($specs) ? 'false' : 'true'; ?>;
    var itemSel = document.getElementById('item_id');
    if(itemSel){
      itemSel.addEventListener('change', function(){
        if(specsTouched) return;
        var f = item_specs[this.value] || {};
        var keys = Object.keys(f);
        if(!keys.length) return;
        document.getElementById('specRows').innerHTML = '';
        keys.forEach(function(k){ addSpecRow(k, f[k]); });
        specsTouched = true;
      });
    }

    function saveOrder(e){
      e.preventDefault();
      var btn = document.getElementById('saveBtn');
      btn.disabled = true;
      var form = document.getElementById('oForm');
      mpFetchJson(form.action, { method: 'POST', body: new FormData(form) })
        .then(function(d){
          if(d && d.csrf_hash){
            var ci = form.querySelector('input[name="<?= $this->security->get_csrf_token_name(); ?>"]');
            if(ci) ci.value = d.csrf_hash;
          }
          if(d && d.success){
            showToast(d.message || 'Saved.');
            setTimeout(function(){ window.location.href = base_url + 'mobile/nylon_order/' + (d.id || EDIT_ID); }, 600);
          } else {
            showToast(d && d.message ? d.message : 'Could not save this order. Please check the details and try again.');
            btn.disabled = false;
          }
        })
        .catch(function(err){ showToast(mpErrorText(err)); btn.disabled = false; });
      return false;
    }
  </script>
</body>
</html>

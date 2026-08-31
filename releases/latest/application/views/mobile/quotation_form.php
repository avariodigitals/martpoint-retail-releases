<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — New Quotation</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 140px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .section-title { font-size: 15px; font-weight: 700; margin: 20px 0 10px; color: var(--mp-text); }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-text); }
    label .req { color: var(--mp-danger); }
    input[type="text"], input[type="number"], input[type="date"], input[type="search"], textarea, select.mp-select {
      width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none; appearance: none; -webkit-appearance: none;
    }
    input:focus, textarea:focus { border-color: var(--mp-primary); }
    textarea { min-height: 80px; resize: vertical; }
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
    .totals { background: #E0E7FF; border-radius: 12px; padding: 14px; margin: 16px 0; }
    .totals .value { font-size: 22px; font-weight: 700; color: var(--mp-primary); }
    .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn:active { background: var(--mp-primary-dark); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (max-width: 360px) { .form-row { grid-template-columns: 1fr; } }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/quotations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <form id="quote-form" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" name="command" value="<?= $command; ?>">
        <input type="hidden" name="quotation_status" value="Quotation">
        <input type="hidden" name="rowcount" value="<?= $rowcount; ?>">
        <input type="hidden" name="quotation_id" value="<?= $quotation_id; ?>">

        <div class="card">
          <div class="form-group">
            <label>Customer <span class="req">*</span></label>
            <select class="mp-select" name="customer_id" id="customer_id" required>
              <option value="">Select customer</option>
              <?php foreach($customers as $c): ?>
                <option value="<?= $c->id; ?>" <?= ($c->id == $customer_id) ? 'selected' : ''; ?>><?= $c->customer_name; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Quotation Date <span class="req">*</span></label>
              <input type="date" name="quotation_date" value="<?= $quotation_date; ?>" required>
            </div>
            <div class="form-group">
              <label>Expiry Date</label>
              <input type="date" name="expire_date" value="<?= $expire_date ?: ''; ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Reference</label>
              <input type="text" name="reference_no" value="<?= $reference_no; ?>" placeholder="Reference no.">
            </div>
            <?php if(!empty($warehouses)): ?>
            <div class="form-group">
              <label>Branch</label>
              <select class="mp-select" name="warehouse_id">
                <option value="">Select</option>
                <?php foreach($warehouses as $w): ?>
                  <option value="<?= $w->id; ?>" <?= ($w->id == $warehouse_id) ? 'selected' : ''; ?>><?= $w->warehouse_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="section-title">Item</div>
        <div class="card">
          <div class="form-group">
            <label>Item <span class="req">*</span></label>
            <?php
              $tax_map = [];
              foreach($taxes as $t){ $tax_map[$t->id] = $t->tax ?? 0; }
            ?>
            <select class="mp-select" name="tr_item_id_1" id="itemSelect" required>
              <option value="">Select item</option>
              <?php foreach($items as $i): ?>
                <option value="<?= $i->id; ?>" data-price="<?= $i->sales_price; ?>" data-tax="<?= $tax_map[$i->tax_id] ?? 0; ?>" data-taxtype="Exclusive" data-taxid="<?= $i->tax_id; ?>" <?= ($i->id == $item_id) ? 'selected' : ''; ?>><?= $i->item_name; ?> (<?= store_number_format($i->sales_price); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Qty <span class="req">*</span></label>
              <input type="number" step="0.01" id="qty" name="td_data_1_3" value="<?= ($qty ?? 1); ?>" required>
            </div>
            <div class="form-group">
              <label>Price <span class="req">*</span></label>
              <input type="number" step="0.01" id="price" name="td_data_1_4" value="<?= ($price ?? ''); ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Tax %</label>
              <input type="number" step="0.01" id="taxValue" name="tr_tax_value_1" value="<?= ($tax_value ?? 0); ?>">
            </div>
            <div class="form-group">
              <label>Tax Type</label>
              <select class="mp-select" name="tr_tax_type_1" id="taxType">
                <option value="Exclusive" <?= (($tax_type ?? 'Exclusive') == 'Exclusive') ? 'selected' : ''; ?>>Exclusive</option>
                <option value="Inclusive" <?= (($tax_type ?? '') == 'Inclusive') ? 'selected' : ''; ?>>Inclusive</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Discount %</label>
              <input type="number" step="0.01" id="discount" name="item_discount_input_1" value="<?= ($discount ?? 0); ?>">
            </div>
            <div class="form-group">
              <label>Tax ID</label>
              <select class="mp-select" name="tr_tax_id_1" id="taxId">
                <option value="">None</option>
                <?php foreach($taxes as $t): ?>
                  <option value="<?= $t->id; ?>" <?= ($t->id == ($tax_id ?? '')) ? 'selected' : ''; ?>><?= $t->tax_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description_1" id="description"><?= ($description ?? ''); ?></textarea>
          </div>

          <!-- computed item fields -->
          <input type="hidden" id="taxAmt" name="td_data_1_11">
          <input type="hidden" id="unitTotal" name="td_data_1_10">
          <input type="hidden" id="totalCost" name="td_data_1_9">
          <input type="hidden" id="discountAmt" name="td_data_1_8">
          <input type="hidden" name="item_discount_type_1" value="Percentage">

          <div class="totals">
            <div class="label">Total</div>
            <div class="value" id="lineTotal">0.00</div>
          </div>

          <input type="hidden" id="subtotal" name="tot_subtotal_amt">
          <input type="hidden" id="roundoff" name="tot_round_off_amt" value="0">
          <input type="hidden" id="grandtotal" name="tot_total_amt">
        </div>

        <div class="section-title">Extra Charges</div>
        <div class="card">
          <div class="form-row">
            <div class="form-group">
              <label>Shipping</label>
              <input type="number" step="0.01" id="shipping" value="0" min="0">
            </div>
            <div class="form-group">
              <label>Packaging</label>
              <input type="number" step="0.01" id="packaging" value="0" min="0">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Other</label>
              <input type="number" step="0.01" id="other_charge" value="<?= ($other_charges_input ?? 0); ?>" min="0">
            </div>
            <div class="form-group">
              <label>Charges Tax</label>
              <select class="mp-select" id="other_charges_tax_id" name="other_charges_tax_id">
                <option value="">None</option>
                <?php foreach($taxes as $t): ?>
                  <option value="<?= $t->id; ?>" data-tax="<?= $t->tax; ?>" <?= ($t->id == ($other_charges_tax_id ?? '')) ? 'selected' : ''; ?>><?= $t->tax_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <input type="hidden" id="other_charges_input" name="other_charges_input" value="<?= ($other_charges_input ?? 0); ?>">
          <input type="hidden" id="other_charges_amt" name="other_charges_amt" value="<?= ($other_charges_amt ?? 0); ?>">
          <input type="hidden" id="tot_discount" name="tot_discount_to_all_amt" value="0">
          <div class="totals">
            <div class="label">Grand Total</div>
            <div class="value" id="grandTotalDisplay">0.00</div>
          </div>
        </div>

        <div class="card">
          <div class="form-group">
            <label>Quotation Note</label>
            <textarea name="quotation_note" id="quotation_note" rows="3" placeholder="Notes, terms or delivery details..."><?= ($quotation_note ?? ''); ?></textarea>
          </div>
        </div>

        <button type="button" id="saveBtn" class="btn"><?= $quotation_id ? 'Update Quotation' : 'Save Quotation'; ?></button>
      </form>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var taxMap = {};
    <?php foreach($taxes as $t): ?>
      taxMap[<?= $t->id; ?>] = <?= (float) $t->tax; ?>;
    <?php endforeach; ?>

    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      sel.style.display = 'none';
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
        if(idx == sel.selectedIndex) d.classList.add('active');
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
      trigger.addEventListener('click', function(e){ e.stopPropagation(); document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); }); opts.classList.toggle('open'); });
    });
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    function round2(n){ return Math.round((n + Number.EPSILON) * 100) / 100; }

    function compute(){
      var qty = parseFloat(document.getElementById('qty').value) || 0;
      var price = parseFloat(document.getElementById('price').value) || 0;
      var tax = parseFloat(document.getElementById('taxValue').value) || 0;
      var taxType = document.getElementById('taxType').value;
      var discount = parseFloat(document.getElementById('discount').value) || 0;

      var lineTotal = qty * price;
      var taxAmt = 0;
      if(taxType == 'Exclusive'){
        taxAmt = round2(lineTotal * tax / 100);
      }
      var discountAmt = round2((lineTotal + taxAmt) * discount / 100);
      var totalCost = round2(lineTotal + taxAmt - discountAmt);

      document.getElementById('taxAmt').value = taxAmt;
      document.getElementById('discountAmt').value = discountAmt;
      document.getElementById('unitTotal').value = round2((totalCost / qty) || 0);
      document.getElementById('totalCost').value = totalCost;
      document.getElementById('subtotal').value = totalCost;
      document.getElementById('lineTotal').textContent = mpFormatNumber(totalCost);

      var shipping = parseFloat(document.getElementById('shipping').value) || 0;
      var packaging = parseFloat(document.getElementById('packaging').value) || 0;
      var other = parseFloat(document.getElementById('other_charge').value) || 0;
      var otherChargesInput = round2(shipping + packaging + other);
      var chargesTaxId = document.getElementById('other_charges_tax_id').value;
      var chargesTaxPct = taxMap[chargesTaxId] || 0;
      var chargesTax = round2(otherChargesInput * chargesTaxPct / 100);
      var otherChargesAmt = round2(otherChargesInput + chargesTax);
      var grand = round2(totalCost + otherChargesAmt);

      document.getElementById('other_charges_input').value = otherChargesInput;
      document.getElementById('other_charges_amt').value = otherChargesAmt;
      document.getElementById('grandtotal').value = grand;
      document.getElementById('grandTotalDisplay').textContent = mpFormatNumber(grand);
    }

    document.getElementById('itemSelect').addEventListener('change', function(){
      var opt = this.options[this.selectedIndex];
      var price = parseFloat(opt.getAttribute('data-price')) || 0;
      var tax = parseFloat(opt.getAttribute('data-tax')) || 0;
      var taxId = opt.getAttribute('data-taxid') || '';
      var taxType = opt.getAttribute('data-taxtype') || 'Exclusive';
      document.getElementById('price').value = price;
      document.getElementById('taxValue').value = tax;
      document.getElementById('taxId').value = taxId;
      // update tax type select
      var taxTypeSel = document.getElementById('taxType');
      for(var i=0; i<taxTypeSel.options.length; i++){
        if(taxTypeSel.options[i].value == taxType){ taxTypeSel.selectedIndex = i; break; }
      }
      taxTypeSel.dispatchEvent(new Event('change'));
      compute();
    });

    document.getElementById('qty').addEventListener('input', compute);
    document.getElementById('price').addEventListener('input', compute);
    document.getElementById('taxValue').addEventListener('input', compute);
    document.getElementById('taxType').addEventListener('change', function(){ /* trigger mp select label update not needed */ compute(); });
    document.getElementById('discount').addEventListener('input', compute);
    document.getElementById('taxId').addEventListener('change', compute);
    document.getElementById('shipping').addEventListener('input', compute);
    document.getElementById('packaging').addEventListener('input', compute);
    document.getElementById('other_charge').addEventListener('input', compute);
    document.getElementById('other_charges_tax_id').addEventListener('change', compute);

    document.getElementById('saveBtn').addEventListener('click', async function(){
      var form = document.getElementById('quote-form');
      compute();
      var fd = new FormData(form);
      try {
        var res = await fetch(base_url + 'quotation/quotation_save_and_update', {method: 'POST', body: fd});
        var txt = await res.text();
        var parts = txt.trim().split('<<<###>>>');
        if(parts[0] === 'success'){
          mpAlert('Quotation saved', 'success');
          setTimeout(function(){ window.location.href = base_url + 'mobile/quotation_view/' + (parts[1] || ''); }, 600);
        } else {
          mpAlert(txt.replace(/<[^>]*>/g, '').trim() || 'Save failed', 'danger');
        }
      } catch(err){
        mpAlert('Network error. Please try again.', 'danger');
      }
    });

    compute();
  </script>
</body>
</html>

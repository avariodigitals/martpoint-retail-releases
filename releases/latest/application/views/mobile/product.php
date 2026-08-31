<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — <?= $page_title; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .section-title { font-size: 15px; font-weight: 700; margin: 20px 0 10px; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-group .req { color: var(--mp-danger); }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); outline: none; -webkit-appearance: none; }
    .form-group input[type="file"] { padding: 8px; }
    .form-group textarea { resize: vertical; min-height: 60px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-group .hint { font-size: 12px; color: var(--mp-muted); margin-top: 4px; display: block; }
    .radio-group { display: flex; gap: 16px; margin-bottom: 8px; }
    .radio-group label { display: flex; align-items: center; gap: 6px; font-weight: 500; cursor: pointer; }
    .radio-group input { width: auto; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 46px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 12px 12px; background: #fff; max-height: 220px; overflow-y: auto; position: relative; z-index: 10; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 12px 12px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option:hover, .mp-select-option.active { background: var(--mp-bg); }
    .variant-picker { display: flex; gap: 8px; margin-bottom: 12px; align-items: stretch; }
    .variant-picker .mp-select-wrap { flex: 1; }
    .attr-type { margin-bottom: 12px; }
    .attr-type .attr-title { font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--mp-ink); }
    .attr-values { display: flex; flex-wrap: wrap; gap: 8px; }
    .attr-values label { display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid var(--mp-border); border-radius: 20px; padding: 6px 12px; font-size: 13px; cursor: pointer; }
    .attr-values input { width: auto; }
    .attr-values label.checked { background: var(--mp-bg); border-color: var(--mp-primary); color: var(--mp-primary); }
    .matrix-preview { font-size: 12px; color: var(--mp-muted); margin: 8px 0; }
    .btn { display: block; width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-primary); border: 1px solid var(--mp-border); }
    .variant-row { background: var(--mp-bg); border-radius: 12px; padding: 12px; margin-bottom: 10px; }
    .variant-row .v-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .variant-row .v-title { font-weight: 700; font-size: 15px; }
    .variant-row .v-remove { color: var(--mp-danger); font-size: 13px; font-weight: 600; background: none; border: none; padding: 0; }
    .variant-row .form-row { margin-bottom: 0; }
    .empty-state { text-align: center; padding: 20px; color: var(--mp-muted); font-size: 13px; }
    #toast { position: fixed; top: 16px; left: 50%; transform: translateX(-50%) translateY(-120%); max-width: 360px; width: calc(100% - 32px); padding: 14px 18px; border-radius: 14px; background: #0F172A; color: #fff; font-size: 14px; font-weight: 500; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000; opacity: 0; transition: all 0.3s ease; }
    #toast.active { transform: translateX(-50%) translateY(0); opacity: 1; }
    #toast.success { background: var(--mp-success); }
    #toast.error { background: var(--mp-danger); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <form id="product-form" action="<?= base_url('mobile/save_product'); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" id="hidden_rowcount" name="hidden_rowcount" value="0">
        <input type="hidden" id="q_id" name="q_id" value="<?= $q_id; ?>">
        <input type="hidden" id="command" name="command" value="<?= $command; ?>">

        <div class="section-title">Basics</div>
        <div class="card">
          <div class="form-group">
            <label>Product Code</label>
            <input type="text" name="item_code" value="<?= $item_code; ?>" readonly>
          </div>
          <div class="form-group">
            <label>Product Name <span class="req">*</span></label>
            <input type="text" name="item_name" id="item_name" value="<?= htmlspecialchars($item_name); ?>" required autofocus>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Category <span class="req">*</span></label>
              <select class="mp-select" name="category_id" required>
                <option value="">Select</option>
                <?= get_categories_select_list($category_id); ?>
              </select>
            </div>
            <div class="form-group">
              <label>Brand</label>
              <select class="mp-select" name="brand_id">
                <option value="">Select</option>
                <?= get_brands_select_list($brand_id); ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Unit <span class="req">*</span></label>
              <select class="mp-select" name="unit_id" required>
                <option value="">Select</option>
                <?= get_units_select_list($unit_id); ?>
              </select>
            </div>
            <div class="form-group">
              <label>Alert Qty</label>
              <input type="number" name="alert_qty" value="<?= $alert_qty; ?>" min="0" step="0.01">
            </div>
          </div>
          <div class="form-group">
            <label>Product Type</label>
            <select class="mp-select" name="item_group" id="item_group" required>
              <option value="Single" <?= $item_group == 'Single' ? 'selected' : ''; ?>>Single Product</option>
              <?php if(mp_feature_enabled('bundles')): ?>
              <option value="Variants" <?= $item_group == 'Variants' ? 'selected' : ''; ?>>Variant Product</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Barcode</label>
              <input type="text" name="custom_barcode" value="<?= htmlspecialchars($custom_barcode); ?>">
            </div>
            <div class="form-group">
              <label>SKU</label>
              <input type="text" name="sku" value="<?= htmlspecialchars($sku); ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="2"><?= htmlspecialchars($description); ?></textarea>
          </div>
          <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="item_image" id="item_image" accept="image/*">
            <small class="hint">Max 1MB, jpg/png/gif/webp, 1500x1500</small>
          </div>
        </div>

        <div class="section-title">Pricing &amp; Tax</div>
        <div class="card">
          <div class="form-row">
            <div class="form-group">
              <label>Base/Cost Price <span class="req">*</span></label>
              <input type="number" step="0.01" name="price" id="price" value="<?= $price; ?>" required>
            </div>
            <div class="form-group">
              <label>Purchase Price <span class="req">*</span></label>
              <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="<?= $purchase_price; ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Sale Price <span class="req">*</span></label>
              <input type="number" step="0.01" name="sales_price" id="sales_price" value="<?= $sales_price; ?>" required>
            </div>
            <div class="form-group">
              <label>MRP</label>
              <input type="number" step="0.01" name="mrp" id="mrp" value="<?= $mrp; ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Profit Margin (%)</label>
              <input type="number" step="0.01" name="profit_margin" id="profit_margin" value="<?= $profit_margin; ?>">
            </div>
            <div class="form-group">
              <label>Opening Stock</label>
              <input type="number" step="0.01" name="adjustment_qty" id="adjustment_qty" value="<?= $adjustment_qty; ?>" min="0">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Tax <span class="req">*</span></label>
              <select class="mp-select" name="tax_id" required>
                <option value="">Select</option>
                <?= get_tax_select_list($tax_id); ?>
              </select>
            </div>
            <div class="form-group">
              <label>Tax Type <span class="req">*</span></label>
              <select class="mp-select" name="tax_type" required>
                <option value="Exclusive" <?= $tax_type == 'Exclusive' ? 'selected' : ''; ?>>Exclusive</option>
                <option value="Inclusive" <?= $tax_type == 'Inclusive' ? 'selected' : ''; ?>>Inclusive</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Discount Type</label>
              <select class="mp-select" name="discount_type">
                <option value="Percentage" <?= $discount_type == 'Percentage' ? 'selected' : ''; ?>>Percentage(%)</option>
                <option value="Fixed" <?= $discount_type == 'Fixed' ? 'selected' : ''; ?>>Fixed</option>
              </select>
            </div>
            <div class="form-group">
              <label>Discount Value</label>
              <input type="number" step="0.01" name="discount" value="<?= $discount; ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Branch</label>
            <select class="mp-select" name="warehouse_id">
              <?= get_warehouse_select_list($warehouse_id ?? '', '', true); ?>
            </select>
          </div>
        </div>

        <div id="variants-section" style="display:none;">
          <div class="section-title">Variants</div>
          <div class="card">
            <div class="section-title" style="margin-top:0; font-size:14px;">Build from Attributes</div>
            <div id="attribute-builder"></div>
            <div class="matrix-preview" id="matrix-preview"></div>
            <button type="button" id="btn-generate-matrix" class="btn btn-secondary" style="padding: 10px 16px; margin-bottom: 16px;">Generate Matrix</button>

            <div class="section-title" style="font-size:14px;">Or Add Existing Variant</div>
            <div class="variant-picker">
              <select class="mp-select" id="variant_select">
                <option value="">Select an existing variant</option>
                <?php foreach($variants as $v): ?>
                <option value="<?= $v->id; ?>" data-name="<?= htmlspecialchars($v->variant_name); ?>"><?= htmlspecialchars($v->variant_name); ?></option>
                <?php endforeach; ?>
              </select>
              <button type="button" id="btn-add-variant" class="btn btn-secondary" style="width:auto; padding: 10px 16px;">Add</button>
            </div>
            <?php if(empty($variants) && empty($attribute_map)): ?>
              <div class="empty-state">No variants or attributes available. Create them from the desktop first.</div>
            <?php endif; ?>
            <div id="variant-rows"></div>
          </div>
        </div>

        <button type="submit" id="btn-save" class="btn btn-primary">Save Product</button>
      </form>
    </section>
  </div>

  <div id="toast"></div>

  <script>
    var variantRows = 0;
    var attributeMap = <?= json_encode($attribute_map ?? []); ?>;
    var availableVariants = {};
    document.querySelectorAll('#variant_select option').forEach(function(opt){
      if(opt.value) availableVariants[opt.value] = opt.dataset.name;
    });

    function showToast(message, type){
      var toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = type === 'success' ? 'success' : 'error';
      toast.classList.add('active');
      setTimeout(function(){ toast.classList.remove('active'); }, 3000);
    }

    function toggleSections(){
      var group = document.getElementById('item_group').value;
      var vsec = document.getElementById('variants-section');
      if(group === 'Variants'){
        vsec.style.display = 'block';
      } else {
        vsec.style.display = 'none';
        document.getElementById('variant-rows').innerHTML = '';
        variantRows = 0;
        document.getElementById('hidden_rowcount').value = 0;
      }
    }
    document.getElementById('item_group').addEventListener('change', toggleSections);
    toggleSections();

    function initMpSelects(){
      document.querySelectorAll('select.mp-select').forEach(function(sel){
        if(sel.dataset.mpInit) return;
        sel.dataset.mpInit = '1';

        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);

        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        wrap.appendChild(trigger);

        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);

        var options = Array.from(sel.options);

        function renderOptions(){
          list.innerHTML = '';
          options.forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              updateTrigger();
              sel.dispatchEvent(new Event('change', {bubbles: true}));
              closeAllMpSelects();
            });
            list.appendChild(div);
          });
        }

        function updateTrigger(){
          var s = sel.options[sel.selectedIndex];
          if(s && s.value){
            trigger.textContent = s.textContent;
            trigger.classList.remove('placeholder');
          } else {
            trigger.textContent = (s && s.textContent) ? s.textContent : 'Select';
            trigger.classList.add('placeholder');
          }
          renderOptions();
        }

        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAllMpSelects();
          wrap.classList.toggle('open');
        });

        sel.addEventListener('change', updateTrigger);

        updateTrigger();
      });

      document.addEventListener('click', function(){
        closeAllMpSelects();
      });
    }

    function closeAllMpSelects(){
      document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); });
    }

    initMpSelects();

    function reindexVariantRows(){
      var rows = document.querySelectorAll('.variant-row');
      rows.forEach(function(row, idx){
        var i = idx + 1;
        var old = parseInt(row.dataset.index) || (idx + 1);
        row.dataset.index = i;
        row.querySelector('.v-index').textContent = i;
        var controls = row.querySelectorAll('input, select, textarea');
        controls.forEach(function(el){
          if(el.name){
            el.name = el.name.replace(new RegExp('^td_data_' + old + '_'), 'td_data_' + i + '_');
            ['tr_variant_id_', 'variant_name_', 'count_id_', 'item_code_', 'variant_image_'].forEach(function(prefix){
              if(el.name === prefix + old) el.name = prefix + i;
            });
          }
          if(el.id){
            el.id = el.id.replace(new RegExp('^td_data_' + old + '_'), 'td_data_' + i + '_');
            ['tr_variant_id_', 'variant_name_', 'count_id_', 'item_code_', 'variant_image_'].forEach(function(prefix){
              if(el.id === prefix + old) el.id = prefix + i;
            });
          }
        });
      });
      variantRows = rows.length;
      document.getElementById('hidden_rowcount').value = variantRows;
    }

    function fillVariantDefaults(i){
      var base = parseFloat(document.getElementById('price').value) || 0;
      var purchase = parseFloat(document.getElementById('purchase_price').value) || 0;
      var sales = parseFloat(document.getElementById('sales_price').value) || 0;
      var row = document.querySelector('.variant-row[data-index="' + i + '"]');
      if(!row || base <= 0) return;
      var price3 = row.querySelector('[name="td_data_' + i + '_3"]');
      var price4 = row.querySelector('[name="td_data_' + i + '_4"]');
      var price5 = row.querySelector('[name="td_data_' + i + '_5"]');
      var price6 = row.querySelector('[name="td_data_' + i + '_6"]');
      if(price3 && price3.value === '') price3.value = round2(base);
      if(price4 && price4.value === '') price4.value = purchase > 0 ? round2(purchase) : round2(base);
      if(price5 && price5.value === '') price5.value = '0';
      if(price6 && price6.value === '') price6.value = sales > 0 ? round2(sales) : round2(base);
    }

    document.getElementById('btn-add-variant').addEventListener('click', function(){
      var sel = document.getElementById('variant_select');
      var variantId = sel.value;
      if(!variantId) return;
      var name = availableVariants[variantId];
      if(!name) return;

      // Prevent duplicate
      if(document.querySelector('input[name^="tr_variant_id_"][value="' + variantId + '"')){
        showToast('This variant is already added.', 'error');
        return;
      }

      variantRows++;
      var i = variantRows;
      var html =
        '<div class="variant-row" data-index="' + i + '">' +
          '<div class="v-header">' +
            '<div><span class="v-index">' + i + '</span>. <span class="v-title">' + name + '</span></div>' +
            '<button type="button" class="v-remove" onclick="removeVariantRow(this)">Remove</button>' +
          '</div>' +
          '<input type="hidden" name="tr_variant_id_' + i + '" value="' + variantId + '">' +
          '<input type="hidden" name="td_data_' + i + '_9" value="">' +
          '<input type="hidden" name="count_id_' + i + '" value="">' +
          '<input type="hidden" name="item_code_' + i + '" value="">' +
          '<div class="form-row">' +
            '<div class="form-group"><label>SKU</label><input type="text" name="td_data_' + i + '_2" placeholder="SKU"></div>' +
            '<div class="form-group"><label>Barcode</label><input type="text" name="td_data_' + i + '_8" placeholder="Barcode"></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group"><label>Base/Cost Price</label><input type="number" step="0.01" name="td_data_' + i + '_3" placeholder="0.00" required></div>' +
            '<div class="form-group"><label>Purchase Price</label><input type="number" step="0.01" name="td_data_' + i + '_4" placeholder="0.00" required></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group"><label>Profit Margin (%)</label><input type="number" step="0.01" name="td_data_' + i + '_5" placeholder="0"></div>' +
            '<div class="form-group"><label>Sale Price</label><input type="number" step="0.01" name="td_data_' + i + '_6" placeholder="0.00" required></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group"><label>MRP</label><input type="number" step="0.01" name="td_data_' + i + '_10" placeholder="0.00"></div>' +
            '<div class="form-group"><label>Opening Stock</label><input type="number" step="0.01" name="td_data_' + i + '_11" placeholder="0" min="0"></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group" style="grid-column: 1 / -1;"><label>Variant Image</label><input type="file" name="variant_image_' + i + '" accept="image/*"></div>' +
          '</div>' +
        '</div>';
      document.getElementById('variant-rows').insertAdjacentHTML('beforeend', html);
      sel.value = '';
      sel.dispatchEvent(new Event('change'));
      reindexVariantRows();
      fillVariantDefaults(i);
    });

    function removeVariantRow(btn){
      btn.closest('.variant-row').remove();
      reindexVariantRows();
    }

    // Pricing helpers: base cost price seeds purchase & sales, but every field stays editable
    function setIfEmpty(el, value){
      if(el && (el.value === '' || el.value === null)) el.value = value;
    }
    function round2(n){ return (Math.round(n * 100) / 100).toFixed(2); }
    function computeMargin(cost, sale){ return cost > 0 ? round2(((sale - cost) / cost) * 100) : 0; }
    function computeSale(cost, margin){ return round2(cost * (1 + margin / 100)); }

    document.getElementById('price').addEventListener('change', function(){
      var base = parseFloat(this.value) || 0;
      if(base > 0){
        setIfEmpty(document.getElementById('purchase_price'), round2(base));
        setIfEmpty(document.getElementById('sales_price'), round2(base));
        setIfEmpty(document.getElementById('profit_margin'), 0);
      }
    });
    document.getElementById('purchase_price').addEventListener('change', function(){
      var cost = parseFloat(this.value) || 0;
      var margin = parseFloat(document.getElementById('profit_margin').value) || 0;
      if(cost > 0 && margin >= 0){
        var sale = computeSale(cost, margin);
        if(document.getElementById('sales_price').value === ''){
          document.getElementById('sales_price').value = sale;
        }
      }
    });
    document.getElementById('profit_margin').addEventListener('change', function(){
      var cost = parseFloat(document.getElementById('purchase_price').value) || 0;
      var margin = parseFloat(this.value) || 0;
      if(cost > 0){
        document.getElementById('sales_price').value = computeSale(cost, margin);
      }
    });
    document.getElementById('sales_price').addEventListener('change', function(){
      var cost = parseFloat(document.getElementById('purchase_price').value) || 0;
      var sale = parseFloat(this.value) || 0;
      if(cost > 0){
        document.getElementById('profit_margin').value = computeMargin(cost, sale);
      }
    });

    // Same auto-fill for dynamically added variant rows
    document.getElementById('variant-rows').addEventListener('change', function(e){
      var el = e.target;
      var match = el.name.match(/^td_data_(\d+)_(\d+)$/);
      if(!match) return;
      var i = match[1], col = match[2];
      if(col === '3'){
        var base = parseFloat(el.value) || 0;
        if(base > 0){
          var pp = document.getElementsByName('td_data_' + i + '_4')[0];
          var sp = document.getElementsByName('td_data_' + i + '_6')[0];
          var pm = document.getElementsByName('td_data_' + i + '_5')[0];
          if(pp) setIfEmpty(pp, round2(base));
          if(sp) setIfEmpty(sp, round2(base));
          if(pm) setIfEmpty(pm, 0);
        }
      }
      if(col === '4'){
        var cost = parseFloat(el.value) || 0;
        var pm = document.getElementsByName('td_data_' + i + '_5')[0];
        var sp = document.getElementsByName('td_data_' + i + '_6')[0];
        if(cost > 0 && pm && parseFloat(pm.value) >= 0 && sp){
          setIfEmpty(sp, computeSale(cost, parseFloat(pm.value)));
        }
      }
      if(col === '5'){
        var cost = parseFloat(document.getElementsByName('td_data_' + i + '_4')[0].value) || 0;
        var margin = parseFloat(el.value) || 0;
        var sp = document.getElementsByName('td_data_' + i + '_6')[0];
        if(cost > 0 && sp) sp.value = computeSale(cost, margin);
      }
      if(col === '6'){
        var cost = parseFloat(document.getElementsByName('td_data_' + i + '_4')[0].value) || 0;
        var sale = parseFloat(el.value) || 0;
        var pm = document.getElementsByName('td_data_' + i + '_5')[0];
        if(cost > 0 && pm) pm.value = computeMargin(cost, sale);
      }
    });

    // Attribute-driven variant matrix
    function buildAttributeBuilder(){
      var container = document.getElementById('attribute-builder');
      if(!container || !attributeMap || Object.keys(attributeMap).length === 0){
        if(container) container.innerHTML = '<div class="empty-state">No attributes configured for this store.</div>';
        return;
      }
      container.innerHTML = '';
      Object.keys(attributeMap).forEach(function(type){
        var values = attributeMap[type];
        var wrap = document.createElement('div');
        wrap.className = 'attr-type';
        wrap.innerHTML = '<div class="attr-title">' + type.charAt(0).toUpperCase() + type.slice(1) + '</div>' +
          '<div class="attr-values" id="attr-values-' + type + '"></div>';
        container.appendChild(wrap);
        var list = wrap.querySelector('.attr-values');
        values.forEach(function(val){
          var label = document.createElement('label');
          label.innerHTML = '<input type="checkbox" value="' + val + '" data-type="' + type + '"> ' + val;
          label.querySelector('input').addEventListener('change', function(){
            label.classList.toggle('checked', this.checked);
            updateMatrixPreview();
          });
          list.appendChild(label);
        });
      });
    }

    function getSelectedAttributeValues(){
      var selected = {};
      document.querySelectorAll('.attr-values input:checked').forEach(function(cb){
        var type = cb.dataset.type;
        if(!selected[type]) selected[type] = [];
        selected[type].push(cb.value);
      });
      return selected;
    }

    function cartesianProduct(arrs){
      if(!arrs.length) return [];
      return arrs.reduce(function(a, b){
        return a.map(function(x){ return b.map(function(y){ return x.concat([y]); }); }).reduce(function(p, c){ return p.concat(c); }, []);
      }, [[]]);
    }

    function comboName(combo){
      return combo.map(function(c){ return c.value; }).join(' / ');
    }

    function existingVariantNames(){
      var names = [];
      document.querySelectorAll('.variant-row .v-title').forEach(function(el){ names.push(el.textContent.trim()); });
      return names;
    }

    function updateMatrixPreview(){
      var selected = getSelectedAttributeValues();
      var types = Object.keys(selected);
      if(types.length === 0){
        document.getElementById('matrix-preview').textContent = '';
        return;
      }
      var arrs = types.map(function(t){ return selected[t].map(function(v){ return {type: t, value: v}; }); });
      var combos = cartesianProduct(arrs);
      document.getElementById('matrix-preview').textContent = combos.length + ' variant' + (combos.length === 1 ? '' : 's') + ' will be created';
    }

    document.getElementById('btn-generate-matrix').addEventListener('click', function(){
      var selected = getSelectedAttributeValues();
      var types = Object.keys(selected);
      if(types.length === 0){
        showToast('Select at least one value from an attribute.', 'error');
        return;
      }
      var arrs = types.map(function(t){ return selected[t].map(function(v){ return {type: t, value: v}; }); });
      var combos = cartesianProduct(arrs);
      if(combos.length === 0) return;

      var existing = existingVariantNames();
      var added = 0;
      combos.forEach(function(combo){
        var name = comboName(combo);
        if(existing.indexOf(name) !== -1) return;
        addGeneratedVariant(name);
        added++;
      });
      showToast(added + ' variant' + (added === 1 ? '' : 's') + ' generated.', 'success');
    });

    function addGeneratedVariant(name){
      variantRows++;
      var i = variantRows;
      var html =
        '<div class="variant-row" data-index="' + i + '">' +
          '<div class="v-header">' +
            '<div><span class="v-index">' + i + '</span>. <span class="v-title">' + name + '</span></div>' +
            '<button type="button" class="v-remove" onclick="removeVariantRow(this)">Remove</button>' +
          '</div>' +
          '<input type="hidden" name="tr_variant_id_' + i + '" value="new">' +
          '<input type="hidden" name="variant_name_' + i + '" value="' + name + '">' +
          '<input type="hidden" name="td_data_' + i + '_9" value="">' +
          '<input type="hidden" name="count_id_' + i + '" value="">' +
          '<input type="hidden" name="item_code_' + i + '" value="">' +
          '<div class="form-row">' +
            '<div class="form-group"><label>SKU</label><input type="text" name="td_data_' + i + '_2" placeholder="SKU"></div>' +
            '<div class="form-group"><label>Barcode</label><input type="text" name="td_data_' + i + '_8" placeholder="Barcode"></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group"><label>Base/Cost Price</label><input type="number" step="0.01" name="td_data_' + i + '_3" placeholder="0.00" required></div>' +
            '<div class="form-group"><label>Purchase Price</label><input type="number" step="0.01" name="td_data_' + i + '_4" placeholder="0.00" required></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group"><label>Profit Margin (%)</label><input type="number" step="0.01" name="td_data_' + i + '_5" placeholder="0"></div>' +
            '<div class="form-group"><label>Sale Price</label><input type="number" step="0.01" name="td_data_' + i + '_6" placeholder="0.00" required></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group"><label>MRP</label><input type="number" step="0.01" name="td_data_' + i + '_10" placeholder="0.00"></div>' +
            '<div class="form-group"><label>Opening Stock</label><input type="number" step="0.01" name="td_data_' + i + '_11" placeholder="0" min="0"></div>' +
          '</div>' +
          '<div class="form-row">' +
            '<div class="form-group" style="grid-column: 1 / -1;"><label>Variant Image</label><input type="file" name="variant_image_' + i + '" accept="image/*"></div>' +
          '</div>' +
        '</div>';
      document.getElementById('variant-rows').insertAdjacentHTML('beforeend', html);
      reindexVariantRows();
      fillVariantDefaults(i);
    }

    buildAttributeBuilder();

    document.getElementById('product-form').addEventListener('submit', function(e){
      e.preventDefault();
      var btn = document.getElementById('btn-save');
      btn.disabled = true;
      btn.textContent = 'Saving...';

      var formData = new FormData(this);

      fetch('<?= base_url('mobile/save_product'); ?>', {
        method: 'POST',
        body: formData
      })
      .then(function(res){ return res.json(); })
      .then(function(data){
        btn.disabled = false;
        btn.textContent = 'Save Product';
        if(data.status === 'success'){
          showToast(data.message, 'success');
          setTimeout(function(){
            window.location.href = data.redirect || '<?= base_url('mobile/stock'); ?>';
          }, 800);
        } else {
          showToast(data.message || 'Save failed.', 'error');
        }
      })
      .catch(function(err){
        btn.disabled = false;
        btn.textContent = 'Save Product';
        showToast('Network or server error. Try again.', 'error');
      });
    });
  </script>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>

<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
<?php $this->load->view('comman/app_icons'); ?>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Record Wastage</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-ink: #1E293B; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .lede { font-size: 13px; color: var(--mp-muted); margin: 0 0 14px; padding-left: 34px; }
    .section-title { font-size: 15px; font-weight: 700; margin: 18px 0 10px; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-group .req { color: var(--mp-danger); }
    .form-group input[type=text], .form-group input[type=date], .form-group textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); outline: none; }
    .form-group textarea { resize: vertical; min-height: 64px; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .search-wrap { position: relative; }
    .search-input { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); outline: none; }
    .search-results { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 240px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .search-results.open { display: block; }
    .search-result { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .search-result:last-child { border-bottom: none; }
    .search-result .stock { display: block; font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .search-empty { padding: 14px; color: var(--mp-muted); font-size: 13px; text-align: center; }
    .picked { display: flex; align-items: flex-start; gap: 10px; }
    .picked .name { font-weight: 600; font-size: 15px; flex: 1; min-width: 0; }
    .picked .meta { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .picked .clear { color: var(--mp-danger); background: none; border: none; font-size: 16px; cursor: pointer; padding: 2px 6px; }
    .qty-row { display: flex; align-items: center; gap: 12px; }
    .qty-control { display: flex; align-items: center; border: 1px solid var(--mp-border); border-radius: 12px; overflow: hidden; }
    .qty-control button { width: 46px; height: 44px; background: var(--mp-bg); border: none; color: var(--mp-ink); font-size: 20px; cursor: pointer; }
    .qty-control input { width: 84px; height: 44px; border: none; border-left: 1px solid var(--mp-border); border-right: 1px solid var(--mp-border); text-align: center; font-size: 16px; font-weight: 600; outline: none; }
    .unit-hint { font-size: 12px; color: var(--mp-muted); }
    .reasons { display: flex; flex-wrap: wrap; gap: 8px; }
    .reason-chip { flex: 1 1 44%; padding: 12px 10px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; color: var(--mp-ink); font-size: 14px; font-weight: 600; cursor: pointer; text-align: center; }
    .reason-chip[aria-pressed=true] { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .btn { display: block; width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-primary[disabled] { opacity: .55; cursor: default; }
    .btn-link { display: block; margin-top: 12px; text-align: center; font-size: 14px; color: var(--mp-primary); text-decoration: none; font-weight: 600; }
    .fineprint { font-size: 12px; color: var(--mp-muted); margin-top: 10px; text-align: center; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/stock_adjustments'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Record Wastage</h1>
        </div>
      </div>
      <p class='lede'>Stock that left the shelf without being sold — broken, spilled, expired or counted short.</p>

      <form id='wastage-form' action='<?= base_url('mobile/save_wastage'); ?>' method='post' autocomplete='off'>
        <input type='hidden' name='<?= $this->security->get_csrf_token_name(); ?>' value='<?= $this->security->get_csrf_hash(); ?>'>
        <input type='hidden' name='command' value='save'>
        <input type='hidden' name='rowcount' value='1'>
        <input type='hidden' name='reference_no' id='reference_no' value='<?= htmlspecialchars($reference_no); ?>'>
        <input type='hidden' name='tr_item_id_1' id='tr_item_id_1' value=''>
        <input type='hidden' name='tr_reason_1' id='tr_reason_1' value=''>

        <div class='card'>
          <div class='form-group'>
            <label>What went out? <span class='req'>*</span></label>
            <div class='search-wrap' id='pick-search'>
              <input type='text' class='search-input' id='item-search' placeholder='Type the product name, code or barcode' aria-label='Search products'>
              <div class='search-results' id='search-results'></div>
            </div>
            <div class='picked' id='picked' style='display:none;'>
              <div style='flex:1;min-width:0;'>
                <div class='name' id='picked-name'></div>
                <div class='meta' id='picked-meta'></div>
              </div>
              <button type='button' class='clear' id='picked-clear' aria-label='Choose a different product'><i class='fa fa-times-circle'></i></button>
            </div>
          </div>

          <div class='form-group'>
            <label>How much? <span class='req'>*</span></label>
            <div class='qty-row'>
              <div class='qty-control'>
                <button type='button' id='qty-minus' aria-label='Less'>−</button>
                <input type='number' id='qty' name='td_data_1_3' value='1' min='0.01' step='0.01' inputmode='decimal' aria-label='Quantity written off'>
                <button type='button' id='qty-plus' aria-label='More'>+</button>
              </div>
              <div class='unit-hint' id='unit-hint'></div>
            </div>
          </div>
        </div>

        <div class='section-title'>Why did it go out? <span class='req'>*</span></div>
        <div class='card'>
          <div class='reasons' role='group' aria-label='Wastage reason'>
            <?php foreach ($reasons as $code => $label): ?>
              <button type='button' class='reason-chip' data-code='<?= htmlspecialchars($code); ?>' aria-pressed='false'><?= htmlspecialchars($label); ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class='card'>
          <?php if ($show_outlet_picker): ?>
            <div class='form-group'>
              <label>Which outlet?</label>
              <select name='warehouse_id' id='warehouse_id' class='mp-select'>
                <?= get_warehouse_select_list($default_warehouse_id, get_current_store_id(), false); ?>
              </select>
            </div>
          <?php endif; ?>
          <div class='form-group'>
            <label>Date</label>
            <input type='date' name='adjustment_date' id='adjustment_date' value='<?= htmlspecialchars($adjustment_date); ?>'>
          </div>
          <div class='form-group'>
            <label>Anything to add? <span style='font-weight:400;color:var(--mp-muted);'>(optional)</span></label>
            <textarea name='adjustment_note' id='adjustment_note' rows='2' placeholder='e.g. crate slipped off the bar while restocking'></textarea>
          </div>
        </div>

        <button type='submit' class='btn btn-primary' id='btn-save' disabled>Write it off</button>
        <div class='fineprint'>Goes onto the stock ledger straight away, and shows up under Stock Adjustments.</div>
        <a class='btn-link' href='<?= base_url('mobile/stock_adjustments'); ?>'>See all adjustments</a>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <script>
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    var form = document.getElementById('wastage-form');
    var btn = document.getElementById('btn-save');
    var searchInput = document.getElementById('item-search');
    var searchResults = document.getElementById('search-results');
    var pickSearch = document.getElementById('pick-search');
    var picked = document.getElementById('picked');
    var pickedName = document.getElementById('picked-name');
    var pickedMeta = document.getElementById('picked-meta');
    var itemId = document.getElementById('tr_item_id_1');
    var reasonField = document.getElementById('tr_reason_1');
    var qtyInput = document.getElementById('qty');
    var unitHint = document.getElementById('unit-hint');
    var referenceField = document.getElementById('reference_no');
    var onHand = 0;
    var searchTimeout;

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
              sel.dispatchEvent(new Event('change', {bubbles: true}));
            });
            list.appendChild(div);
          });
        }
        function updateTrigger(){
          var opt = sel.options[sel.selectedIndex];
          trigger.textContent = opt ? opt.textContent : 'Select';
          trigger.classList.toggle('placeholder', !opt || !opt.value);
          renderOptions();
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          document.querySelectorAll('.mp-select-options.open, .search-results.open').forEach(function(o){ o.classList.remove('open'); });
          list.classList.toggle('open');
        });
        sel.addEventListener('change', updateTrigger);
        updateTrigger();
      });
      document.addEventListener('click', function(){
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); });
      });
    }
    initMpSelects();

    function readyToSave(){
      btn.disabled = !(itemId.value && reasonField.value);
    }

    function setItem(id, name, stock, unit){
      itemId.value = id;
      onHand = parseFloat(stock) || 0;
      pickedName.textContent = name;
      pickedMeta.textContent = '<?= htmlspecialchars($stock_scope_label, ENT_QUOTES); ?>: '
        + (onHand % 1 === 0 ? onHand.toFixed(0) : onHand.toFixed(2)) + (unit ? ' ' + unit : '');
      unitHint.textContent = unit ? 'in ' + unit : '';
      pickSearch.style.display = 'none';
      picked.style.display = 'flex';
      readyToSave();
    }

    function clearItem(){
      itemId.value = '';
      onHand = 0;
      qtyInput.value = 1;
      picked.style.display = 'none';
      pickSearch.style.display = '';
      searchInput.value = '';
      searchResults.innerHTML = '';
      readyToSave();
      searchInput.focus();
    }
    document.getElementById('picked-clear').addEventListener('click', clearItem);

    document.querySelectorAll('.reason-chip').forEach(function(chip){
      chip.addEventListener('click', function(){
        document.querySelectorAll('.reason-chip').forEach(function(o){ o.setAttribute('aria-pressed', 'false'); });
        chip.setAttribute('aria-pressed', 'true');
        reasonField.value = chip.dataset.code;
        readyToSave();
      });
    });

    document.getElementById('qty-minus').addEventListener('click', function(){
      qtyInput.value = Math.max(0.01, (parseFloat(qtyInput.value) || 1) - 1).toFixed(qtyInput.value % 1 ? 2 : 0);
    });
    document.getElementById('qty-plus').addEventListener('click', function(){
      var next = (parseFloat(qtyInput.value) || 0) + 1;
      if(onHand > 0 && next > onHand) next = onHand;
      qtyInput.value = next;
    });

    searchInput.addEventListener('input', function(){
      clearTimeout(searchTimeout);
      var term = this.value.trim();
      if(term.length < 2){ searchResults.classList.remove('open'); searchResults.innerHTML = ''; return; }
      searchTimeout = setTimeout(function(){
        mpFetchJson('<?= base_url('mobile/wastage_items'); ?>?q=' + encodeURIComponent(term))
          .then(function(response){
            var rows = (response && response.rows) ? response.rows : [];
            searchResults.innerHTML = '';
            if(!rows.length){
              searchResults.innerHTML = '<div class="search-empty">Nothing here matches that. This list only holds your own store’s products.</div>';
            } else {
              rows.forEach(function(row){
                var div = document.createElement('div');
                div.className = 'search-result';
                var label = document.createElement('span');
                label.textContent = row.item_name;
                var stock = document.createElement('span');
                stock.className = 'stock';
                stock.textContent = (row.item_code ? row.item_code + ' · ' : '')
                    + '<?= htmlspecialchars($stock_scope_label, ENT_QUOTES); ?> ' + row.stock;
                div.appendChild(label);
                div.appendChild(stock);
                div.addEventListener('click', function(e){
                  e.stopPropagation();
                  setItem(row.id, row.item_name, row.stock, row.unit);
                  searchResults.classList.remove('open');
                });
                searchResults.appendChild(div);
              });
            }
            searchResults.classList.add('open');
          })
          .catch(function(){
            searchResults.innerHTML = '<div class="search-empty">Could not search right now. Check the connection and try again.</div>';
            searchResults.classList.add('open');
          });
      }, 300);
    });
    document.addEventListener('click', function(){ searchResults.classList.remove('open'); });

    form.addEventListener('submit', function(e){
      e.preventDefault();
      if(btn.disabled) return;
      var qty = parseFloat(qtyInput.value) || 0;
      if(qty <= 0){ cqToast('Enter how much went out.', 'warning'); return; }
      if(!reasonField.value){ cqToast('Choose the reason it went out.', 'warning'); return; }

      btn.disabled = true;
      var was = btn.textContent;
      btn.textContent = 'Saving…';

      mpFetchJson('<?= base_url('mobile/save_wastage'); ?>', { method: 'POST', body: new FormData(form) })
        .then(function(data){
          btn.textContent = was;
          if(data && data.status === 'success'){
            cqToast(data.message, 'success');
            // A fresh sheet needs a fresh replay reference, otherwise the next
            // breakage would look like a retry of the one just recorded.
            if(data.reference_no) referenceField.value = data.reference_no;
            document.querySelectorAll('.reason-chip').forEach(function(o){ o.setAttribute('aria-pressed', 'false'); });
            reasonField.value = '';
            document.getElementById('adjustment_note').value = '';
            clearItem();
          } else {
            readyToSave();
            cqToast((data && data.message) || 'Could not record that. Nothing was taken off the shelf.', 'danger');
          }
        })
        .catch(function(err){
          btn.textContent = was;
          readyToSave();
          cqToast(mpErrorText(err, 'Could not reach the server. Nothing was recorded — try again.'), 'danger');
        });
    });
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>

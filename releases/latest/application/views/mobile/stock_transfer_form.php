<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — New Stock Transfer</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .section-title { font-size: 15px; font-weight: 700; margin: 16px 0 10px; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-group .req { color: var(--mp-danger); }
    .form-group input, .form-group textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); outline: none; }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
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
    .search-results { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .search-results.open { display: block; }
    .search-result { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .search-result:last-child { border-bottom: none; }
    .search-result:hover { background: var(--mp-bg); }
    .search-empty { padding: 14px; color: var(--mp-muted); font-size: 13px; text-align: center; }
    .item-row { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 10px; }
    .item-row .item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .item-row .item-name { font-weight: 600; font-size: 15px; }
    .item-row .remove { color: var(--mp-danger); background: none; border: none; font-size: 16px; cursor: pointer; padding: 4px; }
    .item-row .qty-row { display: flex; align-items: center; gap: 12px; }
    .item-row .qty-row label { font-size: 13px; color: var(--mp-muted); }
    .item-row .qty-control { display: flex; align-items: center; border: 1px solid var(--mp-border); border-radius: 12px; overflow: hidden; }
    .item-row .qty-control button { width: 42px; height: 42px; background: var(--mp-bg); border: none; color: var(--mp-ink); font-size: 18px; cursor: pointer; }
    .item-row .qty-control input { width: 70px; height: 42px; border: none; border-left: 1px solid var(--mp-border); border-right: 1px solid var(--mp-border); text-align: center; font-size: 15px; font-weight: 600; outline: none; }
    .item-row .available { font-size: 12px; color: var(--mp-muted); margin-top: 8px; }
    .total-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; font-size: 15px; font-weight: 600; color: var(--mp-ink); }
    .btn { display: block; width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-primary); border: 1px solid var(--mp-border); margin-top: 12px; }
    .empty-items { text-align: center; padding: 30px 20px; color: var(--mp-muted); font-size: 13px; }
    #toast { position: fixed; top: 16px; left: 50%; transform: translateX(-50%) translateY(-120%); max-width: 360px; width: calc(100% - 32px); padding: 14px 18px; border-radius: 14px; background: #0F172A; color: #fff; font-size: 14px; font-weight: 500; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000; opacity: 0; transition: all 0.3s ease; }
    #toast.active { transform: translateX(-50%) translateY(0); opacity: 1; }
    #toast.success { background: var(--mp-success); }
    #toast.error { background: var(--mp-danger); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/stock_transfers'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>New Stock Transfer</h1>
        </div>
      </div>

      <form id='stock-transfer-form' action='<?= base_url('mobile/save_stock_transfer'); ?>' method='post'>
        <input type='hidden' name='<?= $this->security->get_csrf_token_name(); ?>' value='<?= $this->security->get_csrf_hash(); ?>'>
        <input type='hidden' name='command' value='save'>
        <input type='hidden' id='rowcount' name='rowcount' value='1'>

        <div class='card'>
          <div class='form-group'>
            <label>Transfer Date <span class='req'>*</span></label>
            <input type='date' name='transfer_date' id='transfer_date' value='<?= $transfer_date; ?>' required>
          </div>
          <div class='form-row'>
            <div class='form-group'>
              <label>From Branch <span class='req'>*</span></label>
              <select class='mp-select' name='warehouse_from' id='warehouse_from'>
                <?= get_warehouse_select_list('', get_current_store_id(), true); ?>
              </select>
            </div>
            <div class='form-group'>
              <label>To Branch <span class='req'>*</span></label>
              <select class='mp-select' name='warehouse_to' id='warehouse_to'>
                <?= get_warehouse_select_list('', get_current_store_id(), true); ?>
              </select>
            </div>
          </div>
          <div class='form-group'>
            <label>Note</label>
            <textarea name='note' id='note' rows='2' placeholder='Optional note'><?= htmlspecialchars($note); ?></textarea>
          </div>
        </div>

        <div class='section-title'>Items</div>
        <div class='card'>
          <div class='search-wrap'>
            <input type='text' class='search-input' id='item-search' placeholder='Search product name, code or barcode' autocomplete='off'>
            <div class='search-results' id='search-results'></div>
          </div>
          <div id='items-empty' class='empty-items' style='margin-top: 16px;'>Search and add products to transfer.</div>
          <div id='items-list' style='margin-top: 16px;'></div>
          <div class='total-row' id='total-row' style='display:none;'>
            <span>Total Quantity</span>
            <span id='total-qty'>0</span>
          </div>
        </div>

        <button type='submit' class='btn btn-primary' id='btn-save'>Save Transfer</button>
        <a href='<?= base_url('mobile/stock_transfers'); ?>' class='btn btn-secondary'>Cancel</a>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <div id='toast'></div>

  <script>
    var nextIndex = 1;
    var rowCountInput = document.getElementById('rowcount');
    var itemsList = document.getElementById('items-list');
    var itemsEmpty = document.getElementById('items-empty');
    var totalRow = document.getElementById('total-row');
    var totalQtyEl = document.getElementById('total-qty');
    var searchInput = document.getElementById('item-search');
    var searchResults = document.getElementById('search-results');
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    var searchTimeout;

    function showToast(message, type){
      var toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = type === 'success' ? 'success' : 'error';
      toast.classList.add('active');
      setTimeout(function(){ toast.classList.remove('active'); }, 3000);
    }

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
          if(opt && opt.value){
            trigger.textContent = opt.textContent;
            trigger.classList.remove('placeholder');
          } else {
            trigger.textContent = opt ? opt.textContent : 'Select';
            trigger.classList.add('placeholder');
          }
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

    searchInput.addEventListener('input', function(){
      clearTimeout(searchTimeout);
      var term = this.value.trim();
      if(term.length < 2){
        searchResults.classList.remove('open');
        searchResults.innerHTML = '';
        return;
      }
      searchTimeout = setTimeout(function(){
        fetch('<?= base_url('stock_transfer/search_item'); ?>?q=' + encodeURIComponent(term))
        .then(function(res){ return res.json(); })
        .then(function(data){
          searchResults.innerHTML = '';
          if(!data || data.length === 0){
            searchResults.innerHTML = '<div class="search-empty">No products found.</div>';
          } else {
            data.forEach(function(item){
              var div = document.createElement('div');
              div.className = 'search-result';
              div.textContent = item.text;
              div.addEventListener('click', function(e){
                e.stopPropagation();
                addItem(item.id, item.text);
                searchInput.value = '';
                searchResults.innerHTML = '';
                searchResults.classList.remove('open');
              });
              searchResults.appendChild(div);
            });
          }
          searchResults.classList.add('open');
        })
        .catch(function(){
          searchResults.innerHTML = '<div class="search-empty">Search failed.</div>';
          searchResults.classList.add('open');
        });
      }, 300);
    });

    searchInput.addEventListener('focus', function(){
      if(this.value.trim().length >= 2) searchResults.classList.add('open');
    });

    function addItem(id, name){
      var warehouseFrom = document.getElementById('warehouse_from').value;
      if(!warehouseFrom){
        showToast('Select a From Branch first.', 'error');
        return;
      }
      var existing = document.querySelector('input[name^="tr_item_id_"][value="' + id + '"]');
      if(existing){
        var idx = existing.name.replace('tr_item_id_', '');
        var qtyInput = document.getElementById('td_data_' + idx + '_3');
        if(qtyInput){
          var max = parseFloat(qtyInput.max) || 0;
          var newQty = parseFloat(qtyInput.value || 0) + 1;
          if(max && newQty > max) newQty = max;
          qtyInput.value = newQty;
          updateTotal();
        }
        return;
      }

      var formData = new FormData();
      formData.append('id', id);
      formData.append('warehouse_id', warehouseFrom);
      formData.append(csrfName, csrfHash);

      fetch('<?= base_url('mobile/find_item_for_transfer'); ?>', {
        method: 'POST',
        body: formData
      })
      .then(function(res){ return res.json(); })
      .then(function(data){
        if(!data || data.id <= 0 || data.available_qty <= 0){
          showToast('Product has no available stock in the selected branch.', 'error');
          return;
        }
        var i = nextIndex++;
        rowCountInput.value = nextIndex - 1;
        var row = document.createElement('div');
        row.className = 'item-row';
        row.id = 'row_' + i;
        row.dataset.index = i;
        row.innerHTML =
          '<div class="item-header">' +
            '<div class="item-name">' + escapeHtml(data.item_name) + '</div>' +
            '<button type="button" class="remove" onclick="removeRow(' + i + ')"><i class="fa fa-trash"></i></button>' +
          '</div>' +
          '<div class="qty-row">' +
            '<label>Quantity</label>' +
            '<div class="qty-control">' +
              '<button type="button" onclick="changeQty(' + i + ', -1)">-</button>' +
              '<input type="number" name="td_data_' + i + '_3" id="td_data_' + i + '_3" value="1" min="0.01" max="' + data.available_qty + '" step="0.01" onchange="updateTotal()">' +
              '<button type="button" onclick="changeQty(' + i + ', 1)">+</button>' +
            '</div>' +
          '</div>' +
          '<div class="available">Available: ' + data.available_qty + '</div>' +
          '<input type="hidden" name="tr_item_id_' + i + '" value="' + data.id + '">';
        itemsList.appendChild(row);
        itemsEmpty.style.display = 'none';
        totalRow.style.display = 'flex';
        updateTotal();
      })
      .catch(function(){
        showToast('Could not load product details.', 'error');
      });
    }

    function changeQty(i, delta){
      var input = document.getElementById('td_data_' + i + '_3');
      if(!input) return;
      var max = parseFloat(input.max) || 0;
      var val = parseFloat(input.value || 0) + delta;
      if(val < 0.01) val = 0.01;
      if(max && val > max) val = max;
      input.value = val;
      updateTotal();
    }

    function removeRow(i){
      var row = document.getElementById('row_' + i);
      if(row) row.remove();
      if(itemsList.children.length === 0){
        itemsEmpty.style.display = 'block';
        totalRow.style.display = 'none';
      }
      updateTotal();
    }

    function updateTotal(){
      var total = 0;
      document.querySelectorAll('#items-list .item-row').forEach(function(row){
        var i = row.dataset.index;
        var input = document.getElementById('td_data_' + i + '_3');
        if(input) total += parseFloat(input.value || 0);
      });
      totalQtyEl.textContent = total.toFixed(2);
    }

    function escapeHtml(text){
      var div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    document.getElementById('stock-transfer-form').addEventListener('submit', function(e){
      e.preventDefault();
      var btn = document.getElementById('btn-save');
      var warehouseFrom = document.getElementById('warehouse_from').value;
      var warehouseTo = document.getElementById('warehouse_to').value;
      if(!warehouseFrom || !warehouseTo){
        showToast('Select both From and To branches.', 'error');
        return;
      }
      if(warehouseFrom === warehouseTo){
        showToast('From and To branches cannot be the same.', 'error');
        return;
      }
      if(itemsList.children.length === 0){
        showToast('Add at least one product to transfer.', 'error');
        return;
      }
      btn.disabled = true;
      btn.textContent = 'Saving...';

      var formData = new FormData(this);
      fetch('<?= base_url('mobile/save_stock_transfer'); ?>', {
        method: 'POST',
        body: formData
      })
      .then(function(res){ return res.json(); })
      .then(function(data){
        btn.disabled = false;
        btn.textContent = 'Save Transfer';
        if(data.status === 'success'){
          showToast(data.message, 'success');
          setTimeout(function(){ window.location.href = data.redirect || '<?= base_url('mobile/stock_transfers'); ?>'; }, 800);
        } else {
          showToast(data.message || 'Save failed.', 'error');
        }
      })
      .catch(function(){
        btn.disabled = false;
        btn.textContent = 'Save Transfer';
        showToast('Network or server error. Try again.', 'error');
      });
    });
  </script>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
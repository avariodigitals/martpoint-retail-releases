<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Catalogue</title>
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
    .price-type { display: flex; flex-wrap: nowrap; border: 1px solid var(--mp-border); border-radius: 14px; overflow: hidden; background: var(--mp-surface); margin-bottom: 12px; }
    .price-type button { flex: 1; min-width: 0; padding: 12px 6px; border: none; border-left: 1px solid var(--mp-border); border-radius: 0; background: var(--mp-surface); color: var(--mp-ink); font-size: 14px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .price-type button:first-child { border-left: none; border-top-left-radius: 14px; border-bottom-left-radius: 14px; }
    .price-type button:last-child { border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
    .price-type button.active { background: var(--mp-primary); color: #fff; border-left-color: var(--mp-primary); }
    .price-type button.active + button { border-left-color: var(--mp-primary); }
    .filter-form { margin-bottom: 12px; }
    .filter-bar { display: flex; flex-direction: column; gap: 10px; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .search-bar .submit-btn { border: none; background: var(--mp-primary); color: #fff; border-radius: 10px; padding: 8px 16px; font-size: 14px; font-weight: 600; cursor: pointer; }
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
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .cat-item { padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .cat-item:last-child { border-bottom: none; }
    .cat-item .name { font-weight: 700; font-size: 15px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
    .cat-item .meta { font-size: 12px; color: var(--mp-muted); line-height: 1.4; }
    .cat-item .row { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-top: 10px; }
    .cat-item .left { flex: 1; min-width: 0; }
    .cat-item .right { flex: 0 0 130px; text-align: right; }
    .cat-item .price { font-weight: 700; font-size: 18px; color: var(--mp-primary); }
    .cat-item .price-sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .cat-item .online-price { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .cat-item .stock { font-size: 12px; color: var(--mp-muted); margin-top: 6px; }
    .cat-item .edit-btn { display: inline-block; width: 100%; text-align: center; padding: 8px 0; border-radius: 10px; background: var(--mp-primary); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; margin-top: 8px; }
    .badge { display: inline-block; font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.variants { background: #E0E7FF; color: var(--mp-primary); }
    .load-more { display: block; width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--mp-border); background: #fff; color: var(--mp-primary); font-size: 15px; font-weight: 600; text-align: center; text-decoration: none; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/more'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Catalogue</h1>
        </div>
      </div>

      <form class='filter-form' id='catalogueForm' method='get' action='<?= base_url('mobile/catalogue'); ?>'>
        <input type='hidden' name='price_type' id='price_type' value='<?= htmlspecialchars($price_type); ?>'>
        <div class='price-type' id='price_type_chips'>
          <button type='button' data-value='retail' <?= ($price_type === 'retail') ? 'class="active"' : ''; ?>>Retail (MRP)</button>
          <button type='button' data-value='wholesale' <?= ($price_type !== 'retail') ? 'class="active"' : ''; ?>>Wholesale</button>
        </div>
        <div class='filter-bar'>
          <div class='search-bar'>
            <i class='fa fa-search'></i>
            <input type='search' name='search' value='<?= htmlspecialchars($search ?? ''); ?>' placeholder='Search name, code or barcode'>
            <button type='submit' class='submit-btn'>Go</button>
          </div>
          <div class='select-group'>
            <select class='mp-select' id='categoryFilter' name='category'>
              <option value='' <?= empty($category_id) ? 'selected' : ''; ?>>All Categories</option>
              <?php foreach($categories as $cat): ?>
                <option value='<?= (int)$cat->id; ?>' <?= ($category_id == (int)$cat->id) ? 'selected' : ''; ?>><?= htmlspecialchars($cat->category_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </form>

      <div class='card cat-list'>
        <?php if(!empty($products)): ?>
          <?php foreach($products as $item):
            $mrp = (float)($item->mrp ?? 0);
            $sales = (float)$item->sales_price;
            if($price_type === 'retail'){
              $main_price = $mrp > 0 ? $mrp : $sales;
              $main_label = 'Retail';
              $alt_price = $sales;
              $alt_label = 'Wholesale';
            } else {
              $main_price = $sales;
              $main_label = 'Wholesale';
              $alt_price = $mrp;
              $alt_label = 'Retail';
            }
          ?>
            <div class='cat-item'>
              <div class='left'>
                <div class='name'>
                  <?= htmlspecialchars($item->item_name); ?>
                  <?php if($item->item_group == 'Variants'): ?>
                    <span class='badge variants'>Variants</span>
                  <?php endif; ?>
                </div>
                <div class='meta'>
                  <?= htmlspecialchars($item->item_code ?: '-'); ?>
                  <?= !empty($item->custom_barcode) ? ' · ' . htmlspecialchars($item->custom_barcode) : ''; ?>
                  · <?= htmlspecialchars($item->category_name ?: 'No category'); ?>
                  <?= !empty($item->brand_name) ? ' · ' . htmlspecialchars($item->brand_name) : ''; ?>
                </div>
              </div>
              <div class='row'>
                <div class='left'>
                  <div class='stock'><?= number_format((float)$item->stock, 0); ?> in stock</div>
                </div>
                <div class='right'>
                  <div class='price'><?= store_number_format($main_price); ?></div>
                  <?php if($alt_price > 0 && $alt_price != $main_price): ?>
                    <div class='price-sub'><?= $alt_label; ?>: <?= store_number_format($alt_price); ?></div>
                  <?php endif; ?>
                  <?php if((float)$item->online_price > 0): ?>
                    <div class='online-price'>Online: <?= store_number_format($item->online_price); ?></div>
                  <?php endif; ?>
                  <?php if(permissions('items_edit')): ?>
                    <a href='<?= base_url('mobile/product/' . (int)$item->id); ?>' class='edit-btn'>Edit Product</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class='empty-state'>
            <i class='fa fa-book'></i>
            <div>No products found.</div>
          </div>
        <?php endif; ?>
      </div>

      <?php if(!empty($has_more)): ?>
        <?php
          $query = http_build_query(array_filter([
            'search' => $search ?? '',
            'category' => $category_id ?? '',
            'price_type' => $price_type ?? ''
          ]));
          $next_url = base_url('mobile/catalogue/' . ($page + 1) . ($query ? '?' . $query : ''));
        ?>
        <a href='<?= $next_url; ?>' class='load-more'>Load more</a>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    (function(){
      var form = document.getElementById('catalogueForm');

      // Price type toggle: update hidden input and submit the form so the price display refreshes.
      var chips = document.getElementById('price_type_chips');
      if(chips){
        chips.addEventListener('click', function(e){
          var btn = e.target.closest('button[data-value]');
          if(!btn) return;
          [].forEach.call(chips.querySelectorAll('button'), function(b){ b.classList.remove('active'); });
          btn.classList.add('active');
          document.getElementById('price_type').value = btn.getAttribute('data-value');
          form.submit();
        });
      }

      // Custom in-app select (no native dropdown shoot-out).
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
              // Submit the filter form so the catalogue actually filters.
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
          if(opt && opt.value){
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

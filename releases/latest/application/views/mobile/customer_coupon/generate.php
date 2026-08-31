<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Create Customer Coupon</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .form-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .req { color: var(--mp-danger); }
    .form-control { width: 100%; padding: 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: var(--mp-surface); outline: none; min-height: 52px; }
    .form-control:focus { border-color: var(--mp-primary); }
    textarea.form-control { min-height: 90px; resize: vertical; }
    .form-control[readonly] { background: var(--mp-bg); color: var(--mp-muted); }
    .input-row { display: flex; gap: 10px; }
    .input-row .form-control { flex: 1; }
    .input-row button { width: 52px; border: 1px solid var(--mp-border); border-radius: 12px; background: var(--mp-surface); color: var(--mp-primary); font-size: 18px; }
    .customer-search { position: relative; }
    .customer-results { display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; z-index: 50; box-shadow: 0 10px 25px rgba(0,0,0,0.12); }
    .customer-results.open { display: block; }
    .customer-result { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .customer-result:last-child { border-bottom: none; }
    .customer-result .name { font-weight: 600; font-size: 14px; }
    .customer-result .meta { font-size: 12px; color: var(--mp-muted); }
    .customer-result:active { background: #EFF6FF; }
    .customer-selected { margin-top: 4px; font-size: 13px; color: var(--mp-ink); }
    .mp-select { position: relative; }
    .mp-select .trigger { width: 100%; padding: 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: var(--mp-surface); display: flex; align-items: center; justify-content: space-between; font-size: 15px; color: var(--mp-ink); cursor: pointer; min-height: 52px; }
    .mp-select .trigger .placeholder { color: var(--mp-muted); }
    .mp-select .trigger i { color: var(--mp-muted); }
    .mp-select .options { display: none; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; max-height: 240px; overflow-y: auto; margin-top: 6px; z-index: 20; box-shadow: 0 10px 25px rgba(0,0,0,0.12); }
    .mp-select.open .options { display: block; }
    .mp-select .option { padding: 12px 14px; font-size: 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .mp-select .option:last-child { border-bottom: none; }
    .mp-select .option.selected { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    .mp-select .hidden-select { position: absolute; opacity: 0; pointer-events: none; }
    .btn-primary { display: block; width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn-primary:disabled { opacity: 0.6; }
    .field-hint { font-size: 12px; color: var(--mp-muted); margin-top: -10px; margin-bottom: 12px; }
    .error-msg { color: var(--mp-danger); font-size: 12px; display: none; margin-top: 2px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/marketing'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Create Customer Coupon</h1>
        </div>
      </div>

      <form class="form-card" id="coupon-form">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="base_url" value="<?= base_url(); ?>">
        <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" name="command" value="save">

        <div class="form-group">
          <label>Customer <span class="req">*</span></label>
          <div class="customer-search">
            <input type="text" class="form-control" id="customer_search" placeholder="Search customer name or mobile" autocomplete="off">
            <input type="hidden" name="customer_id" id="customer_id" value="<?= htmlspecialchars($customer_id ?? ''); ?>">
            <div class="customer-results" id="customer_results"></div>
          </div>
          <div class="customer-selected" id="customer_selected" style="display:none;"></div>
          <div class="error-msg" id="customer_id_msg">Please select a customer.</div>
        </div>

        <div class="form-group">
          <label>Coupon <span class="req">*</span></label>
          <div class="mp-select" id="coupon_select">
            <select class="hidden-select" name="coupon_id" id="coupon_id">
              <option value="">Select coupon</option>
              <?php foreach($coupons as $c): ?>
                <option value="<?= (int)$c->id; ?>" data-expire-date="<?= show_date($c->expire_date); ?>" data-expire-iso="<?= $c->expire_date; ?>" data-coupon-type="<?= htmlspecialchars($c->type); ?>" data-coupon-value="<?= (float)$c->value; ?>"><?= htmlspecialchars($c->name); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="trigger" id="coupon_trigger">
              <span class="placeholder">Select coupon</span>
              <i class="fa fa-chevron-down"></i>
            </div>
            <div class="options" id="coupon_options">
              <div class="option" data-value="">Select coupon</div>
              <?php foreach($coupons as $c): ?>
                <div class="option" data-value="<?= (int)$c->id; ?>"><?= htmlspecialchars($c->name); ?></div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="error-msg" id="coupon_id_msg">Please select a coupon.</div>
        </div>

        <div class="form-group">
          <label>Coupon Code <span class="req">*</span></label>
          <div class="input-row">
            <input type="text" class="form-control" name="code" id="code" placeholder="Enter or generate code">
            <button type="button" id="generate_code" title="Generate code"><i class="fa fa-refresh"></i></button>
          </div>
          <div class="error-msg" id="code_msg">Please enter a coupon code.</div>
        </div>

        <div class="form-group">
          <label>Expire Date</label>
          <input type="date" class="form-control" name="expire_date" id="expire_date" style="padding:12px 14px;">
        </div>

        <div class="form-group">
          <label>Coupon Value</label>
          <input type="text" class="form-control" name="coupon_value" id="coupon_value" readonly>
        </div>

        <div class="form-group">
          <label>Coupon Type</label>
          <input type="text" class="form-control" name="coupon_type" id="coupon_type" readonly>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea class="form-control" name="description" id="description" placeholder="Optional note"></textarea>
        </div>

        <button type="button" class="btn-primary" id="save_btn">Generate Coupon</button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    (function($){
      var base_url = '<?= base_url(); ?>';
      var customerSearchTimer = null;
      var preloadedCustomerId = '<?= htmlspecialchars($customer_id ?? ''); ?>';

      function showFieldError(id, show){
        $('#' + id + '_msg').toggle(show);
      }

      function setCouponDetails(){
        var $sel = $('#coupon_id option:selected');
        var expire = $sel.attr('data-expire-iso') || '';
        var value = $sel.attr('data-coupon-value') || '';
        var type = $sel.attr('data-coupon-type') || '';
        $('#expire_date').val(expire);
        $('#coupon_value').val(value ? mpFormatNumber(value, true) : '');
        $('#coupon_type').val(type);
      }

      // Customer search
      function renderCustomerResults(items){
        var $list = $('#customer_results').empty();
        if(!items || !items.length){
          $list.append('<div class="customer-result"><div class="name">No customers found</div></div>');
        } else {
          $.each(items, function(i, c){
            var meta = [c.mobile, c.customer_code].filter(Boolean).join(' · ');
            var $row = $('<div class="customer-result"></div>');
            $row.append('<div class="name">' + escapeHtml(c.customer_name) + '</div>');
            if(meta) $row.append('<div class="meta">' + escapeHtml(meta) + '</div>');
            $row.on('click', function(){
              $('#customer_id').val(c.id);
              $('#customer_search').val(c.customer_name);
              $('#customer_selected').text('Selected: ' + c.customer_name).show();
              $('#customer_results').removeClass('open');
              showFieldError('customer_id', false);
            });
            $list.append($row);
          });
        }
        $list.addClass('open');
      }

      function searchCustomers(term){
        $.getJSON(base_url + 'mobile/customer_search', { q: term }, function(data){
          renderCustomerResults(data);
        }).fail(function(){
          $('#customer_results').empty().append('<div class="customer-result"><div class="name">Search failed</div></div>').addClass('open');
        });
      }

      function preloadCustomer(id){
        if(!id) return;
        $.post(base_url + 'customers/getCustomers/' + id, { store_id: $('#store_id').val() }, function(data){
          if(data && data.length){
            var c = data[0];
            $('#customer_id').val(c.id);
            $('#customer_search').val(c.customer_name);
            $('#customer_selected').text('Selected: ' + c.customer_name).show();
          }
        }, 'json').fail(function(){
          $('#customer_search').attr('placeholder', 'Search customer name or mobile');
        });
      }

      $('#customer_search').on('input', function(){
        var term = $(this).val().trim();
        clearTimeout(customerSearchTimer);
        if(term.length < 1){
          $('#customer_results').removeClass('open').empty();
          return;
        }
        customerSearchTimer = setTimeout(function(){ searchCustomers(term); }, 250);
      });

      $(document).on('click', function(e){
        if(!$(e.target).closest('.customer-search').length){
          $('#customer_results').removeClass('open');
        }
      });

      // Coupon custom select
      var $couponSelect = $('#coupon_select');
      $('#coupon_trigger').on('click', function(e){
        e.stopPropagation();
        $('.customer-results').removeClass('open');
        $('.mp-select').not($couponSelect).removeClass('open');
        $couponSelect.toggleClass('open');
      });

      $('#coupon_options .option').on('click', function(){
        var val = $(this).data('value');
        var text = $(this).text();
        $('#coupon_id').val(val).trigger('change');
        $('#coupon_trigger .placeholder').replaceWith('<span>' + escapeHtml(text) + '</span>');
        $('#coupon_select .option').removeClass('selected');
        $(this).addClass('selected');
        $couponSelect.removeClass('open');
        showFieldError('coupon_id', false);
      });

      $(document).on('click', function(e){
        if(!$(e.target).closest('#coupon_select').length){
          $couponSelect.removeClass('open');
        }
      });

      $('#coupon_id').on('change', setCouponDetails);

      // Generate code
      function generateCode(){
        var number = Math.random().toString().slice(2,16);
        $('#code').val(number);
        showFieldError('code', false);
      }
      $('#generate_code').on('click', generateCode);

      // Save
      $('#save_btn').on('click', function(){
        var valid = true;
        if(!$('#customer_id').val()){ showFieldError('customer_id', true); valid = false; }
        if(!$('#coupon_id').val()){ showFieldError('coupon_id', true); valid = false; }
        if(!$('#code').val().trim()){ showFieldError('code', true); valid = false; }
        if(!valid){
          mpAlert('Please fill all required fields.', 'warning');
          return;
        }

        var $btn = $(this).attr('disabled', true);
        var formData = new FormData($('#coupon-form')[0]);

        $.ajax({
          type: 'POST',
          url: base_url + 'customer_coupon/save?command=save',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success: function(result){
            $btn.attr('disabled', false);
            if(result === 'success'){
              mpSuccess('Customer coupon created successfully.');
              if(window.parent && window.parent !== window && typeof window.parent.closePopup === 'function'){
                setTimeout(function(){ window.parent.closePopup(); }, 600);
              } else {
                setTimeout(function(){ window.location.href = base_url + 'mobile/marketing'; }, 900);
              }
            } else {
              mpAlert(result, 'danger');
            }
          },
          error: function(){
            $btn.attr('disabled', false);
            mpError('Failed to save. Please try again.');
          }
        });
      });

      // Preload customer if set
      if(preloadedCustomerId){
        preloadCustomer(preloadedCustomerId);
      }

    })(jQuery);
  </script>
</body>
</html>

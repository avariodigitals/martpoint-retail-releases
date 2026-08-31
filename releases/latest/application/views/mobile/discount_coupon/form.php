<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= $page_title; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: clamp(18px, 5vw, 22px); font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .form-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .req { color: var(--mp-danger); }
    .form-control { width: 100%; padding: 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: var(--mp-surface); outline: none; min-height: 52px; }
    .form-control:focus { border-color: var(--mp-primary); }
    textarea.form-control { min-height: 90px; resize: vertical; }
    .choice-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .choice { padding: 10px 14px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; font-weight: 600; color: var(--mp-ink); cursor: pointer; }
    .choice input { position: absolute; opacity: 0; }
    .choice:has(input:checked) { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .error-msg { color: var(--mp-danger); font-size: 12px; display: none; }
    .btn-primary { display: block; width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn-primary:disabled { opacity: 0.6; }
    @media (min-width: 600px) { .screen { padding: 16px 24px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/discount_coupon/view'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <form class="form-card" id="coupon-form">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" name="command" value="<?= $coupon->id ? 'update' : 'save'; ?>">
        <?php if($coupon->id): ?><input type="hidden" name="q_id" value="<?= (int)$coupon->id; ?>"><?php endif; ?>

        <div class="form-group">
          <label>Coupon Name <span class="req">*</span></label>
          <input type="text" name="coupon_name" class="form-control" value="<?= htmlspecialchars($coupon->name); ?>" placeholder="e.g. Summer Sale">
          <div class="error-msg" id="coupon_name_msg">Please enter a coupon name.</div>
        </div>

        <div class="form-group">
          <label>Expire Date <span class="req">*</span></label>
          <input type="date" name="expire_date" class="form-control" value="<?= $coupon->expire_date ?: ''; ?>">
          <div class="error-msg" id="expire_date_msg">Please select an expiry date.</div>
        </div>

        <div class="form-group">
          <label>Coupon Value <span class="req">*</span></label>
          <input type="number" step="0.01" min="0" name="coupon_value" class="form-control" value="<?= $coupon->value; ?>" placeholder="0.00">
          <div class="error-msg" id="coupon_value_msg">Please enter a value.</div>
        </div>

        <div class="form-group">
          <label>Coupon Type <span class="req">*</span></label>
          <div class="choice-group">
            <label class="choice"><input type="radio" name="coupon_type" value="Percentage" <?= ($coupon->type ?? 'Percentage') === 'Percentage' ? 'checked' : ''; ?>>Percentage</label>
            <label class="choice"><input type="radio" name="coupon_type" value="Amount" <?= ($coupon->type ?? '') === 'Amount' ? 'checked' : ''; ?>>Amount</label>
          </div>
          <div class="error-msg" id="coupon_type_msg">Please select a type.</div>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-control" placeholder="Optional note"><?= htmlspecialchars($coupon->description ?? ''); ?></textarea>
        </div>

        <button type="button" class="btn-primary" id="save_btn"><?= $coupon->id ? 'Update' : 'Save'; ?></button>
      </form>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    (function($){
      var base_url = '<?= base_url(); ?>';
      function showFieldError(id, show){ $('#' + id + '_msg').toggle(show); }
      $('#save_btn').on('click', function(){
        var valid = true;
        if(!$('input[name="coupon_name"]').val().trim()){ showFieldError('coupon_name', true); valid = false; } else { showFieldError('coupon_name', false); }
        if(!$('input[name="expire_date"]').val()){ showFieldError('expire_date', true); valid = false; } else { showFieldError('expire_date', false); }
        if(!$('input[name="coupon_value"]').val()){ showFieldError('coupon_value', true); valid = false; } else { showFieldError('coupon_value', false); }
        if(!$('input[name="coupon_type"]:checked').val()){ showFieldError('coupon_type', true); valid = false; } else { showFieldError('coupon_type', false); }
        if(!valid){ mpAlert('Please fill all required fields.', 'warning'); return; }

        var $btn = $(this).attr('disabled', true);
        var formData = new FormData($('#coupon-form')[0]);
        $.ajax({
          type: 'POST',
          url: base_url + 'discount_coupon/save',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success: function(result){
            $btn.attr('disabled', false);
            if(result === 'success'){
              mpSuccess('Coupon saved successfully.');
              setTimeout(function(){ window.location.href = base_url + 'mobile/discount_coupon/view'; }, 900);
            } else {
              mpAlert(result, 'danger');
            }
          },
          error: function(){
            $btn.attr('disabled', false);
            mpAlert('Failed to save. Please try again.', 'danger');
          }
        });
      });
    })(jQuery);
  </script>
</body>
</html>

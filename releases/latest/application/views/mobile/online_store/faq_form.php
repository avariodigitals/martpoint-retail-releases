<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= $faq ? 'Edit FAQ' : 'Add FAQ'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .form-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: 13px; font-weight: 600; color: var(--mp-ink); margin-bottom: 6px; }
    .field input, .field textarea { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 15px; outline: none; }
    .field input:focus, .field textarea:focus { border-color: var(--mp-primary); }
    .field textarea { min-height: 120px; resize: vertical; }
    .field-row { display: flex; gap: 12px; }
    .field-row .field { flex: 1; }
    .toggle { display: flex; align-items: center; gap: 10px; }
    .toggle input { width: 20px; height: 20px; }
    .btn { width: 100%; padding: 14px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-size: 15px; font-weight: 600; cursor: pointer; }
    .btn:disabled { opacity: 0.6; }
    .error { color: var(--mp-danger); font-size: 13px; margin-top: 6px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/online_store/faqs'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $faq ? 'Edit FAQ' : 'Add FAQ'; ?></h1>
        </div>
      </div>

      <form class="form-card" id="faq-form">
        <input type="hidden" name="faq_id" value="<?= $faq ? (int)$faq->id : 0; ?>">
        <?php if(function_exists('csrf_token') || $this->security): ?>
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <?php endif; ?>

        <div class="field">
          <label for="question">Question</label>
          <input type="text" id="question" name="question" value="<?= htmlspecialchars($faq->question ?? ''); ?>" placeholder="e.g. Do you deliver?" required>
        </div>

        <div class="field">
          <label for="answer">Answer</label>
          <textarea id="answer" name="answer" placeholder="Write the answer..." required><?= htmlspecialchars($faq->answer ?? ''); ?></textarea>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="sort_order">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" value="<?= (int)($faq->sort_order ?? 0); ?>" min="0" required>
          </div>
          <div class="field">
            <label>Enabled</label>
            <label class="toggle">
              <input type="checkbox" name="is_enabled" value="1" <?= (($faq->is_enabled ?? 1) == 1) ? 'checked' : ''; ?>>
              <span>Show on storefront</span>
            </label>
          </div>
        </div>

        <button type="submit" class="btn" id="save-btn">Save FAQ</button>
        <div class="error" id="form-error"></div>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    document.getElementById('faq-form').addEventListener('submit', function(e){
      e.preventDefault();
      var btn = document.getElementById('save-btn');
      btn.disabled = true;
      btn.textContent = 'Saving...';
      document.getElementById('form-error').textContent = '';

      var fd = new FormData(this);
      fetch('<?= base_url('online_store/save_faq'); ?>', {
        method: 'POST',
        body: fd
      })
      .then(r => r.json())
      .then(d => {
        if(d && d.status === 'success'){
          window.location.href = '<?= base_url('mobile/online_store/faqs'); ?>';
        } else {
          btn.disabled = false;
          btn.textContent = 'Save FAQ';
          document.getElementById('form-error').textContent = d && d.message ? d.message : 'Save failed';
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Save FAQ';
        document.getElementById('form-error').textContent = 'Save failed. Please try again.';
      });
    });
  </script>
</body>
</html>
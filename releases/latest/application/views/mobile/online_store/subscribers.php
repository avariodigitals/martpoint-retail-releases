<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Subscribers</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
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
    .topbar .add { padding: 9px 14px; border-radius: 10px; background: var(--mp-primary); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; }
    .summary-card { background: linear-gradient(135deg, var(--mp-primary), var(--mp-primary-dark)); border-radius: 16px; padding: 18px; margin-bottom: 16px; color: #fff; }
    .summary-card .num { font-size: 30px; font-weight: 800; }
    .summary-card .lbl { font-size: 12px; opacity: 0.85; margin-top: 2px; }
    .sub-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 14px 16px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); display: flex; align-items: center; gap: 12px; }
    .sub-icon { width: 38px; height: 38px; border-radius: 50%; background: var(--mp-bg); color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    .sub-info { flex: 1; min-width: 0; }
    .sub-email { font-size: 14px; font-weight: 600; color: var(--mp-ink); word-break: break-all; }
    .sub-meta { font-size: 11px; color: var(--mp-muted); margin-top: 3px; }
    .sub-del { color: var(--mp-danger); font-size: 16px; text-decoration: none; padding: 6px; }
    .hint { font-size: 12px; color: var(--mp-muted); text-align: center; padding: 4px 12px 14px; line-height: 1.5; }
    .empty { text-align: center; padding: 60px 24px; color: var(--mp-muted); font-size: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Subscribers</h1>
        </div>
        <a href="<?= base_url('online_store/export_subscribers'); ?>" class="add"><i class="fa fa-download"></i> CSV</a>
      </div>

      <div class="summary-card">
        <div class="num"><?= (int)$total_subscribers; ?></div>
        <div class="lbl">Newsletter subscribers</div>
      </div>

      <?php if(!empty($subscribers)): ?>
        <p class="hint">Customers who opted in on your storefront. Export the CSV to use with any email or WhatsApp marketing tool.</p>
        <?php foreach($subscribers as $s): ?>
          <div class="sub-card" data-id="<?= (int)$s->id; ?>">
            <div class="sub-icon"><i class="fa fa-envelope-o"></i></div>
            <div class="sub-info">
              <div class="sub-email"><?= htmlspecialchars($s->email); ?></div>
              <div class="sub-meta"><?= htmlspecialchars($s->source ?: 'newsletter'); ?> · <?= !empty($s->created_at) ? date('M j, Y', strtotime($s->created_at)) : ''; ?></div>
            </div>
            <?php if($can_edit): ?>
              <a href="#" class="sub-del" onclick="deleteSubscriber(<?= (int)$s->id; ?>, event);"><i class="fa fa-trash"></i></a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No subscribers yet.<br>Enable the Newsletter section on your storefront homepage to start collecting emails.</div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function deleteSubscriber(id, e){
      e.preventDefault();
      if(!confirm('Remove this subscriber?')) return;
      var fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fetch('<?= base_url('online_store/delete_subscriber/'); ?>' + id, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
          if(d && d.status === 'success'){
            document.querySelector('[data-id="' + id + '"]').remove();
          } else {
            alert(d && d.message ? d.message : 'Delete failed');
          }
        })
        .catch(() => alert('Delete failed. Please try again.'));
    }
  </script>
</body>
</html>

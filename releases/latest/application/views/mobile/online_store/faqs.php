<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — FAQs</title>
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
    .faq-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .faq-question { font-size: 15px; font-weight: 700; margin: 0 0 6px; }
    .faq-answer { font-size: 13px; color: var(--mp-muted); line-height: 1.45; margin: 0 0 12px; }
    .faq-meta { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 12px; color: var(--mp-muted); }
    .faq-actions { display: flex; gap: 10px; }
    .faq-actions a { color: var(--mp-primary); font-size: 13px; font-weight: 600; text-decoration: none; }
    .faq-actions a.delete { color: var(--mp-danger); }
    .badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; }
    .badge-enabled { background: #D1FAE5; color: #065F46; }
    .badge-disabled { background: #FEF2F2; color: #991B1B; }
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
          <h1>FAQs</h1>
        </div>
        <?php if($can_edit): ?>
          <a href="<?= base_url('mobile/online_store/faq_form'); ?>" class="add">+ New</a>
        <?php endif; ?>
      </div>

      <?php if(!empty($faqs)): ?>
        <?php foreach($faqs as $f): ?>
          <div class="faq-card" data-id="<?= (int)$f->id; ?>">
            <div class="faq-question"><?= htmlspecialchars($f->question); ?></div>
            <div class="faq-answer"><?= nl2br(htmlspecialchars($f->answer)); ?></div>
            <div class="faq-meta">
              <div>
                <span class="badge <?= $f->is_enabled ? 'badge-enabled' : 'badge-disabled'; ?>"><?= $f->is_enabled ? 'Enabled' : 'Disabled'; ?></span>
                <span style="margin-left:8px;">Order <?= (int)$f->sort_order; ?></span>
              </div>
              <?php if($can_edit): ?>
                <div class="faq-actions">
                  <a href="<?= base_url('mobile/online_store/faq_form?id=' . $f->id); ?>">Edit</a>
                  <a href="#" class="delete" onclick="deleteFaq(<?= (int)$f->id; ?>, event);">Delete</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No FAQs yet. Tap + to add one.</div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function deleteFaq(id, e){
      e.preventDefault();
      if(!confirm('Delete this FAQ?')) return;
      fetch('<?= base_url('online_store/delete_faq/'); ?>' + id, { method: 'POST' })
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
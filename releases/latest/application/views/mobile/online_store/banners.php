<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Banners</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .add-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 12px; background: var(--mp-primary); color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 16px; }
    .banner-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .banner-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .banner-title { font-size: 16px; font-weight: 700; }
    .banner-type { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
    .type-hero { background: #DBEAFE; color: #1E40AF; }
    .type-promo { background: #FFEDD5; color: #9A3412; }
    .banner-sub { font-size: 13px; color: var(--mp-muted); margin-bottom: 12px; }
    .banner-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
    .banner-label { color: var(--mp-muted); }
    .banner-actions { display: flex; gap: 10px; margin-top: 14px; }
    .banner-actions a, .banner-actions button { flex: 1; text-align: center; padding: 10px; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none; }
    .btn-edit { background: #EFF6FF; color: var(--mp-primary); }
    .btn-del { background: #FEF2F2; color: #DC2626; }
    .status-active { background: #D1FAE5; color: #065F46; }
    .status-inactive { background: #F1F5F9; color: #475569; }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
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
          <h1>Banners</h1>
        </div>
      </div>

      <a href="<?= base_url('mobile/online_store/banner_form'); ?>" class="add-btn"><i class="fa fa-plus"></i> Add Banner</a>

      <?php if(!empty($banners)): ?>
        <?php foreach($banners as $b): ?>
          <div class="banner-card">
            <div class="banner-header">
              <span class="banner-title"><?= htmlspecialchars($b->banner_title); ?></span>
              <span class="banner-type <?= $b->banner_type == 'hero' ? 'type-hero' : 'type-promo'; ?>"><?= $b->banner_type == 'hero' ? 'Hero' : 'Promo'; ?></span>
            </div>
            <div class="banner-sub"><?= htmlspecialchars($b->banner_subtitle); ?></div>
            <div class="banner-row">
              <span class="banner-label">Order</span>
              <span class="banner-value"><?= (int)$b->display_order; ?></span>
            </div>
            <div class="banner-row">
              <span class="banner-label">Status</span>
              <span class="banner-type <?= $b->status ? 'status-active' : 'status-inactive'; ?>"><?= $b->status ? 'Active' : 'Inactive'; ?></span>
            </div>
            <div class="banner-row">
              <span class="banner-label">Dates</span>
              <span class="banner-value"><?= $b->start_date ?: 'Always'; ?> to <?= $b->end_date ?: 'Always'; ?></span>
            </div>
            <div class="banner-actions">
              <a href="<?= base_url('mobile/online_store/banner_form/' . $b->id); ?>" class="btn-edit">Edit</a>
              <?php if($can_edit): ?>
                <button type="button" class="btn-del" onclick="deleteBanner(<?= $b->id; ?>, this)" style="border:none;">Delete</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No banners yet.</div>
      <?php endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function deleteBanner(id, btn){
      if(!confirm('Delete this banner?')) return;
      btn.disabled = true;
      const fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fetch('<?= base_url('online_store/delete_banner/'); ?>' + id, {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ location.reload(); }
        else { alert(res.message || 'Failed to delete'); btn.disabled = false; }
      }).catch(()=>{ alert('Error deleting'); btn.disabled = false; });
    }
  </script>
</body>
</html>

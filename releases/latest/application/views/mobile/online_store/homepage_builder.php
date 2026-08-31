<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Homepage Builder</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 700; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .section-row { display: flex; align-items: center; gap: 12px; padding: 16px; border-bottom: 1px solid var(--mp-border); background: #fff; touch-action: none; }
    .section-row:last-child { border-bottom: none; }
    .section-row.disabled { background: #F8FAFC; }
    .section-row.sortable-ghost { opacity: .4; background: #DBEAFE; }
    .section-handle { font-size: 18px; color: #94A3B8; cursor: grab; }
    .section-name { flex: 1; font-weight: 600; font-size: 15px; }
    .section-row.disabled .section-name { color: var(--mp-muted); }
    .section-badge { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: #DBEAFE; color: #1E40AF; }
    .section-actions { display: flex; align-items: center; gap: 10px; }
    .section-actions button { background: none; border: none; color: var(--mp-primary); font-size: 13px; font-weight: 600; cursor: pointer; }
    .section-actions input { width: 18px; height: 18px; }
    .guide { font-size: 13px; color: var(--mp-muted); line-height: 1.5; }
    .guide p { margin: 0 0 10px; }
    .guide strong { color: var(--mp-ink); }
    .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-weight: 600; font-size: 15px; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .link { color: var(--mp-primary); text-decoration: none; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Homepage Builder</h1>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Sections <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="link" style="float:right;font-size:13px;"><i class="fa fa-eye"></i> Preview</a></div>
        <div id="sections-container">
          <?php
            $duplicable = ['hero_banner','promo_banner','featured_products','featured_services','featured_categories','testimonials','brands','instagram_gallery'];
            foreach($homepage_sections as $s):
              $isDup = in_array($s->section_key, $duplicable) || preg_match('/^('.implode('|',$duplicable).')_\d+$/', $s->section_key);
          ?>
            <div class="section-row <?= $s->is_enabled ? '' : 'disabled'; ?>" data-key="<?= $s->section_key; ?>" data-order="<?= $s->display_order; ?>">
              <span class="section-handle">&#9776;</span>
              <span class="section-name"><?= htmlspecialchars($s->section_label); ?></span>
              <?php if($isDup): ?><span class="section-badge">Copy</span><?php endif; ?>
              <div class="section-actions">
                <?php if($isDup): ?><button type="button" onclick="duplicateSection(this)"><i class="fa fa-clone"></i></button><?php endif; ?>
                <input type="checkbox" class="section-toggle" <?= $s->is_enabled ? 'checked' : ''; ?>>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <button type="button" class="btn btn-primary" onclick="saveSections()" style="margin-bottom:16px;">Save Layout</button>

      <div class="card">
        <div class="card-title">How to Create Content</div>
        <div class="form-group guide" style="padding:16px;">
          <p><strong>Banner</strong> — Create in <a href="<?= base_url('mobile/online_store/banners'); ?>" class="link">Banners</a></p>
          <p><strong>Products/Services</strong> — Manage in <a href="<?= base_url('mobile/online_store/products'); ?>" class="link">Products</a> / <a href="<?= base_url('mobile/online_store/services'); ?>" class="link">Services</a></p>
          <p><strong>Brands</strong> — Add logos in <a href="<?= base_url('mobile/online_store/brands'); ?>" class="link">Brands</a></p>
          <p><strong>Testimonials</strong> — Add reviews in <a href="<?= base_url('mobile/online_store/testimonials'); ?>" class="link">Testimonials</a></p>
          <p><strong>Instagram</strong> — Upload in <a href="<?= base_url('mobile/online_store/instagram'); ?>" class="link">Instagram</a></p>
          <p><strong>FAQs</strong> — Add in <a href="<?= base_url('mobile/online_store/faqs'); ?>" class="link">FAQs</a></p>
          <p><strong>Headline / Hours</strong> — Edit in <a href="<?= base_url('mobile/online_store/appearance'); ?>" class="link">Appearance</a></p>
          <p><strong>Trust Badges / Newsletter</strong> — Edit in <a href="<?= base_url('mobile/online_store/settings'); ?>" class="link">Store Settings</a></p>
        </div>
      </div>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function showToast(message, isError){
      const t = document.createElement('div');
      t.textContent = message;
      t.style.position='fixed'; t.style.top='16px'; t.style.left='16px'; t.style.right='16px';
      t.style.background=isError?'#DC2626':'#059669'; t.style.color='#fff'; t.style.padding='14px 16px';
      t.style.borderRadius='12px'; t.style.textAlign='center'; t.style.zIndex='1000'; t.style.fontWeight='600';
      document.body.appendChild(t); setTimeout(()=>t.remove(), 3000);
    }
    new Sortable(document.getElementById('sections-container'), { handle: '.section-handle', animation: 150, ghostClass: 'sortable-ghost' });
    function saveSections(){
      const rows = document.querySelectorAll('#sections-container .section-row');
      const fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      rows.forEach((row, idx) => {
        const key = row.dataset.key;
        fd.append('sections['+key+'][enabled]', row.querySelector('.section-toggle').checked ? 1 : 0);
        fd.append('sections['+key+'][order]', idx + 1);
      });
      fetch('<?= base_url('online_store/save_homepage_sections'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        showToast(res.message || 'Saved', res.status !== 'success');
      }).catch(()=>showToast('Save failed', true));
    }
    function duplicateSection(btn){
      btn.disabled = true; btn.innerHTML = '<i class="fa fa-refresh fa-spin"></i>';
      const fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fd.append('section_key', btn.closest('.section-row').dataset.key);
      fetch('<?= base_url('online_store/duplicate_homepage_section'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); location.reload(); }
        else { showToast(res.message || 'Failed', true); btn.disabled = false; btn.innerHTML = '<i class="fa fa-clone"></i>'; }
      }).catch(()=>{ showToast('Error', true); btn.disabled = false; });
    }
  </script>
</body>
</html>

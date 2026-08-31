<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Testimonials</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
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
    .sync-box { background: #fff; border: 1px dashed var(--mp-border); border-radius: 16px; padding: 16px; margin-bottom: 16px; }
    .sync-title { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
    .sync-text { font-size: 13px; color: var(--mp-muted); margin-bottom: 14px; line-height: 1.5; }
    .sync-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 12px; background: #DC2626; color: #fff; border: none; font-weight: 600; font-size: 14px; cursor: pointer; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 700; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group { padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group:last-child { border-bottom: none; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--mp-ink); margin-bottom: 6px; }
    .form-control { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; }
    .form-control:focus { border-color: var(--mp-primary); outline: none; }
    .form-check { display: flex; align-items: center; gap: 10px; font-size: 14px; }
    .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-weight: 600; font-size: 15px; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .testi-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .testi-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .testi-photo { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 1px solid var(--mp-border); }
    .testi-name { font-size: 15px; font-weight: 700; }
    .stars { color: var(--mp-warning); font-size: 14px; margin-top: 4px; }
    .testi-text { font-size: 14px; color: var(--mp-ink); line-height: 1.5; margin-bottom: 12px; }
    .testi-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .status { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }
    .status-active { background: #D1FAE5; color: #065F46; }
    .status-inactive { background: #F1F5F9; color: #475569; }
    .testi-actions { display: flex; gap: 10px; }
    .testi-actions button { flex: 1; padding: 10px; border-radius: 10px; border: none; font-weight: 600; font-size: 13px; cursor: pointer; }
    .btn-edit { background: #EFF6FF; color: var(--mp-primary); }
    .btn-del { background: #FEF2F2; color: #DC2626; }
    .empty { text-align: center; padding: 40px 24px; color: var(--mp-muted); font-size: 14px; }
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
          <h1>Testimonials</h1>
        </div>
      </div>

      <div class="sync-box">
        <div class="sync-title"><i class="fa fa-google"></i> Import from Google My Business</div>
        <div class="sync-text">Set your Google Places API key and Place ID in Settings, then import your Google reviews.</div>
        <button class="sync-btn" id="btn-gmb" onclick="fetchGmb()"><i class="fa fa-refresh"></i> Import Reviews</button>
      </div>

      <?php if($can_edit): ?>
      <div class="card">
        <div class="card-title" id="formTitle">Add Testimonial</div>
        <form id="testiForm" enctype="multipart/form-data" onsubmit="return false;">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="testimonial_id" id="testimonial_id" value="">
          <div class="form-group">
            <label class="form-label">Customer Name</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Testimonial</label>
            <textarea name="testimonial_text" id="testimonial_text" class="form-control" rows="3" required></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Rating</label>
            <select name="rating" id="rating" class="form-control">
              <?php for($i=5; $i>=1; $i--): ?>
                <option value="<?= $i; ?>"><?= $i; ?> Star<?= $i > 1 ? 's' : ''; ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Customer Photo</label>
            <input type="file" name="customer_photo" id="customer_photo" class="form-control" accept="image/*">
          </div>
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
          </div>
          <div class="form-group">
            <label class="form-check"><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> <span>Active</span></label>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="saveTestimonial()">Save</button>
          </div>
        </form>
      </div>
      <?php endif; ?>

      <?php if(!empty($testimonials)): ?>
        <?php foreach($testimonials as $t): ?>
          <div class="testi-card">
            <div class="testi-header">
              <?php if($t->customer_photo): ?>
                <img src="<?= base_url($t->customer_photo); ?>" class="testi-photo" alt="">
              <?php else: ?>
                <div class="testi-photo" style="display:flex;align-items:center;justify-content:center;background:#F1F5F9;color:#94A3B8;"><i class="fa fa-user"></i></div>
              <?php endif; ?>
              <div>
                <div class="testi-name"><?= htmlspecialchars($t->customer_name); ?></div>
                <div class="stars"><?= str_repeat('★', (int)$t->rating) . str_repeat('☆', 5 - (int)$t->rating); ?></div>
              </div>
            </div>
            <div class="testi-text"><?= nl2br(htmlspecialchars($t->testimonial_text)); ?></div>
            <div class="testi-row">
              <span style="font-size:13px;color:var(--mp-muted);">Sort: <?= (int)$t->sort_order; ?></span>
              <span class="status <?= $t->is_enabled ? 'status-active' : 'status-inactive'; ?>"><?= $t->is_enabled ? 'Active' : 'Inactive'; ?></span>
            </div>
            <?php if($can_edit): ?>
            <div class="testi-actions">
              <button class="btn-edit" onclick="editTestimonial(<?= $t->id; ?>, '<?= addslashes(htmlspecialchars($t->customer_name)); ?>', '<?= addslashes(htmlspecialchars($t->testimonial_text)); ?>', <?= (int)$t->rating; ?>, <?= (int)$t->sort_order; ?>, <?= $t->is_enabled ? 1 : 0; ?>)">Edit</button>
              <button class="btn-del" onclick="deleteTestimonial(<?= $t->id; ?>, this)">Delete</button>
            </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No testimonials yet.</div>
      <?php endif; ?>
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
    function fetchGmb(){
      const btn = document.getElementById('btn-gmb');
      btn.disabled = true; btn.innerHTML = '<i class="fa fa-refresh fa-spin"></i> Importing...';
      const fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fetch('<?= base_url('online_store/fetch_gmb_reviews'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-refresh"></i> Import Reviews';
        if(res.status === 'success'){ showToast(res.message); location.reload(); } else { showToast(res.message || 'Import failed', true); }
      }).catch(()=>{ showToast('Network error', true); btn.disabled = false; btn.innerHTML = '<i class="fa fa-refresh"></i> Import Reviews'; });
    }
    function saveTestimonial(){
      const fd = new FormData(document.getElementById('testiForm'));
      fetch('<?= base_url('online_store/save_testimonial'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); location.reload(); } else { showToast(res.message || 'Failed to save', true); }
      }).catch(()=>showToast('Network error', true));
    }
    function editTestimonial(id, name, text, rating, order, enabled){
      document.getElementById('testimonial_id').value = id;
      document.getElementById('customer_name').value = name;
      document.getElementById('testimonial_text').value = text;
      document.getElementById('rating').value = rating;
      document.getElementById('sort_order').value = order;
      document.getElementById('is_enabled').checked = enabled === 1;
      document.getElementById('formTitle').textContent = 'Edit Testimonial';
      window.scrollTo({top:0, behavior:'smooth'});
    }
    function deleteTestimonial(id, btn){
      if(!confirm('Delete this testimonial?')) return;
      btn.disabled = true;
      const fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fetch('<?= base_url('online_store/delete_testimonial/'); ?>'+id, {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); location.reload(); } else { showToast(res.message || 'Failed', true); btn.disabled = false; }
      }).catch(()=>{ showToast('Network error', true); btn.disabled = false; });
    }
  </script>
</body>
</html>

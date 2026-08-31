<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — QR Codes</title>
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
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 700; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group { padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group:last-child { border-bottom: none; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--mp-ink); margin-bottom: 6px; }
    .form-control { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; }
    .form-control:focus { border-color: var(--mp-primary); outline: none; }
    .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-weight: 600; font-size: 15px; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .qr-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .qr-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .qr-name { font-size: 15px; font-weight: 700; }
    .qr-type { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: #EFF6FF; color: var(--mp-primary); }
    .qr-body { display: flex; align-items: center; gap: 16px; margin-bottom: 14px; }
    .qr-image { width: 80px; height: 80px; border-radius: 12px; border: 1px solid var(--mp-border); object-fit: contain; }
    .qr-actions { display: flex; gap: 10px; }
    .qr-actions a, .qr-actions button { flex: 1; text-align: center; padding: 10px; border-radius: 10px; border: none; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: none; }
    .btn-download { background: #D1FAE5; color: #065F46; }
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
          <h1>QR Codes</h1>
        </div>
      </div>

      <?php if($can_edit): ?>
      <div class="card">
        <div class="card-title">Generate QR Code</div>
        <div class="form-group">
          <label class="form-label">QR Name</label>
          <input type="text" id="qr_name" class="form-control" placeholder="e.g. Store QR">
        </div>
        <div class="form-group">
          <label class="form-label">QR Type</label>
          <select id="qr_type" class="form-control" onchange="toggleQrOptions()">
            <option value="store">Store QR</option>
            <option value="product">Product QR</option>
            <option value="service">Service QR</option>
            <option value="category">Category QR</option>
            <?php if(mp_feature_enabled('table_management')) { ?><option value="table">Table QR</option><?php } ?>
            <option value="attendance">Attendance QR</option>
          </select>
        </div>
        <div class="form-group" id="product-select" style="display:none;">
          <label class="form-label">Select Product</label>
          <select id="related_product" class="form-control"><option value="">- Select -</option><?php foreach($products as $p): ?><option value="<?= $p->id; ?>"><?= htmlspecialchars($p->item_name); ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group" id="service-select" style="display:none;">
          <label class="form-label">Select Service</label>
          <select id="related_service" class="form-control"><option value="">- Select -</option><?php foreach($services as $s): ?><option value="<?= $s->id; ?>"><?= htmlspecialchars($s->service_name); ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group" id="category-select" style="display:none;">
          <label class="form-label">Select Category</label>
          <select id="related_category" class="form-control"><option value="">- Select -</option><?php foreach($categories as $c): ?><option value="<?= $c->id; ?>"><?= htmlspecialchars($c->category_name); ?></option><?php endforeach; ?></select>
        </div>
        <?php if(mp_feature_enabled('table_management')) { ?>
        <div class="form-group" id="table-input" style="display:none;">
          <label class="form-label">Table Number</label>
          <input type="text" id="table_number" class="form-control" placeholder="e.g. Table 5">
        </div>
        <?php } ?>
        <div class="form-group">
          <button type="button" class="btn btn-primary" id="btn-generate" onclick="generateQr()"><i class="fa fa-qrcode"></i> Generate QR</button>
        </div>
      </div>
      <?php endif; ?>

      <?php if(!empty($qr_codes)): ?>
        <?php foreach($qr_codes as $qr):
          $typeLabels = ['store' => 'Store', 'product' => 'Product', 'service' => 'Service', 'category' => 'Category', 'table' => 'Table', 'attendance' => 'Attendance'];
          $typeLabel = $typeLabels[trim($qr->qr_type ?: '')] ?? ucfirst($qr->qr_type);
        ?>
          <div class="qr-card">
            <div class="qr-header">
              <span class="qr-name"><?= htmlspecialchars($qr->qr_name); ?></span>
              <span class="qr-type"><?= $typeLabel; ?></span>
            </div>
            <div class="qr-body">
              <?php if($qr->qr_image && file_exists($qr->qr_image)): ?>
                <img src="<?= base_url($qr->qr_image); ?>" class="qr-image" alt="QR">
              <?php else: ?>
                <div class="qr-image" style="display:flex;align-items:center;justify-content:center;background:#F1F5F9;color:#94A3B8;"><i class="fa fa-qrcode" style="font-size:28px;"></i></div>
              <?php endif; ?>
            </div>
            <div class="qr-actions">
              <a href="<?= base_url($qr->qr_image); ?>" download class="btn-download"><i class="fa fa-download"></i> Download</a>
              <button class="btn-del" onclick="deleteQr(<?= $qr->id; ?>, this)"><i class="fa fa-trash"></i> Delete</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No QR codes yet.</div>
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
    function toggleQrOptions(){
      var type = document.getElementById('qr_type').value;
      document.getElementById('product-select').style.display = 'none';
      document.getElementById('service-select').style.display = 'none';
      document.getElementById('category-select').style.display = 'none';
      <?php if(mp_feature_enabled('table_management')) { ?>if(document.getElementById('table-input')) document.getElementById('table-input').style.display = 'none';<?php } ?>
      if(type=='product') document.getElementById('product-select').style.display = 'block';
      if(type=='service') document.getElementById('service-select').style.display = 'block';
      if(type=='category') document.getElementById('category-select').style.display = 'block';
      <?php if(mp_feature_enabled('table_management')) { ?>if(type=='table' && document.getElementById('table-input')) document.getElementById('table-input').style.display = 'block';<?php } ?>
    }
    function generateQr(){
      var type = document.getElementById('qr_type').value;
      var relatedId = 0;
      if(type=='product') relatedId = document.getElementById('related_product').value;
      if(type=='service') relatedId = document.getElementById('related_service').value;
      if(type=='category') relatedId = document.getElementById('related_category').value;
      var tableNumber = <?php echo mp_feature_enabled('table_management') ? "document.getElementById('table_number').value" : "''"; ?>;
      var btn = document.getElementById('btn-generate');
      btn.disabled = true; btn.innerHTML = '<i class="fa fa-refresh fa-spin"></i> Generating...';
      const fd = new FormData();
      fd.append('qr_type', type);
      fd.append('related_id', relatedId);
      fd.append('table_number', tableNumber);
      fd.append('qr_name', document.getElementById('qr_name').value);
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fetch('<?= base_url('online_store/generate_qr'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-qrcode"></i> Generate QR';
        if(res.status === 'success'){ showToast(res.message); setTimeout(()=>location.reload(), 800); }
        else { showToast(res.message || 'Failed', true); }
      }).catch(()=>{ showToast('Network error', true); btn.disabled = false; btn.innerHTML = '<i class="fa fa-qrcode"></i> Generate QR'; });
    }
    function deleteQr(id, btn){
      if(!confirm('Delete this QR code?')) return;
      btn.disabled = true;
      const fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fetch('<?= base_url('online_store/delete_qr/'); ?>'+id, {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); setTimeout(()=>location.reload(), 800); }
        else { showToast(res.message || 'Failed', true); btn.disabled = false; }
      }).catch(()=>{ showToast('Network error', true); btn.disabled = false; });
    }
  </script>
</body>
</html>

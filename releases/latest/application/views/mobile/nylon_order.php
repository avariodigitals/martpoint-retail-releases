<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-default { background: #E2E8F0; color: #475569; }
    .badge-info { background: #DBEAFE; color: #1D4ED8; }
    .badge-warning { background: #FEF3C7; color: #B45309; }
    .badge-primary { background: #E0E7FF; color: #4338CA; }
    .badge-success { background: #D1FAE5; color: #047857; }
    .badge-danger { background: #FEE2E2; color: #B91C1C; }
    .hero { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .hero .nm { font-size: 16px; font-weight: 700; }
    .hero .sub { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .kv { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px; }
    .kv div { font-size: 11px; color: var(--mp-muted); }
    .kv b { display: block; font-size: 14px; color: var(--mp-ink); margin-top: 2px; }
    .kv b.warn { color: var(--mp-danger); }
    .spec-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .spec-table td { padding: 7px 0; border-bottom: 1px solid var(--mp-border); font-size: 13px; }
    .spec-table td:first-child { color: var(--mp-muted); width: 40%; }
    .spec-table tr:last-child td { border-bottom: none; }
    .section-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 18px 0 8px; }
    .art { display: flex; align-items: center; gap: 12px; background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 12px 14px; margin-bottom: 8px; }
    .art .fi { width: 36px; height: 36px; border-radius: 10px; background: var(--mp-bg); display: flex; align-items: center; justify-content: center; color: var(--mp-muted); flex-shrink: 0; }
    .art .an { flex: 1; min-width: 0; }
    .art .an .f { font-size: 13px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .art .an .m { font-size: 11px; color: var(--mp-muted); margin-top: 2px; }
    .art .acts { display: flex; gap: 6px; }
    .icon-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .icon-btn.ok { color: var(--mp-success); }
    .icon-btn.no { color: var(--mp-danger); }
    .upload-box { border: 1px dashed var(--mp-border); border-radius: 12px; padding: 14px; text-align: center; font-size: 13px; color: var(--mp-muted); margin-bottom: 8px; }
    .upload-box input[type=file] { display: none; }
    .upload-box label { color: var(--mp-primary); font-weight: 700; cursor: pointer; }
    .job-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 12px 14px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; color: var(--mp-ink); }
    .job-card .jc-nm { font-size: 13px; font-weight: 700; }
    .job-card .jc-sub { font-size: 11px; color: var(--mp-muted); margin-top: 2px; }
    .btn { padding: 10px 14px; border-radius: 10px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; color: var(--mp-ink); display: inline-flex; align-items: center; gap: 6px; }
    .btn.primary { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .btn-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
    .hist { font-size: 12px; color: var(--mp-muted); padding: 6px 0; border-bottom: 1px solid var(--mp-border); }
    .hist:last-child { border-bottom: none; }
    .dispatch-row { display: flex; gap: 8px; margin-top: 10px; }
    .dispatch-row input { flex: 1; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-family: inherit; font-size: 14px; }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    .empty-state { text-align: center; padding: 20px; color: var(--mp-muted); font-size: 13px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/nylon_orders'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="hero">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
          <div>
            <div class="nm"><?= htmlspecialchars($order->customer_name ?: 'Walk-in'); ?></div>
            <div class="sub"><?= htmlspecialchars($order->item_name ?: ''); ?><?= $order->repeat_of_id ? ' · repeat order' : ''; ?></div>
          </div>
          <span class="badge badge-<?= Custom_orders_model::status_badge($order->status); ?>"><?= htmlspecialchars(Custom_orders_model::status_label($order->status)); ?></span>
        </div>
        <div class="kv">
          <div>Ordered<b><?= rtrim(rtrim(number_format((float)$order->order_qty, 2), '0'), '.'); ?> <?= htmlspecialchars($order->unit_name ?: ''); ?></b></div>
          <div>Dispatched<b><?= rtrim(rtrim(number_format((float)$order->dispatched_qty, 2), '0'), '.'); ?></b></div>
          <div>Total<b><?= store_number_format($order->total_amount); ?></b></div>
          <div>Balance<b class="<?= (float)$order->balance_due > 0 ? 'warn' : ''; ?>"><?= store_number_format($order->balance_due); ?></b></div>
          <div>Order Date<b><?= show_date($order->order_date); ?></b></div>
          <div>Due Date<b><?= $order->due_date ? show_date($order->due_date) : '—'; ?></b></div>
        </div>
        <?php if(!empty($order->specs)): ?>
        <table class="spec-table">
          <?php foreach($order->specs as $k => $v): ?>
          <tr><td><?= htmlspecialchars($k); ?></td><td><?= htmlspecialchars($v); ?></td></tr>
          <?php endforeach; ?>
          <?php if(!empty($order->design_ref)): ?><tr><td>Design Ref</td><td><?= htmlspecialchars($order->design_ref); ?></td></tr><?php endif; ?>
        </table>
        <?php endif; ?>
        <div class="btn-row">
          <?php if(!empty($can_jobs)): ?><a class="btn primary" href="<?= base_url('mobile/nylon_job_form/'.$order->id); ?>"><i class="fa fa-industry"></i> New Job</a><?php endif; ?>
          <?php if(!empty($can_edit)): ?><button class="btn" id="repeatBtn"><i class="fa fa-copy"></i> Repeat Order</button><?php endif; ?>
          <?php if(!empty($can_edit)): ?><a class="btn" href="<?= base_url('mobile/nylon_order_form/'.$order->id); ?>"><i class="fa fa-pencil"></i> Edit</a><?php endif; ?>
        </div>
        <?php if(!empty($can_edit) && $order->status !== 'delivered' && $order->status !== 'cancelled'): ?>
        <div class="dispatch-row">
          <input type="number" step="any" min="0" id="dispatchQty" placeholder="Dispatch qty">
          <button class="btn primary" id="dispatchBtn"><i class="fa fa-truck"></i> Dispatch</button>
        </div>
        <?php endif; ?>
      </div>

      <div class="section-label">Artwork &amp; Design</div>
      <?php if(empty($order->artwork_required) && empty($order->artworks)): ?>
        <div class="empty-state"><div>No artwork required for this order.</div></div>
      <?php else: ?>
        <?php foreach($order->artworks as $art): ?>
        <div class="art">
          <div class="fi"><i class="fa fa-file-image-o"></i></div>
          <div class="an">
            <div class="f"><a href="<?= base_url($art->file_path); ?>" target="_blank" style="color:inherit;text-decoration:none;"><?= htmlspecialchars($art->file_name); ?></a> <span style="color:var(--mp-muted);font-weight:400;">v<?= (int)$art->version_no; ?></span></div>
            <div class="m"><?= htmlspecialchars(ucfirst($art->status)); ?><?= $art->approved_by ? ' · by '.htmlspecialchars($art->approved_by) : ''; ?><?= $art->note ? ' · '.htmlspecialchars($art->note) : ''; ?></div>
          </div>
          <span class="badge badge-<?= $art->status === 'approved' ? 'success' : ($art->status === 'rejected' ? 'danger' : 'warning'); ?>"><?= htmlspecialchars(ucfirst($art->status)); ?></span>
          <?php if(!empty($can_approve) && $art->status !== 'approved'): ?>
          <div class="acts">
            <button class="icon-btn ok" data-art="<?= (int)$art->id; ?>" data-st="approved" title="Approve"><i class="fa fa-check"></i></button>
            <?php if($art->status !== 'rejected'): ?><button class="icon-btn no" data-art="<?= (int)$art->id; ?>" data-st="rejected" title="Reject"><i class="fa fa-times"></i></button><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if(!empty($can_artwork)): ?>
        <div class="upload-box">
          <form id="artForm" enctype="multipart/form-data">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="order_id" value="<?= (int)$order->id; ?>">
            <input type="file" name="artwork_file" id="artworkFile" accept="image/*,.pdf,.ai,.psd,.cdr,.svg,.zip">
            <label for="artworkFile"><i class="fa fa-cloud-upload"></i> Upload artwork / design file</label>
            <div id="artFileName" style="margin-top:6px;font-size:12px;"></div>
          </form>
        </div>
        <?php endif; ?>
      <?php endif; ?>

      <div class="section-label">Linked Jobs</div>
      <?php if(!empty($jobs)): ?>
        <?php foreach($jobs as $j): ?>
        <a class="job-card" href="<?= base_url('mobile/nylon_job/'.$j->id); ?>">
          <div>
            <div class="jc-nm"><?= htmlspecialchars($j->job_code); ?></div>
            <div class="jc-sub"><?= rtrim(rtrim(number_format((float)$j->planned_qty, 2), '0'), '.'); ?> planned<?= $j->due_date ? ' · due '.show_date($j->due_date) : ''; ?></div>
          </div>
          <span class="badge badge-<?= Nylon_model::job_status_badge($j->status); ?>"><?= htmlspecialchars(Nylon_model::job_status_label($j->status)); ?></span>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><div>No production jobs yet.</div></div>
      <?php endif; ?>

      <?php if(!empty($history)): ?>
      <div class="section-label">History</div>
      <div class="hero" style="margin-bottom:0;">
        <?php foreach($history as $h): ?>
        <div class="hist"><?= htmlspecialchars($h->note ?: (ucfirst($h->old_status ?: '—').' → '.ucfirst($h->new_status ?: '—'))); ?> · <?= show_date($h->created_at); ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    var ORDER_ID = <?= (int)$order->id; ?>;

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 3500); }

    function post(url, fields, btn){
      var fd = new FormData();
      for(var k in fields){ fd.append(k, fields[k]); }
      fd.append(csrf_token, csrf_hash);
      if(btn) btn.disabled = true;
      fetch(base_url + url, { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
          if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; }
          if(d && d.success){ window.location.reload(); }
          else { showToast(d && d.message ? d.message : 'Action failed.'); if(btn) btn.disabled = false; }
        })
        .catch(function(){ showToast('Network error.'); if(btn) btn.disabled = false; });
    }

    document.querySelectorAll('[data-art]').forEach(function(btn){
      btn.addEventListener('click', function(){
        var st = this.getAttribute('data-st');
        if(st === 'rejected' && !confirm('Reject this artwork? Printing cannot proceed on a rejected design.')){ return; }
        post('nylon/artwork_status', { id: this.getAttribute('data-art'), status: st }, this);
      });
    });

    var db = document.getElementById('dispatchBtn');
    if(db){
      db.addEventListener('click', function(){
        var q = parseFloat(document.getElementById('dispatchQty').value);
        if(!q || q <= 0){ showToast('Enter a dispatch quantity.'); return; }
        post('nylon/order_dispatch', { id: ORDER_ID, dispatch_qty: q }, this);
      });
    }

    var rb = document.getElementById('repeatBtn');
    if(rb){
      rb.addEventListener('click', function(){
        if(!confirm('Create a repeat order with this approved specification?')){ return; }
        post('nylon/order_repeat', { id: ORDER_ID }, this);
      });
    }

    var af = document.getElementById('artworkFile');
    if(af){
      af.addEventListener('change', function(){
        if(!this.files.length) return;
        document.getElementById('artFileName').textContent = 'Uploading '+this.files[0].name+'…';
        var form = document.getElementById('artForm');
        var fd = new FormData(form);
        fetch(base_url + 'nylon/artwork_upload', { method: 'POST', body: fd })
          .then(function(r){ return r.json(); })
          .then(function(d){
            if(d && d.success){ window.location.reload(); }
            else { document.getElementById('artFileName').textContent = ''; showToast(d && d.message ? d.message : 'Upload failed.'); }
          })
          .catch(function(){ document.getElementById('artFileName').textContent = ''; showToast('Network error.'); });
      });
    }
  </script>
</body>
</html>

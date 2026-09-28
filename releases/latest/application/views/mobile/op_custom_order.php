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
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card h3 { font-size: 13px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .kv { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px dashed var(--mp-border); font-size: 14px; }
    .kv:last-child { border-bottom: none; }
    .kv .k { color: var(--mp-muted); }
    .kv .v { font-weight: 600; text-align: right; }
    .spec-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px dashed var(--mp-border); }
    .spec-row:last-child { border-bottom: none; }
    .spec-row .k { color: var(--mp-muted); }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
    .badge-default { background: #E2E8F0; color: #475569; }
    .badge-info { background: #DBEAFE; color: #1D4ED8; }
    .badge-warning { background: #FEF3C7; color: #B45309; }
    .badge-primary { background: #E0E7FF; color: #4338CA; }
    .badge-success { background: #D1FAE5; color: #047857; }
    .badge-danger { background: #FEE2E2; color: #B91C1C; }
    .flow { display: flex; align-items: center; gap: 4px; overflow-x: auto; padding-bottom: 6px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .flow::-webkit-scrollbar { display: none; }
    .flow-step { flex: 0 0 auto; display: flex; align-items: center; gap: 4px; }
    .flow-pill { padding: 5px 10px; border-radius: 14px; font-size: 11px; font-weight: 600; background: var(--mp-bg); color: var(--mp-muted); white-space: nowrap; }
    .flow-pill.done { background: #D1FAE5; color: #047857; }
    .flow-pill.now { background: var(--mp-primary); color: #fff; }
    .flow-arrow { color: var(--mp-border); font-size: 10px; }
    .status-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .st-btn { padding: 9px 14px; border-radius: 12px; border: 1px solid var(--mp-border); background: var(--mp-surface); font-size: 13px; font-weight: 600; cursor: pointer; }
    .st-btn.primary { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .st-btn.danger { color: var(--mp-danger); border-color: #FECACA; }
    .st-btn:disabled { opacity: 0.6; }
    .hist { position: relative; padding-left: 18px; }
    .hist-item { position: relative; padding-bottom: 14px; font-size: 13px; }
    .hist-item::before { content: ''; position: absolute; left: -14px; top: 5px; width: 8px; height: 8px; border-radius: 50%; background: var(--mp-primary); }
    .hist-item::after { content: ''; position: absolute; left: -11px; top: 16px; bottom: 0; width: 2px; background: var(--mp-border); }
    .hist-item:last-child::after { display: none; }
    .hist-item .what { font-weight: 600; }
    .hist-item .when { font-size: 12px; color: var(--mp-muted); }
    .actions { display: flex; gap: 10px; margin-top: 4px; }
    .btn { flex: 1; padding: 14px; border-radius: 14px; border: 1px solid var(--mp-border); background: var(--mp-bg); color: var(--mp-ink); font-size: 14px; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; }
    .btn.primary { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .btn.danger { color: var(--mp-danger); border-color: #FECACA; }
    .notes-block { font-size: 13px; color: var(--mp-ink); white-space: pre-wrap; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/custom_orders'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($order->order_code); ?></h1>
        </div>
        <span class="badge badge-<?= Custom_orders_model::status_badge($order->status); ?>"><?= htmlspecialchars(Custom_orders_model::status_label($order->status)); ?></span>
      </div>

      <div class="card">
        <h3><?= htmlspecialchars($co_label); ?></h3>
        <div class="kv"><span class="k">Client / Item</span><span class="v"><?= htmlspecialchars($order->customer_name ?: 'Walk-in'); ?><br><small style="font-weight:400;color:var(--mp-muted)"><?= htmlspecialchars($order->template_item_name ?: $order->item_name ?: '-'); ?></small></span></div>
        <div class="kv"><span class="k"><?= htmlspecialchars(mp_label('staff')); ?></span><span class="v"><?= htmlspecialchars($order->staff_name ?: '-'); ?></span></div>
        <div class="kv"><span class="k">Order Date</span><span class="v"><?= show_date($order->order_date); ?></span></div>
        <div class="kv"><span class="k">Due Date</span><span class="v"><?= !empty($order->due_date) ? show_date($order->due_date) : '-'; ?></span></div>
        <div class="kv"><span class="k">Quoted</span><span class="v"><?= $this->currency($order->quoted_price); ?></span></div>
        <div class="kv"><span class="k">Deposit</span><span class="v"><?= $this->currency($order->deposit_paid); ?> / <?= $this->currency($order->deposit_amount); ?></span></div>
        <div class="kv"><span class="k">Total</span><span class="v" style="color:var(--mp-primary)"><?= $this->currency($order->total_amount); ?></span></div>
        <div class="kv"><span class="k">Balance Due</span><span class="v" style="color:<?= ((float)$order->balance_due > 0) ? 'var(--mp-danger)' : 'var(--mp-success)'; ?>"><?= $this->currency($order->balance_due); ?></span></div>
      </div>

      <?php $specs = json_decode($order->specifications_json ?: '[]', true); if(!empty($specs)): ?>
      <div class="card">
        <h3>Specifications</h3>
        <?php foreach($specs as $k => $v): ?>
        <div class="spec-row"><span class="k"><?= htmlspecialchars($k); ?></span><span><?= htmlspecialchars($v); ?></span></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if(!empty($order->notes)): ?>
      <div class="card"><h3>Notes</h3><div class="notes-block"><?= nl2br(htmlspecialchars($order->notes)); ?></div></div>
      <?php endif; ?>

      <div class="card">
        <h3>Workflow</h3>
        <div class="flow">
          <?php $seen = false; foreach($workflow as $ws):
            if($ws === $order->status){ $seen = true; }
            $cls = ($ws === $order->status) ? 'now' : ((!$seen || $order->status === $ws) ? '' : 'done');
            if(!$seen && $ws !== $order->status){ $cls = 'done'; }
          ?>
          <span class="flow-step"><span class="flow-pill <?= $cls; ?>"><?= htmlspecialchars(Custom_orders_model::status_label($ws)); ?></span><i class="fa fa-angle-right flow-arrow"></i></span>
          <?php endforeach; ?>
        </div>
        <?php if($can_edit && $order->status !== 'cancelled'): ?>
        <div class="status-grid" id="statusGrid">
          <?php foreach($workflow as $ws): if($ws === $order->status || $ws === 'cancelled') continue; ?>
          <button class="st-btn <?= ($ws === 'delivered' || $ws === 'picked_up') ? 'primary' : ''; ?>" data-status="<?= $ws; ?>"><?= htmlspecialchars(Custom_orders_model::status_label($ws)); ?></button>
          <?php endforeach; ?>
          <button class="st-btn danger" data-status="cancelled">Cancel</button>
        </div>
        <?php endif; ?>
      </div>

      <?php if(!empty($history)): ?>
      <div class="card">
        <h3>History</h3>
        <div class="hist">
          <?php foreach($history as $h): ?>
          <div class="hist-item">
            <div class="what"><?= htmlspecialchars(Custom_orders_model::status_label($h->old_status)); ?> <i class="fa fa-arrow-right"></i> <?= htmlspecialchars(Custom_orders_model::status_label($h->new_status)); ?></div>
            <div class="when"><?= htmlspecialchars($h->changed_by_name ?: 'System'); ?> · <?= date('M j, g:i A', strtotime($h->created_at)); ?><?= !empty($h->note) ? ' — '.htmlspecialchars($h->note) : ''; ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="actions">
        <a href="<?= base_url('mobile/custom_order_form/'.$order->id); ?>" class="btn primary"><i class="fa fa-pencil"></i> Edit</a>
        <?php if($can_delete): ?>
        <button class="btn danger" id="deleteBtn"><i class="fa fa-trash"></i> Delete</button>
        <?php endif; ?>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    var order_id = <?= (int)$order->id; ?>;

    function post(url, fields, cb){
      var fd = new FormData();
      for(var k in fields){ fd.append(k, fields[k]); }
      fd.append(csrf_token, csrf_hash);
      fetch(base_url + url, { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(d){ if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; } cb(d); })
        .catch(function(){ cb({success:false, message:'Network error.'}); });
    }

    document.querySelectorAll('.st-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var st = this.getAttribute('data-status');
        if(st === 'cancelled' && !confirm('Cancel this <?= strtolower(htmlspecialchars($co_label)); ?>?')){ return; }
        var b = this; b.disabled = true;
        post('mobile/custom_order_status', { id: order_id, status: st }, function(d){
          if(d && d.success){ window.location.reload(); }
          else { alert(d && d.message ? d.message : 'Update failed.'); b.disabled = false; }
        });
      });
    });

    var del = document.getElementById('deleteBtn');
    if(del){
      del.addEventListener('click', function(){
        if(!confirm('Delete <?= htmlspecialchars($order->order_code); ?>? This cannot be undone.')){ return; }
        post('mobile/custom_order_delete', { id: order_id }, function(d){
          if(d && d.success){ window.location.href = base_url + 'mobile/custom_orders'; }
          else { alert(d && d.message ? d.message : 'Delete failed.'); }
        });
      });
    }
  </script>
</body>
</html>

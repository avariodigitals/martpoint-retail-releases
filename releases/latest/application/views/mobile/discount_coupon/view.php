<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Coupons Master</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 140px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: clamp(18px, 5vw, 22px); font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
    .summary-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; text-align: center; }
    .summary-card .val { font-size: 22px; font-weight: 700; }
    .summary-card .lbl { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .table-wrap { background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .table-header, .table-row { display: grid; grid-template-columns: minmax(100px,1.5fr) 90px 80px 90px; gap: 8px; padding: 12px 14px; align-items: center; font-size: 13px; }
    .table-header { background: #F8FAFC; color: var(--mp-muted); font-weight: 600; border-bottom: 1px solid var(--mp-border); }
    .table-row { border-bottom: 1px solid var(--mp-border); }
    .table-row:last-child { border-bottom: none; }
    .table-row > div { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .name-col { font-weight: 600; color: var(--mp-ink); }
    .value-col { font-weight: 600; color: var(--mp-primary); }
    .status { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; display: inline-block; }
    .status-active { background: #D1FAE5; color: #065F46; }
    .status-inactive { background: #F1F5F9; color: #475569; }
    .actions { display: flex; gap: 6px; }
    .actions a, .actions button { background: none; border: none; color: var(--mp-primary); font-size: 15px; cursor: pointer; padding: 4px; }
    .actions .del { color: var(--mp-danger); }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
    .fab { position: fixed; left: 16px; bottom: calc(100px + env(safe-area-inset-bottom, 0px)); width: 48px; height: 48px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 14px rgba(0,87,255,0.35); border: none; cursor: pointer; z-index: 80; text-decoration: none; }
    @media (min-width: 600px) { .screen { padding: 16px 24px 160px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Coupons Master</h1>
        </div>
      </div>

      <?php
        $active = 0; $expired = 0; $total = count($coupons);
        foreach($coupons as $c){ if($c->status == 1) $active++; if($c->expire_date < date('Y-m-d')) $expired++; }
      ?>
      <div class="summary">
        <div class="summary-card"><div class="val"><?= $total; ?></div><div class="lbl">Total</div></div>
        <div class="summary-card"><div class="val"><?= $active; ?></div><div class="lbl">Active</div></div>
        <div class="summary-card"><div class="val"><?= $expired; ?></div><div class="lbl">Expired</div></div>
      </div>

      <?php if(!empty($coupons)): ?>
        <div class="table-wrap">
          <div class="table-header">
            <div>Name</div>
            <div>Value</div>
            <div>Expires</div>
            <div>Action</div>
          </div>
          <?php foreach($coupons as $c): ?>
            <div class="table-row" id="row-<?= $c->id; ?>">
              <div class="name-col">
                <div title="<?= htmlspecialchars($c->name); ?>"><?= htmlspecialchars($c->name); ?></div>
                <div style="font-size:11px;color:var(--mp-muted);"><?= htmlspecialchars($c->type); ?></div>
              </div>
              <div class="value-col"><?= store_number_format($c->value); ?></div>
              <div class="exp-col">
                <?= show_date($c->expire_date); ?>
                <?php if($c->expire_date < date('Y-m-d')): ?><br><span style="color:var(--mp-danger);font-size:10px;">Expired</span><?php endif; ?>
              </div>
              <div class="actions">
                <button type="button" class="status-btn" onclick="toggleStatus(<?= $c->id; ?>, <?= $c->status == 1 ? 0 : 1; ?>)" title="<?= $c->status == 1 ? 'Deactivate' : 'Activate'; ?>">
                  <span class="status <?= $c->status == 1 ? 'status-active' : 'status-inactive'; ?>"><?= $c->status == 1 ? 'Active' : 'Inactive'; ?></span>
                </button>
                <a href="<?= base_url('mobile/discount_coupon/update/' . $c->id); ?>" title="Edit"><i class="fa fa-edit"></i></a>
                <button type="button" class="del" onclick="deleteCoupon(<?= $c->id; ?>)" title="Delete"><i class="fa fa-trash"></i></button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty">No coupons yet.</div>
      <?php endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <a href="<?= base_url('mobile/discount_coupon/add'); ?>" class="fab" title="Create Coupon"><i class="fa fa-plus"></i></a>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function toggleStatus(id, status){
      var fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fd.append('id', id);
      fd.append('status', status);
      fetch('<?= base_url('discount_coupon/update_status'); ?>', {method:'POST', body:fd})
      .then(r=>r.text()).then(res=>{ if(res.trim() === 'success') location.reload(); else mpAlert(res || 'Failed', 'danger'); });
    }
    function deleteCoupon(id){
      if(!confirm('Delete this coupon?')) return;
      var fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fd.append('q_id', id);
      fetch('<?= base_url('discount_coupon/delete_coupon'); ?>', {method:'POST', body:fd})
      .then(r=>r.text()).then(res=>{ if(res.trim() === 'success') location.reload(); else mpAlert(res || 'Failed', 'danger'); });
    }
  </script>
</body>
</html>

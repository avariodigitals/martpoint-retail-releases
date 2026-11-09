<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <?php $this->load->view('comman/app_icons'); ?>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root{--mp-primary:#0057ff;--mp-bg:#f1f5f9;--mp-surface:#fff;--mp-text:#0f172a;--mp-muted:#64748b;--mp-border:#e2e8f0;--safe-bottom:env(safe-area-inset-bottom,0px)}
    *{box-sizing:border-box}html,body{margin:0;padding:0;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--mp-bg);color:var(--mp-text)}
    #app{max-width:520px;margin:0 auto;background:var(--mp-surface);min-height:100vh;position:relative}.screen{padding:12px 12px 130px}
    .topbar{display:flex;align-items:center;gap:12px;margin:4px 0 14px}.back{color:var(--mp-primary);font-size:20px;text-decoration:none;width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:var(--mp-bg)}
    .store-name{font-size:11px;color:var(--mp-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px}.topbar h1{font-size:20px;margin:2px 0 0}.card{border:1px solid var(--mp-border);border-radius:14px;padding:14px;margin-bottom:12px;background:#fff}
    .hint,.meta{font-size:12px;color:var(--mp-muted)}input,textarea{width:100%;padding:11px;border:1px solid var(--mp-border);border-radius:10px;font:inherit;margin:5px 0 10px}button,.btn{display:inline-block;border:0;border-radius:10px;padding:10px 13px;background:var(--mp-primary);color:#fff;font-weight:700;text-decoration:none;margin:3px 3px 3px 0}
    .secondary{background:#e2e8f0;color:#0f172a}.error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:10px;margin-bottom:10px}.success{background:#dcfce7;color:#166534;padding:10px;border-radius:10px;margin-bottom:10px}code{word-break:break-all}
  </style>
</head>
<body><div id="app"><section class="screen">
  <div class="topbar"><a class="back" href="<?= base_url('mobile/warranty_lookup'); ?>"><i class="fa fa-chevron-left"></i></a><div><div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div><h1><?= htmlspecialchars($page_title); ?></h1></div></div>
  <?php if(!empty($this->session->flashdata('success'))): ?><div class="success"><?= htmlspecialchars($this->session->flashdata('success')); ?></div><?php endif; ?>
  <?php if(!empty($error)): ?><div class="error"><?= htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if(!empty($unit)): ?>
    <div class="card"><strong>Open warranty case</strong><p><?= htmlspecialchars($unit->item_name ?? 'Device'); ?> · <?= htmlspecialchars($unit->customer_name ?? 'Customer'); ?> · <?= htmlspecialchars($unit->sales_code ?? ''); ?></p>
      <div class="meta">SN <?= htmlspecialchars($unit->sold_serial_number ?? '—'); ?> · IMEI <?= htmlspecialchars($unit->sold_imei_number ?? '—'); ?><?= !empty($unit->sold_imei_number_2) ? ' / '.htmlspecialchars($unit->sold_imei_number_2) : ''; ?></div>
      <form method="post" action="<?= base_url('mobile/warranty_claims'); ?>">
        <?php if($this->security->get_csrf_token_name()): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"><?php endif; ?>
        <input type="hidden" name="salesitems_id" value="<?= (int)$unit->id; ?>">
        <label for="issue">Customer reported fault</label><textarea id="issue" name="issue" rows="4" maxlength="8000" required placeholder="Describe the fault and when it started"></textarea><button type="submit">Open case</button>
      </form>
    </div>
  <?php endif; ?>
  <div class="card"><form method="get" action="<?= base_url('mobile/warranty_claims'); ?>"><input type="search" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search case, serial, IMEI, customer or invoice"><button type="submit">Search cases</button></form></div>
  <?php foreach(($claims ?? array()) as $claim):
    $next=array('reported'=>array('inspecting','approved','rejected','cancelled'),'inspecting'=>array('approved','rejected','awaiting_parts','repaired','replaced','refunded','resolved','cancelled'),'approved'=>array('awaiting_parts','repaired','replaced','refunded','resolved'),'awaiting_parts'=>array('inspecting','repaired','replaced','resolved'),'repaired'=>array('resolved'),'replaced'=>array('resolved'),'refunded'=>array('resolved'))[$claim->status] ?? array();
  ?>
    <div class="card"><strong><?= htmlspecialchars($claim->claim_code); ?> · <?= htmlspecialchars(ucwords(str_replace('_',' ',$claim->status))); ?></strong><p><?= htmlspecialchars($claim->item_name ?? 'Device'); ?> · <?= htmlspecialchars($claim->customer_name ?? 'Customer'); ?></p>
      <div class="meta">SN <?= htmlspecialchars($claim->sold_serial_number ?? '—'); ?> · IMEI <?= htmlspecialchars($claim->sold_imei_number ?? '—'); ?><?= !empty($claim->sold_imei_number_2) ? ' / '.htmlspecialchars($claim->sold_imei_number_2) : ''; ?><br><?= htmlspecialchars($claim->sales_code ?? ''); ?> · <?= htmlspecialchars($claim->mobile ?? ''); ?></div>
      <p><?= nl2br(htmlspecialchars($claim->issue)); ?></p>
      <?php if(!empty($next)): ?><form method="post" action="<?= base_url('mobile/warranty_claims'); ?>">
        <?php if($this->security->get_csrf_token_name()): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"><?php endif; ?>
        <input type="hidden" name="claim_id" value="<?= (int)$claim->id; ?>">
        <textarea name="decision_note" rows="2" maxlength="8000" placeholder="Inspection decision (needed for approve or reject)"><?= htmlspecialchars($claim->decision_note ?? ''); ?></textarea>
        <textarea name="resolution_note" rows="2" maxlength="8000" placeholder="Repair, replacement or resolution notes"><?= htmlspecialchars($claim->resolution_note ?? ''); ?></textarea>
        <?php foreach($next as $status): ?><button class="secondary" type="submit" name="status" value="<?= $status; ?>"><?= htmlspecialchars(ucwords(str_replace('_',' ',$status))); ?></button><?php endforeach; ?>
      </form><?php elseif(!empty($claim->resolution_note)): ?><p><strong>Resolution:</strong> <?= nl2br(htmlspecialchars($claim->resolution_note)); ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if(empty($claims)): ?><div class="card">No warranty cases found.</div><?php endif; ?>
</section><?php $this->load->view('mobile/bottom_nav',['active'=>'more']); ?></div>
<?php $this->load->view('mobile/mp_alert'); ?><?php $this->load->view('mobile/chat'); ?>
</body></html>

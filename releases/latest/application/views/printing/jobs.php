<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section print-jobs">
  <div class="mp-page-head">
    <h2>Print Jobs</h2>
    <div class="mp-page-sub">Every job across the print workflow — from intake to collection. <?= count($jobs) ?> total.</div>
    <?php if ($CI->permissions('print_jobs_add')): ?><a href="<?= base_url('printing/job') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Print Job</a><?php endif; ?>
  </div>

  <?php if (empty($jobs)): ?>
  <div class="mp-card"><div class="mp-card-body"><div class="mp-empty-state">No print jobs yet.</div></div></div>
  <?php else: ?>
  <div class="jt-grid">
    <?php foreach ($jobs as $j):
      $overdue = $j->due_date && strtotime($j->due_date) < strtotime(date('Y-m-d')) && !in_array($j->production_status, ['completed','cancelled']);
      $pstatus = ['planned'=>'#64748b','in_progress'=>'#2563eb','on_hold'=>'#d97706','completed'=>'#059669','cancelled'=>'#dc2626'][$j->production_status] ?? '#64748b';
    ?>
    <a href="<?= base_url('printing/job/' . $j->id) ?>" class="jt-card">
      <div class="jt-top">
        <span class="jt-code"><?= htmlspecialchars($j->job_code) ?></span>
        <span class="jt-status" style="background:<?= $pstatus ?>22;color:<?= $pstatus ?>;"><?= htmlspecialchars(ucwords(str_replace('_',' ',$j->production_status))) ?></span>
      </div>
      <div class="jt-title"><?= htmlspecialchars($j->title ?: 'Print job') ?></div>
      <div class="jt-client"><i class="fa fa-user-o"></i> <?= htmlspecialchars($j->customer_name ?: 'Walk-in') ?></div>
      <div class="jt-gates">
        <span class="jt-gate <?= $j->artwork_status === 'approved' ? 'ok' : '' ?>" title="Artwork">Art</span>
        <span class="jt-gate <?= $j->design_status === 'cleared' ? 'ok' : '' ?>" title="Design clearance">Des</span>
        <span class="jt-gate <?= $j->authorization_status === 'authorized' ? 'ok' : '' ?>" title="Print authorization">Auth</span>
        <span class="jt-gate <?= in_array($j->payment_status,['verified','paid']) ? 'ok' : '' ?>" title="Payment">Pay</span>
      </div>
      <div class="jt-foot">
        <span class="jt-amount"><?= $CI->currency($j->quote_amount, true) ?></span>
        <?php if ($j->due_date): ?><span class="jt-due <?= $overdue ? 'overdue' : '' ?>"><i class="fa fa-clock-o"></i> <?= show_date($j->due_date) ?></span><?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<style>
.print-jobs{font-family:'Inter',sans-serif}
.jt-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
.jt-card{display:block;background:#fff;border:1px solid #d5e3e8;border-radius:12px;padding:14px;text-decoration:none;color:#102a35;box-shadow:0 1px 2px rgba(14,90,110,.05);transition:border-color .15s ease,transform .1s ease}
.jt-card:hover{border-color:#0e7490;text-decoration:none;color:#102a35;transform:translateY(-1px)}
.jt-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px}
.jt-code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;background:#eef5f7;border:1px solid #d5e3e8;border-radius:5px;padding:2px 7px;color:#0b5d73}
.jt-status{font-size:10.5px;font-weight:700;border-radius:999px;padding:2px 9px}
.jt-title{font-size:14px;font-weight:700;line-height:1.35;margin-bottom:4px}
.jt-client{font-size:12px;color:#5b6f78;margin-bottom:10px}
.jt-client i{width:14px;color:#9db2bb}
.jt-gates{display:flex;gap:5px;margin-bottom:10px}
.jt-gate{font-size:10px;font-weight:700;letter-spacing:.03em;border-radius:5px;padding:2px 7px;background:#eef2f4;color:#9db2bb;text-transform:uppercase}
.jt-gate.ok{background:#d1fae5;color:#065f46}
.jt-foot{display:flex;align-items:center;justify-content:space-between;border-top:1px solid #eef5f7;padding-top:9px}
.jt-amount{font-size:14px;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;color:#0b5d73}
.jt-due{font-size:11px;color:#5b6f78}
.jt-due.overdue{color:#dc2626;font-weight:700}
</style>

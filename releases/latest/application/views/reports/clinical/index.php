<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Clinical reports — index. Lists only the reports this viewer's grants allow,
 * each with a one-line description of the question it answers.
 */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$total = count($reports);
?>
<div class="cr-head">
  <div>
    <div class="cr-eyebrow">Reports</div>
    <h1>Clinical reports</h1>
    <p>Operational and financial reporting for the clinic. Only the reports your role can read are listed.</p>
  </div>
</div>

<div class="cr-kpis">
  <div class="cr-kpi">
    <div class="lbl">Available to you</div>
    <div class="val"><?= (int)$total; ?></div>
    <div class="sub">report<?= $total === 1 ? '' : 's'; ?> based on your role</div>
  </div>
  <div class="cr-kpi">
    <div class="lbl">Reporting period</div>
    <div class="val" style="font-size:18px"><?= date('d M Y', strtotime($from)); ?> — <?= date('d M Y', strtotime($to)); ?></div>
    <div class="sub">change the dates inside any report</div>
  </div>
</div>

<?php if(empty($reports)): ?>
  <div class="cr-panel"><div class="cr-empty">Your role does not include any clinical report.</div></div>
<?php else: ?>
  <div class="cr-grid2">
    <?php foreach($reports as $key => $r): ?>
      <a class="cr-panel" style="display:block;text-decoration:none;color:inherit" href="<?= base_url('clinical_reports/' . $key); ?>">
        <div class="cr-panel-body" style="display:flex;gap:13px;padding:16px 17px;align-items:flex-start">
          <span style="width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:10px;background:#e3efe9;color:#104d40;font-size:16px"><i class="fa <?= htmlspecialchars($r['icon']); ?>"></i></span>
          <span style="min-width:0">
            <strong style="display:block;font-size:15px;font-weight:700"><?= htmlspecialchars($r['title']); ?></strong>
            <span style="display:block;margin-top:3px;font-size:13px;color:var(--mp-muted);line-height:1.45"><?= htmlspecialchars($r['sub']); ?></span>
          </span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

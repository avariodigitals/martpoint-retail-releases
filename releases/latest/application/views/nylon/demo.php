<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2>Demo Scenarios</h2>
    <div class="mp-page-sub">Acceptance-test runs for the Nylon &amp; Polythene workflow — realistic data, real postings</div>
  </div>
  <form method="post" action="<?= base_url('nylon/demo'); ?>" onsubmit="return confirm('This will create demo items, customers, orders and jobs with real stock postings. Continue?');">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="run" value="1">
    <button type="submit" class="mp-qa-btn"><i class="fa fa-flask"></i> Run Demo Scenarios</button>
  </form>
</div>

<?php if ($this->session->flashdata('demo_error')): ?>
<div class="mp-card-form" style="margin-bottom:16px;border-left:4px solid #DC2626;"><div class="mp-card-body" style="padding:12px 16px;"><span class="text-danger"><?= htmlspecialchars($this->session->flashdata('demo_error')); ?></span></div></div>
<?php endif; ?>

<?php if (empty($results) && !empty($existing_jobs)): ?>
<div class="mp-card-form"><div class="mp-card-body" style="padding:12px 16px;">
  Demo data already exists — <?= count($existing_jobs); ?> demo job(s) found. Open them from <a href="<?= base_url('nylon/jobs'); ?>">Jobs</a>.
</div></div>
<?php endif; ?>

<?php if (!empty($inventory)): ?>
<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3><i class="fa fa-cubes"></i> Resulting Inventory</h3></div>
  <div class="mp-card-body" style="padding:0!important;">
    <table class="mp-static-table">
      <thead><tr><th>Item</th><th class="text-right">In stock</th><th>Unit</th></tr></thead>
      <tbody>
      <?php foreach ($inventory as $i): ?>
        <tr><td><?= htmlspecialchars($i['name']); ?></td><td class="text-right"><strong><?= format_qty($i['stock']); ?></strong></td><td><small><?= htmlspecialchars($i['unit']); ?></small></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($results)): ?>
<?php foreach ($results as $s): ?>
  <div class="mp-card-form" style="margin-bottom:20px;">
    <div class="mp-card-head"><h3><?= htmlspecialchars($s['scenario']); ?></h3>
      <div style="display:flex;gap:8px;">
        <?php if (!empty($s['job_id'])): ?><a href="<?= base_url('nylon/job_view/'.$s['job_id']); ?>" class="mp-qa-btn" style="font-size:12px;">Open job <?= htmlspecialchars($s['job_code']); ?></a><?php endif; ?>
        <?php if (!empty($s['order_id'])): ?><a href="<?= base_url('nylon/order_view/'.$s['order_id']); ?>" class="mp-qa-btn" style="font-size:12px;background:var(--mp-muted);">Open order</a><?php endif; ?>
      </div>
    </div>
    <div class="mp-card-body">
      <p style="font-size:13px;color:var(--mp-muted);margin-top:0;"><?= htmlspecialchars($s['summary']); ?></p>
      <?php if (!empty($s['report'])): $rp = $s['report']; ?>
      <table class="mp-static-table" style="max-width:640px;">
        <tr><td>Material consumed</td><td class="text-right"><?= $CI->currency($rp['material_cost']); ?></td></tr>
        <tr><td>Other costs (actual)</td><td class="text-right"><?= $CI->currency($rp['act_other']); ?></td></tr>
        <tr><td><strong>Actual total</strong></td><td class="text-right"><strong><?= $CI->currency($rp['act_total']); ?></strong></td></tr>
        <tr><td>Estimated total</td><td class="text-right"><?= $CI->currency($rp['est_total']); ?></td></tr>
        <tr><td>Order value</td><td class="text-right"><?= $rp['revenue'] ? $CI->currency($rp['revenue']) : '— (stock run)'; ?></td></tr>
        <tr><td><strong>Gross margin</strong></td><td class="text-right"><strong class="<?= $rp['margin']>=0?'text-success':'text-danger'; ?>"><?= $rp['revenue'] ? $CI->currency($rp['margin']).($rp['margin_pct']!==null?' ('.$rp['margin_pct'].'%)':'') : '—'; ?></strong></td></tr>
        <tr><td>Yield</td><td class="text-right"><?= $rp['yield_pct']!==null ? $rp['yield_pct'].'% — '.format_qty($rp['produced_qty']).' of '.format_qty($rp['planned_qty']).' planned' : '—'; ?></td></tr>
        <tr><td>Rejects / scrap / waste</td><td class="text-right"><?= format_qty($rp['reject_qty']); ?> / <?= format_qty($rp['scrap_qty']); ?> / <?= format_qty($rp['waste_qty']); ?></td></tr>
      </table>
      <?php else: ?><p class="mp-muted" style="font-size:12px;">No production job for this scenario — see the linked order.</p><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
<?php elseif (empty($existing_jobs)): ?>
<div class="mp-card-form">
  <div class="mp-card-body">
    <p style="font-size:13px;">Click <strong>Run Demo Scenarios</strong> to seed six acceptance tests:</p>
    <ol style="font-size:13px;color:var(--mp-muted);line-height:1.8;">
      <li><strong>Resin → film → bag</strong> — full pipeline, raw material consumed once, rolls created mid-stage, finished goods after QC.</li>
      <li><strong>Purchased roll → bag</strong> — roll bought via Purchase, job skips extrusion.</li>
      <li><strong>Film-roll-only sale</strong> — no job at all, just a customer order for rolls.</li>
      <li><strong>Branded repeat order</strong> — printing blocked until artwork approved; repeat order copies the approved spec.</li>
      <li><strong>Partial production &amp; delivery</strong> — partial output posted, part of the order dispatched, both stay open.</li>
      <li><strong>Rejected output</strong> — rejects logged at cutting, finished stock not overstated.</li>
    </ol>
    <p class="mp-muted" style="font-size:12px;">Each result shows the resulting inventory positions and the job's estimated-vs-actual cost, yield, waste and margin.</p>
  </div>
</div>
<?php endif; ?>

<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance();

$money = function ($v) use ($CI) { return $CI->currency((float) $v); };

$takings_total = 0.0;
$tips_total    = 0.0;
$rounds_total  = 0;
foreach ($report['rows'] as $r) {
    $takings_total += (float) $r->takings;
    $tips_total    += (float) $r->tips;
    $rounds_total  += (int) $r->rounds;
}
?>

<style>
.wt-wrap { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 18px; align-items: start; }
@media (max-width: 1100px) { .wt-wrap { grid-template-columns: minmax(0, 1fr); } }
.wt-panel { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 16px; }
.wt-panel h3 { margin: 0 0 4px; font-size: 15px; }
.wt-empty { padding: 18px 10px; text-align: center; color: var(--mp-muted); font-size: 13px; }
.wt-range { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px; }
.wt-range label { font-size: 12px; color: var(--mp-muted); display: block; margin-bottom: 3px; }
.wt-btn { display: inline-block; border: 1px solid var(--mp-primary); background: var(--mp-surface); color: var(--mp-primary);
  border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; }
.wt-btn:hover { background: var(--mp-bg); }
.wt-lines td, .wt-lines th { font-size: 13px; }
.wt-lines td.num, .wt-lines th.num { text-align: right; }
.wt-who { font-weight: 600; }
.wt-note { font-size: 12px; color: var(--mp-muted); margin-top: 10px; }
.wt-group + .wt-group { margin-top: 16px; border-top: 1px dashed var(--mp-border); padding-top: 12px; }
</style>

<div class="mp-page-head">
  <div>
    <h2><i class="fa fa-user-circle-o"></i> Waiter Takings</h2>
    <div class="mp-page-sub">What each waiter brought in, and what the guests left them.</div>
  </div>
  <div>
    <a class="wt-btn" href="<?= base_url('operations/tabs'); ?>"><i class="fa fa-glass"></i> Back to the floor</a>
  </div>
</div>

<form method="get" class="wt-range">
  <div>
    <label for="from">From</label>
    <input type="date" class="mp-form-control" id="from" name="from" value="<?= htmlspecialchars($from); ?>">
  </div>
  <div>
    <label for="to">To</label>
    <input type="date" class="mp-form-control" id="to" name="to" value="<?= htmlspecialchars($to); ?>">
  </div>
  <div><button type="submit" class="wt-btn"><i class="fa fa-search"></i> Show</button></div>
  <div class="mp-page-sub">Showing <?= htmlspecialchars($from); ?> to <?= htmlspecialchars($to); ?></div>
</form>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-file-o"></i></div>
    <div class="mp-kpi-label">Bills served</div>
    <div class="mp-kpi-value"><?= (int) $rounds_total; ?></div>
  </div>
  <div class="mp-kpi-card">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Store takings</div>
    <div class="mp-kpi-value"><?= $money($takings_total); ?></div>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-heart-o"></i></div>
    <div class="mp-kpi-label">Tips for the floor</div>
    <div class="mp-kpi-value"><?= $money($tips_total); ?></div>
  </div>
  <div class="mp-kpi-card <?= $report['unnamed_tips'] > 0.005 ? 'debt' : 'success'; ?>">
    <div class="mp-kpi-icon"><i class="fa fa-question-circle-o"></i></div>
    <div class="mp-kpi-label">Tips with no waiter named</div>
    <div class="mp-kpi-value"><?= $money($report['unnamed_tips']); ?></div>
  </div>
</div>

<div class="wt-wrap">
  <div class="wt-panel">
    <h3>Per waiter</h3>
    <div class="mp-page-sub">Takings are the bills they served. Tips are what guests left them — the two are never added together.</div>
    <table class="table table-condensed wt-lines">
      <thead>
        <tr>
          <th>Waiter</th>
          <th class="num" style="width:90px;">Rounds</th>
          <th class="num" style="width:130px;">Takings</th>
          <th class="num" style="width:120px;">Tips</th>
          <th class="num" style="width:130px;">Service charge</th>
        </tr>
      </thead>
      <tbody>
        <?php $shown = 0; foreach (array_slice($report['rows'], 0, 20) as $r): $shown++; ?>
        <tr>
          <td class="wt-who"><?= htmlspecialchars((string) $r->waiter_name); ?></td>
          <td class="num"><?= (int) $r->rounds; ?></td>
          <td class="num"><?= $money($r->takings); ?></td>
          <td class="num"><?= $money($r->tips); ?></td>
          <td class="num"><?= $money($r->charges); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$shown): ?>
        <tr><td colspan="5"><div class="wt-empty">No rounds were served in this period.</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <?php if (count($report['rows']) > 20): ?>
      <div class="wt-note">Showing the first 20 of <?= (int) count($report['rows']); ?>. Narrow the dates for the rest.</div>
    <?php endif; ?>
    <div class="wt-note">
      "Not named" means the bill was never given a waiter. Name them on the round and the money moves to their line.
    </div>
  </div>

  <?php if ($show_exceptions): ?>
  <div class="wt-panel">
    <h3>Given away &amp; pulled off</h3>
    <div class="mp-page-sub">What the house paid for, and what never reached a guest.</div>

    <div class="wt-group">
      <strong>On the house</strong>
      <table class="table table-condensed wt-lines">
        <tbody>
          <?php foreach (array_slice($exceptions['comps'], 0, 8) as $c): ?>
          <tr>
            <td><?= htmlspecialchars((string) ($c->reason ?: 'No reason recorded')); ?></td>
            <td class="num"><?= (int) $c->item_count; ?> item(s)</td>
            <td class="num"><?= $money($c->amount); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($exceptions['comps'])): ?>
          <tr><td><div class="wt-empty">Nothing given away in this period.</div></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="wt-note">Total given away: <b><?= $money($exceptions['comp_total']); ?></b></div>
    </div>

    <div class="wt-group">
      <strong>Voided rounds</strong>
      <table class="table table-condensed wt-lines">
        <tbody>
          <?php foreach (array_slice($exceptions['voids'], 0, 8) as $v): ?>
          <tr>
            <td><?= htmlspecialchars((string) ($v->reason ?: 'No reason recorded')); ?></td>
            <td class="num"><?= (int) $v->rounds; ?> round(s)</td>
            <td class="num"><?= $money($v->amount); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($exceptions['voids'])): ?>
          <tr><td><div class="wt-empty">No round was pulled off in this period.</div></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="wt-note">Total off the bill: <b><?= $money($exceptions['void_total']); ?></b> — the stock went back on the shelf.</div>
    </div>
  </div>
  <?php endif; ?>
</div>

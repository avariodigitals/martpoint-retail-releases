<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $CI =& get_instance();
  $fmt = function ($v) { return rtrim(rtrim(number_format((float) $v, 2), '0'), '.'); };
  $pct = function ($a, $b) { return $b > 0 ? round(((float) $a / (float) $b) * 100, 1) : 0; };
?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>Equipment Report</h2>
    <div class="mp-page-sub">
      What each machine actually produced over the period — output, rejects and waste per press.
      Read alongside Maintenance for machines that are producing badly AND are overdue a service.
    </div>
    <a href="<?= base_url('printing_ops/machines') ?>" class="btn btn-default"><i class="fa fa-cog"></i> Manage Machines</a>
  </div>

  <?php
    // Summary across every machine, so the headline answers "is the floor
    // producing" before the operator reads a single row.
    $t_runs = 0; $t_good = 0; $t_in = 0; $t_rej = 0; $t_waste = 0;
    foreach ((array) $report as $r) {
        $t_runs  += (int) ($r['runs'] ?? 0);
        $t_good  += (float) ($r['accepted'] ?? 0);
        $t_in    += (float) ($r['input_qty'] ?? 0);
        $t_rej   += (float) ($r['rejects'] ?? 0);
        $t_waste += (float) ($r['waste'] ?? 0);
    }
  ?>
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px">
    <div class="mp-card" style="flex:1 1 140px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Runs</div>
      <div style="font-size:22px;font-weight:700"><?= number_format($t_runs) ?></div>
    </div></div>
    <div class="mp-card" style="flex:1 1 140px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Accepted output</div>
      <div style="font-size:22px;font-weight:700;color:#059669"><?= $fmt($t_good) ?></div>
    </div></div>
    <div class="mp-card" style="flex:1 1 140px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Rejects</div>
      <div style="font-size:22px;font-weight:700;color:<?= $t_rej > 0 ? '#DC2626' : '#78716C' ?>"><?= $fmt($t_rej) ?></div>
      <div style="font-size:11px;color:#78716C"><?= $pct($t_rej, $t_in) ?>% of input</div>
    </div></div>
    <div class="mp-card" style="flex:1 1 140px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Waste</div>
      <div style="font-size:22px;font-weight:700;color:<?= $t_waste > 0 ? '#D97706' : '#78716C' ?>"><?= $fmt($t_waste) ?></div>
      <div style="font-size:11px;color:#78716C"><?= $pct($t_waste, $t_in) ?>% of input</div>
    </div></div>
    <div class="mp-card" style="flex:1 1 140px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Yield</div>
      <div style="font-size:22px;font-weight:700;color:#0E7490"><?= $pct($t_good, $t_in) ?>%</div>
      <div style="font-size:11px;color:#78716C">good vs input</div>
    </div></div>
  </div>

  <?php if (!empty($due)): ?>
  <div class="mp-card" style="border-color:#FDE68A;background:#FFFBEB;margin-bottom:14px">
    <div class="mp-card-body" style="padding:12px 14px">
      <b style="color:#92400E"><i class="fa fa-wrench"></i> <?= count($due) ?> machine<?= count($due) === 1 ? '' : 's' ?> overdue a service</b>
      <div style="font-size:12px;color:#92400E;margin-top:4px">
        Cross-check this against the yield column — a press that is due a service and also producing poorly is the one to attend to first.
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-card"><div class="mp-card-body" style="padding:0">
    <?php if (empty($report)): ?>
      <div class="mp-empty-state" style="padding:30px">
        No machines registered yet. <a href="<?= base_url('printing_ops/machines') ?>">Add a machine</a> to start tracking output.
      </div>
    <?php else: ?>
      <table class="table" style="margin:0">
        <thead>
          <tr>
            <th>Machine</th>
            <th style="width:90px" class="text-right">Runs</th>
            <th style="width:110px" class="text-right">Input</th>
            <th style="width:110px" class="text-right">Accepted</th>
            <th style="width:100px" class="text-right">Rejects</th>
            <th style="width:100px" class="text-right">Waste</th>
            <th style="width:90px" class="text-right">Yield</th>
            <th style="width:120px">Last service</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ((array) $report as $r):
          $yield = $pct($r['accepted'] ?? 0, $r['input_qty'] ?? 0);
          // Yield colour: green is healthy, amber is worth a look, red means
          // the press is losing most of what it takes in.
          $yc = $yield >= 90 ? '#059669' : ($yield >= 75 ? '#D97706' : '#DC2626');
          $mid = (int) ($r['machine']->id ?? 0);
        ?>
          <tr>
            <td>
              <?php if ($mid): ?>
                <a href="<?= base_url('printing_ops/machine/' . $mid) ?>" style="font-weight:600"><?= htmlspecialchars($r['machine']->name ?? 'Machine') ?></a>
              <?php else: ?>
                <b><?= htmlspecialchars($r['machine']->name ?? 'Machine') ?></b>
              <?php endif; ?>
              <div style="font-size:11.5px;color:#78716C">
                <?= htmlspecialchars(trim(($r['machine']->manufacturer ?? '') . ' ' . ($r['machine']->model ?? ''))) ?: '—' ?>
                <?php if (!empty($r['machine']->machine_code)): ?> · <?= htmlspecialchars($r['machine']->machine_code) ?><?php endif; ?>
              </div>
            </td>
            <td class="text-right"><?= number_format((int) ($r['runs'] ?? 0)) ?></td>
            <td class="text-right"><?= $fmt($r['input_qty'] ?? 0) ?></td>
            <td class="text-right"><b style="color:#059669"><?= $fmt($r['accepted'] ?? 0) ?></b></td>
            <td class="text-right" style="color:<?= ((float) ($r['rejects'] ?? 0)) > 0 ? '#DC2626' : '#78716C' ?>"><?= $fmt($r['rejects'] ?? 0) ?></td>
            <td class="text-right" style="color:<?= ((float) ($r['waste'] ?? 0)) > 0 ? '#D97706' : '#78716C' ?>"><?= $fmt($r['waste'] ?? 0) ?></td>
            <td class="text-right"><b style="color:<?= $yc ?>"><?= $yield ?>%</b></td>
            <td style="font-size:12px">
              <?= !empty($r['machine']->last_service_at) ? show_date($r['machine']->last_service_at) : '<span class="text-muted">never</span>' ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div></div>

  <p class="text-muted" style="font-size:11.5px;margin-top:10px">
    Input is what the press took in, accepted is what passed, and rejects plus waste are what did not.
    Yield is accepted as a share of input. A machine with a low yield and a service overdue is the
    first place to look.
  </p>
</div>

<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $CI =& get_instance();
  $fmt = function ($v) { return rtrim(rtrim(number_format((float) $v, 2), '0'), '.'); };

  // Totals across every material line this client has placed with us, split
  // into "still with us" vs "accounted for" — the two questions a client
  // actually asks: where is my stock, and what have you used.
  $tot_in = 0; $tot_remaining = 0; $tot_done = 0;
  foreach ((array) $materials as $m) {
      $tot_in += (float) ($m->qty_received ?? 0);
      $tot_remaining += (float) ($m->qty_custody ?? 0) + (float) ($m->qty_in_production ?? 0)
                      + (float) ($m->qty_finished ?? 0) + (float) ($m->qty_damaged ?? 0);
      $tot_done += (float) ($m->qty_returned ?? 0) + (float) ($m->qty_collected_finished ?? 0)
                  + (float) ($m->qty_consumed ?? 0);
  }
?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>Material Statement</h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars($customer->customer_name ?? ('Client #' . (int) $customer_id)) ?>
      <?php if (!empty($customer->mobile)): ?> · <?= htmlspecialchars($customer->mobile) ?><?php endif; ?>
    </div>
    <a href="<?= base_url('printing_ops/custody') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
  </div>

  <div class="mp-card" style="margin-bottom:14px"><div class="mp-card-body" style="padding:14px">
    <div style="display:flex;gap:30px;flex-wrap:wrap">
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Brought in</div>
        <div style="font-size:20px;font-weight:700"><?= $fmt($tot_in) ?></div>
      </div>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Still with us</div>
        <div style="font-size:20px;font-weight:700;color:#0E7490"><?= $fmt($tot_remaining) ?></div>
      </div>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Accounted for</div>
        <div style="font-size:20px;font-weight:700;color:#059669"><?= $fmt($tot_done) ?></div>
        <div style="font-size:10.5px;color:#78716C">returned, collected or used</div>
      </div>
    </div>
  </div></div>

  <div class="mp-card"><div class="mp-card-body" style="padding:0">
    <?php if (empty($materials)): ?>
      <div class="mp-empty-state" style="padding:30px">Nothing in custody for this client.</div>
    <?php else: ?>
      <table class="table" style="margin:0">
        <thead>
          <tr>
            <th>Material</th>
            <th style="width:110px">Job</th>
            <th style="width:100px">Brought in</th>
            <th style="width:250px">Where it is now</th>
            <th style="width:110px">Remaining</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ((array) $materials as $m):
          $states = array_filter([
              'In our custody' => (float) ($m->qty_custody ?? 0),
              'In production'  => (float) ($m->qty_in_production ?? 0),
              'Finished'       => (float) ($m->qty_finished ?? 0),
              'Damaged'        => (float) ($m->qty_damaged ?? 0),
          ], function ($v) { return $v > 0; });
          $remaining = array_sum($states);
          $done = (float) ($m->qty_returned ?? 0) + (float) ($m->qty_collected_finished ?? 0)
                + (float) ($m->qty_consumed ?? 0);
        ?>
          <tr>
            <td>
              <b><?= htmlspecialchars($m->material_name ?? '—') ?></b>
              <?php if (!empty($m->material_type)): ?> <small class="text-muted"><?= htmlspecialchars($m->material_type) ?></small><?php endif; ?>
              <?php if (!empty($m->receipt_code)): ?>
                <div style="font-size:11px;color:#78716C">receipt <?= htmlspecialchars($m->receipt_code) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($m->job_code)): ?>
                <a href="<?= base_url('printing/job/' . (int) $m->job_id) ?>"><?= htmlspecialchars($m->job_code) ?></a>
              <?php else: ?><span class="text-muted">unallocated</span><?php endif; ?>
            </td>
            <td><b><?= $fmt($m->qty_received ?? 0) ?></b> <?php if (!empty($m->unit_label)): ?><small class="text-muted"><?= htmlspecialchars($m->unit_label) ?></small><?php endif; ?></td>
            <td>
              <?php if ($states): foreach ($states as $label => $qty): ?>
                <span style="display:inline-block;font-size:11px;background:#E0F2FE;color:#0E7490;border-radius:20px;padding:2px 8px;margin:1px 3px 1px 0;white-space:nowrap">
                  <?= $fmt($qty) ?> <?= htmlspecialchars($label) ?>
                </span>
              <?php endforeach; else: ?>
                <span class="text-muted">nothing left with us</span>
              <?php endif; ?>
              <?php if ($done > 0): ?>
                <div style="font-size:11px;color:#78716C;margin-top:2px"><?= $fmt($done) ?> returned / collected / used</div>
              <?php endif; ?>
            </td>
            <td><b style="font-size:15px;color:<?= $remaining > 0 ? '#0E7490' : '#78716C' ?>"><?= $fmt($remaining) ?></b></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div></div>

  <?php if (!empty($unalloc)): ?>
  <div class="mp-card" style="margin-top:14px"><div class="mp-card-body" style="padding:0">
    <div style="padding:12px 14px;border-bottom:1px solid #E7E5E4;font-weight:700;font-size:13px">
      Not yet allocated to a job (<?= count($unalloc) ?>)
    </div>
    <table class="table" style="margin:0">
      <thead><tr><th>Material</th><th style="width:100px">Brought in</th><th style="width:120px">Received</th></tr></thead>
      <tbody>
      <?php foreach ($unalloc as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u->material_name ?? '—') ?></td>
          <td><?= $fmt($u->qty_received ?? 0) ?> <?php if (!empty($u->unit_label)): ?><small class="text-muted"><?= htmlspecialchars($u->unit_label) ?></small><?php endif; ?></td>
          <td><?= !empty($u->received_at) ? show_date($u->received_at) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
  <?php endif; ?>
</div>

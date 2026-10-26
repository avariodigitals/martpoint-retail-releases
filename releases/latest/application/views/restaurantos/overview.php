<?php
$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?? 'MartPoint';
$has_kitchen = mp_feature_enabled('kitchen_workflow');
$has_tables  = ($tables !== null);

/* Links that keep the current view settings (range + outlet). */
$ro_url = function ($path, $overrides = []) {
    $q = array_merge($_GET, $overrides);
    $keep = [];
    foreach (['range', 'branch_id'] as $k) {
        if (isset($q[$k]) && $q[$k] !== '') $keep[$k] = $q[$k];
    }
    return base_url($path) . ($keep ? '?' . http_build_query($keep) : '');
};

$compare_label = [
    'Today'     => 'vs yesterday',
    '7Days'     => 'vs the previous 7 days',
    '30Days'    => 'vs the previous 30 days',
    'ThisMonth' => 'vs last month',
][$range_key];

$pay_text = function ($status, $settled) {
    $map = ['paid' => 'Settled', 'partial' => 'Part-paid', 'partially_paid' => 'Part-paid',
            'unpaid' => 'Unpaid', 'failed' => 'Failed'];
    $key = strtolower((string) $status);
    if (isset($map[$key])) return $map[$key];
    return $settled ? 'Settled' : 'Unpaid';
};

$prep_text = ['new' => 'Queued', 'preparing' => 'Cooking', 'ready' => 'Ready', 'ready_now' => 'Ready now', 'served' => 'Handed over'];
$branch_all_label = !empty($branch_can_view_all) ? 'All branches' : 'All assigned branches';
$online_waiting = (int) ($online['open'] ?? 0);
$online_ready = (int) ($online['ready'] ?? 0);
$kitchen_active = (int) (($kds_live['new'] ?? 0) + ($kds_live['preparing'] ?? 0));
$kitchen_ready = (int) ($kds_live['ready'] ?? 0);
$kitchen_backlog = (int) ($kds_stale ?? 0);

$when_text = function ($datetime) {
    $ts = strtotime($datetime);
    if (!$ts) return '';
    if (date('Y-m-d', $ts) === date('Y-m-d')) return date('H:i', $ts);
    if (date('Y', $ts) === date('Y')) return date('j M', $ts);
    return date('j M Y', $ts);
};
?>
<?= $this->load->view('restaurantos/_assets', [], TRUE); ?>

<div class="ro-wrap ro-stack">

  <!-- ============ Header: what I'm looking at, and what changes it ============ -->
  <div class="ro-head">
    <div>
      <h2>Overview</h2>
      <div class="ro-head-sub">
        <span class="ro-context-item"><?= htmlspecialchars($store_name); ?></span>
        <span class="ro-context-item"><?= htmlspecialchars($range_label); ?></span>
        <?php $outlet_name = '';
        if ($branch) {
            foreach ($branch_opts as $o) { if ((string) $o['id'] === (string) $branch) $outlet_name = $o['name']; }
        } ?>
        <?php if ($outlet_name !== '' || $branch_opts): ?>
          <span class="ro-context-item"><?= $outlet_name !== '' ? htmlspecialchars($outlet_name) : htmlspecialchars($branch_all_label); ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="ro-head-tools">
      <?php if (!empty($branch_opts)): ?>
        <div class="ro-seg">
          <span class="ro-seg-label">Outlet</span>
          <a class="<?= $branch === '' ? 'active' : ''; ?>" href="<?= $ro_url('restaurantos', ['branch_id' => '']); ?>"><?= htmlspecialchars($branch_all_label); ?></a>
          <?php foreach ($branch_opts as $o): ?>
            <a class="<?= (string) $branch === (string) $o['id'] ? 'active' : ''; ?>"
               href="<?= $ro_url('restaurantos', ['branch_id' => $o['id']]); ?>"><?= htmlspecialchars($o['name']); ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="ro-seg">
        <span class="ro-seg-label">Period</span>
        <?php foreach ($ranges as $key => $label): ?>
          <a class="<?= $range_key === $key ? 'active' : ''; ?>"
             href="<?= $ro_url('restaurantos', ['range' => $key]); ?>"><?= htmlspecialchars($label); ?></a>
        <?php endforeach; ?>
      </div>
      <a href="<?= base_url('pos'); ?>" class="ro-act" style="padding:8px 14px;">
        <span class="ic"><i class="fa fa-shopping-cart"></i></span><span class="tx"><b>Open Walk In</b></span>
      </a>
      <?php if (!empty($can_settings)): ?>
        <a href="<?= base_url('restaurantos/settings'); ?>" class="ro-act slate" style="padding:8px 14px;">
          <span class="ic"><i class="fa fa-cog"></i></span><span class="tx"><b>Restaurant Setup</b></span>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Live queues: one glance, one action. Detailed explanations stay below. -->
  <div class="ro-live-strip" aria-label="Live restaurant queues">
    <a class="ro-live-card <?= $online_waiting > 0 ? 'attention' : ($online_ready > 0 ? 'moving' : 'clear'); ?>" href="<?= base_url('online_store/orders'); ?>">
      <div class="ro-live-top">
        <span class="ro-live-title"><span class="ro-live-icon online"><i class="fa fa-globe"></i></span> Online orders</span>
        <span class="ro-live-status"><i></i><?= $online_waiting > 0 ? 'Needs attention' : ($online_ready > 0 ? 'Ready to hand over' : 'Clear'); ?></span>
      </div>
      <div class="ro-live-value"><strong><?= number_format($online_waiting + $online_ready); ?></strong><span>open<br>orders</span></div>
      <div class="ro-live-breakdown">
        <span><i class="new"></i><?= number_format($online_waiting); ?> being prepared</span>
        <span><i class="ready"></i><?= number_format($online_ready); ?> ready now</span>
      </div>
      <div class="ro-live-foot"><span>Prepared or ready for handover</span><b>Open orders&nbsp; →</b></div>
    </a>

    <?php if ($has_kitchen): ?>
      <a class="ro-live-card <?= $kitchen_backlog > 0 ? 'attention' : ($kitchen_active > 0 ? 'moving' : 'clear'); ?>" href="<?= base_url('operations/kitchen'); ?>">
        <div class="ro-live-top">
          <span class="ro-live-title"><span class="ro-live-icon kitchen"><i class="fa fa-fire"></i></span> Kitchen backlog</span>
          <span class="ro-live-status"><i></i><?= $kitchen_backlog > 0 ? 'Needs clearing' : ($kitchen_active > 0 ? 'In service' : 'Clear'); ?></span>
        </div>
        <div class="ro-live-value"><strong><?= number_format($kitchen_backlog); ?></strong><span>older tickets<br>still open</span></div>
        <div class="ro-live-breakdown">
          <span><i class="new"></i><?= number_format($kitchen_active); ?> active now</span>
          <span><i class="ready"></i><?= number_format($kitchen_ready); ?> ready</span>
        </div>
        <div class="ro-live-foot"><span>Outside the live <?= (int) $live_hours; ?>h window</span><b>Open kitchen&nbsp; →</b></div>
      </a>
    <?php endif; ?>
  </div>

  <!-- ============ What needs attention right now ============ -->
  <?php foreach ($insights as $note): ?>
    <?php if (stripos((string) $note['label'], 'Online orders waiting') !== false || stripos((string) $note['label'], 'Kitchen backlog') !== false) continue; ?>
    <div class="ro-banner restaurant-insight <?= $note['level']; ?> <?= stripos((string) $note['label'], 'online order') !== false ? 'restaurant-online-alert' : ''; ?>">
      <span class="bi"><i class="fa <?= htmlspecialchars($note['icon']); ?>"></i></span>
      <span class="bb"><b><?= htmlspecialchars($note['label']); ?></b><span class="muted"><?= htmlspecialchars($note['text']); ?></span></span>
      <a class="bl" href="<?= base_url($note['link']); ?>"><?= htmlspecialchars($note['link_label']); ?> <span aria-hidden="true">&rarr;</span></a>
    </div>
  <?php endforeach; ?>

  <!-- ============ Sales, tickets and queues ============ -->
  <div class="ro-kpis ro-kpis-primary">
    <div class="ro-kpi">
      <div class="ro-kpi-top"><span class="ro-kpi-ic"><i class="fa fa-money"></i></span><span class="ro-kpi-label">Total revenue</span></div>
      <div class="ro-kpi-val"><?= $CI->currency($sales['total']); ?></div>
      <div class="ro-kpi-foot">
        <?php $chg = (float) $perf['change']; ?>
        <?php if ((float) $perf['yesterday'] > 0): ?>
          <span class="ro-chip <?= $chg >= 0 ? 'up' : 'down' ?>">
            <i class="fa <?= $chg >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i><?= number_format(abs($chg), 1); ?>%
          </span>
        <?php else: ?>
          <span class="ro-chip neutral">No comparison</span>
        <?php endif; ?>
        <span class="ro-kpi-note"><?= (float) $perf['yesterday'] > 0 ? $CI->currency($perf['yesterday']) . ' ' . htmlspecialchars($compare_label) : 'Nothing rang up ' . htmlspecialchars($compare_label); ?></span>
      </div>
      <div class="ro-kpi-foot" style="display:block;margin-top:4px;">
        <span class="ro-chip up">Paid <?= $CI->currency($sales['paid']); ?></span>
        <span class="ro-chip neutral">Outstanding <?= $CI->currency($sales['outstanding']); ?></span>
      </div>
    </div>

    <div class="ro-kpi">
      <div class="ro-kpi-top"><span class="ro-kpi-ic"><i class="fa fa-list-alt"></i></span><span class="ro-kpi-label">Orders</span></div>
      <div class="ro-kpi-val"><?= number_format($counter['cnt'] + $online['cnt']); ?></div>
      <div class="ro-kpi-foot">
        <span class="ro-chip neutral"><?= number_format($counter['cnt']); ?> walk-in</span>
        <span class="ro-chip neutral"><?= number_format($online['cnt']); ?> online</span>
      </div>
    </div>

    <div class="ro-kpi">
      <div class="ro-kpi-top"><span class="ro-kpi-ic"><i class="fa fa-calculator"></i></span><span class="ro-kpi-label">Average ticket</span></div>
      <div class="ro-kpi-val"><?= $CI->currency($avg_ticket); ?></div>
      <div class="ro-kpi-foot"><span class="ro-kpi-note">
        <?= $counter['cnt'] > 0 ? 'Across ' . number_format($counter['cnt']) . ' walk-in invoice' . ($counter['cnt'] === 1 ? '' : 's') : 'No walk-in invoices yet'; ?>
      </span></div>
    </div>

    <div class="ro-kpi">
      <div class="ro-kpi-top"><span class="ro-kpi-ic"><i class="fa fa-cutlery"></i></span><span class="ro-kpi-label">Dine-in tickets</span></div>
      <div class="ro-kpi-val"><?= number_format($counter['dinein']); ?></div>
      <div class="ro-kpi-foot"><span class="ro-kpi-note">
        <?= $counter['cnt'] > 0 ? round(($counter['dinein'] / $counter['cnt']) * 100) . '% of walk-in orders sat at a table' : 'Nothing rang up yet'; ?>
      </span></div>
    </div>

  </div>

  <div class="ro-kpis ro-kpis-secondary">
    <?php if ($has_kitchen): ?>
      <div class="ro-kpi">
        <div class="ro-kpi-top"><span class="ro-kpi-ic amber"><i class="fa fa-fire"></i></span><span class="ro-kpi-label">In the kitchen</span></div>
        <div class="ro-kpi-val"><?= (int) ($kds_live['new'] + $kds_live['preparing']); ?></div>
        <div class="ro-kpi-foot">
          <span class="ro-chip neutral"><?= (int) $kds_live['new']; ?> new</span>
          <span class="ro-chip neutral"><?= (int) $kds_live['preparing']; ?> cooking</span>
          <?php if ($kds_stale > 0): ?>
            <a class="ro-kpi-note" style="color:var(--mp-warning,#B45309);" href="<?= base_url('operations/kitchen'); ?>">
              + <?= number_format($kds_stale); ?> older, unclosed</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="ro-kpi">
        <div class="ro-kpi-top"><span class="ro-kpi-ic green"><i class="fa fa-check-circle"></i></span><span class="ro-kpi-label">Ready for handover</span></div>
        <div class="ro-kpi-val"><?= (int) $kds_live['ready']; ?></div>
        <div class="ro-kpi-foot"><span class="ro-kpi-note">In the pass this service</span></div>
      </div>
    <?php endif; ?>

    <div class="ro-kpi">
      <div class="ro-kpi-top"><span class="ro-kpi-ic amber"><i class="fa fa-money"></i></span><span class="ro-kpi-label">Open balances</span></div>
      <div class="ro-kpi-val"><?= number_format($counter['open_cnt']); ?></div>
      <div class="ro-kpi-foot"><span class="ro-kpi-note"><?= $CI->currency($counter['open_total']); ?> not settled</span></div>
    </div>

    <div class="ro-kpi">
      <div class="ro-kpi-top"><span class="ro-kpi-ic red"><i class="fa fa-exclamation-triangle"></i></span><span class="ro-kpi-label">Low stock</span></div>
      <div class="ro-kpi-val"><?= number_format($low_stock); ?></div>
      <div class="ro-kpi-foot">
        <span class="ro-kpi-note">At or below reorder level</span>
        <?php if ($low_stock > 0): ?><a class="ro-panel-link" href="<?= base_url('warehouse'); ?>">Restock &rarr;</a><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ============ Where the service is ============ -->
  <?php if ($has_kitchen || $has_tables): ?>
    <div class="ro-panel">
      <div class="ro-panel-head">
        <div>
          <div class="t"><i class="fa fa-desktop"></i> Live service board</div>
          <div class="s">What the kitchen and the floor are doing at this moment</div>
        </div>
        <?php if ($has_kitchen): ?><a class="ro-panel-link" href="<?= base_url('operations/kitchen'); ?>">Kitchen display &rarr;</a><?php endif; ?>
      </div>
      <div class="ro-panel-body">
        <div class="ro-cols">
          <?php if ($has_kitchen): ?>
            <div class="ro-panel">
              <div class="ro-panel-head">
                <div class="t">On the fire <span class="ro-chip blue"><?= count($cooking); ?></span></div>
                <div class="s">Oldest first · last <?= (int) $live_hours; ?> hours</div>
              </div>
              <div class="ro-panel-body tight">
                <?php if (empty($cooking)): ?>
                  <div class="ro-empty"><i class="fa fa-coffee"></i><div class="t">Nothing cooking</div><div class="s">The queue is clear right now.</div></div>
                <?php else: ?>
                  <div class="ro-tickets">
                    <?php foreach ($cooking as $t): ?>
                      <a class="ro-ticket <?= htmlspecialchars($t['status']); ?>" href="<?= base_url('operations/kitchen'); ?>">
                        <span class="dot"></span>
                        <span class="ref"><?= htmlspecialchars($t['ref']); ?></span>
                        <span class="who"><?= htmlspecialchars($t['who']); ?></span>
                        <span class="ro-age <?= $t['minutes'] >= 30 ? 'hot' : ($t['minutes'] >= 15 ? 'warn' : ''); ?>"><?= htmlspecialchars($t['age']); ?></span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="ro-panel">
              <div class="ro-panel-head">
                <div class="t">Ready to go <span class="ro-chip up"><?= count($ready); ?></span></div>
                <div class="s">Plated, waiting to be handed over</div>
              </div>
              <div class="ro-panel-body tight">
                <?php if (empty($ready)): ?>
                  <div class="ro-empty"><i class="fa fa-bell-o"></i><div class="t">Pass is empty</div><div class="s">No completed tickets waiting.</div></div>
                <?php else: ?>
                  <div class="ro-tickets">
                    <?php foreach ($ready as $t): ?>
                      <a class="ro-ticket ready" href="<?= base_url('operations/kitchen'); ?>">
                        <span class="dot"></span>
                        <span class="ref"><?= htmlspecialchars($t['ref']); ?></span>
                        <span class="who"><?= htmlspecialchars($t['who']); ?></span>
                        <span class="ro-age <?= $t['minutes'] >= 10 ? 'warn' : ''; ?>"><?= htmlspecialchars($t['age']); ?></span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($has_tables): ?>
            <div class="ro-panel">
              <div class="ro-panel-head">
                <div class="t"><i class="fa fa-table"></i> The floor</div>
                <div class="s">
                  <?= (int) $tables['occupied']; ?> of <?= (int) $tables['total']; ?> tables taken
                  <?php if (!empty($table_rows)): ?>
                    · <?= number_format(array_sum(array_map(function ($r) { return $r['status'] === 'occupied' ? $r['capacity'] : 0; }, $table_rows))); ?> seats in use
                  <?php endif; ?>
                </div>
                <a class="ro-panel-link" href="<?= base_url('operations/table_management'); ?>">Manage &rarr;</a>
              </div>
              <div class="ro-panel-body">
                <?php if (empty($table_rows)): ?>
                  <div class="ro-empty"><i class="fa fa-plus-square"></i><div class="t">No tables yet</div><div class="s">Add your floor plan from Table Management.</div></div>
                <?php else: ?>
                  <div class="ro-tblgrid">
                    <?php foreach ($table_rows as $r): ?>
                      <div class="ro-tbl <?= htmlspecialchars($r['status']); ?>">
                        <div class="n"><?= htmlspecialchars($r['name']); ?></div>
                        <div class="z"><?= htmlspecialchars($r['zone'] !== '' ? $r['zone'] : ($r['capacity'] ? $r['capacity'] . ' seats' : '—')); ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="ro-legend">
                    <span><i style="background:var(--mp-success,#059669);"></i>Available <?= (int) $tables['available']; ?></span>
                    <span><i style="background:var(--mp-primary,#5E4D66);"></i>Occupied <?= (int) $tables['occupied']; ?></span>
                    <span><i style="background:var(--mp-warning,#F59E0B);"></i>Reserved <?= (int) $tables['reserved']; ?></span>
                    <span><i style="background:#A8A29E;"></i>Cleaning <?= (int) $tables['cleaning']; ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- ============ How the trade shaped up ============ -->
  <div class="ro-cols">
    <div class="ro-panel">
      <div class="ro-panel-head">
        <div>
          <div class="t"><i class="fa fa-clock-o"></i> Takings by service period</div>
          <div class="s">
            <?php if (!empty($day_parts['peak'])): ?>
              Busiest service period: <b style="color:var(--mp-text);"><?= htmlspecialchars($day_parts['peak']); ?></b>
            <?php else: ?>
              Time-of-day sales grouping — not a channel
            <?php endif; ?>
          </div>
        </div>
        <a class="ro-panel-link" href="<?= base_url('reports/sales'); ?>">Sales report &rarr;</a>
      </div>
      <div class="ro-panel-body tight">
        <?php if (empty($day_parts['rows']) || (float) $day_parts['sum'] <= 0): ?>
          <div class="ro-empty"><i class="fa fa-clock-o"></i><div class="t">No takings in this period</div><div class="s">Nothing has been rung up at the Walk In desk yet.</div></div>
        <?php else: ?>
          <?php $daypart_max = 0; foreach ($day_parts['rows'] as $d) { $daypart_max = max($daypart_max, (float) $d['total']); } ?>
          <div class="ro-service-breakdown" aria-label="Takings by service period">
            <div class="ro-service-summary">
              <span>Sales mix</span>
              <b><?= $CI->currency($day_parts['sum']); ?> total</b>
            </div>
            <div class="ro-service-period-key">Breakfast 00:00–10:59 · Lunch 11:00–14:59 · Dinner 15:00–20:59 · Late Night 21:00–23:59</div>
            <div class="ro-service-list">
              <?php foreach ($day_parts['rows'] as $d): ?>
                <?php $bar_width = $daypart_max > 0 ? max(4, round(((float) $d['total'] / $daypart_max) * 100)) : 0; ?>
                <div class="ro-service-row">
                  <div class="ro-service-row-head">
                    <span class="ro-service-name"><span class="ro-service-dot"></span><?= htmlspecialchars($d['name']); ?></span>
                    <strong><?= $CI->currency($d['total']); ?></strong>
                  </div>
                  <div class="ro-service-track" role="progressbar" aria-label="<?= htmlspecialchars($d['name']); ?> share of service takings" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $d['pct']; ?>">
                    <span style="width:<?= (int) $bar_width; ?>%;"></span>
                  </div>
                  <div class="ro-service-row-meta">
                    <span><?= number_format($d['cnt']); ?> <?= (int) $d['cnt'] === 1 ? 'order' : 'orders'; ?></span>
                    <span><?= (int) $d['pct']; ?>% of takings</span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($outlets)): ?>
      <div class="ro-panel">
        <div class="ro-panel-head">
          <div>
          <div class="t"><i class="fa fa-map-marker"></i> Outlet sales</div>
          <div class="s">Sales per outlet for <?= htmlspecialchars($range_label); ?> · click to focus this board</div>
          </div>
          <a class="ro-panel-link" href="<?= base_url('warehouse'); ?>">Manage &rarr;</a>
        </div>
        <div class="ro-panel-body tight">
          <table class="ro-table">
            <thead><tr><th>Outlet</th><th class="num">Orders</th><th class="num">Takings</th><th style="width:96px;">Share</th></tr></thead>
            <tbody>
              <?php foreach ($outlets as $o): ?>
                <tr>
                  <td><a class="ro-name" style="color:var(--mp-text);text-decoration:none;"
                         href="<?= $ro_url('restaurantos', ['branch_id' => $o['id']]); ?>"><?= htmlspecialchars($o['name']); ?></a></td>
                  <td class="num"><?= number_format($o['orders']); ?></td>
                  <td class="num"><span class="ro-pill rev"><?= $CI->currency($o['takings']); ?></span></td>
                  <td><span class="ro-bar green"><span style="width:<?= (int) $o['pct']; ?>%;"></span></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <div class="ro-panel">
      <div class="ro-panel-head">
        <div>
          <div class="t"><i class="fa fa-pie-chart"></i> Where the orders came from</div>
          <div class="s">Each channel keeps its own book — this puts them side by side</div>
        </div>
        <a class="ro-panel-link" href="<?= $ro_url('restaurantos/orders', ['range' => $range_key]); ?>">All orders &rarr;</a>
      </div>
      <div class="ro-panel-body tight">
        <?php
        $chan_total = (float) $sales['total'];
        $channels = [];
        foreach ($sales['channels'] as $name => $c) {
            $channels[] = ['name' => $name, 'cnt' => $c['cnt'], 'sum' => $c['total'],
                'note' => $c['outstanding'] > 0 ? 'Outstanding ' . $CI->currency($c['outstanding']) : 'Paid in full',
                'bar' => $name === 'Online' ? 'green' : ($name === 'QR' ? 'orange' : '')];
        }
        ?>
        <table class="ro-table">
          <thead><tr><th>Channel</th><th class="num">Orders</th><th class="num">Value</th><th style="width:96px;">Share</th></tr></thead>
          <tbody>
            <?php foreach ($channels as $c): $pct = $chan_total > 0 ? round(($c['sum'] / $chan_total) * 100) : 0; ?>
              <tr>
                <td>
                  <div class="ro-name"><span class="d" style="background:<?= $c['bar'] === 'green' ? 'var(--mp-success,#2F9A67)' : 'var(--mp-primary,#5E4D66)'; ?>"></span><?= $c['name']; ?></div>
                  <div style="font-size:11px;color:var(--mp-muted,#78716C);margin-top:2px;"><?= $c['note']; ?></div>
                </td>
                <td class="num"><?= number_format($c['cnt']); ?></td>
                <td class="num"><span class="ro-pill rev"><?= $CI->currency($c['sum']); ?></span></td>
                <td><span class="ro-bar <?= $c['bar']; ?>"><span style="width:<?= $pct; ?>%;"></span></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div style="padding:10px 12px 4px;font-size:11.5px;color:var(--mp-muted,#78716C);line-height:1.5;">
          <i class="fa fa-info-circle"></i>
          Values above are the canonical financial ledger for <?= htmlspecialchars($range_label); ?>.
          Paid is collected value; outstanding is still owed, including Pay on Delivery.
        </div>
      </div>
    </div>
  </div>

  <!-- ============ The last few orders, wherever they came from ============ -->
  <div class="ro-panel">
    <div class="ro-panel-head">
      <div>
        <div class="t"><i class="fa fa-file-text-o"></i> Latest orders</div>
        <div class="s">Walk-in invoices and online orders in one timeline · <?= htmlspecialchars($range_label); ?></div>
      </div>
      <a class="ro-panel-link" href="<?= $ro_url('restaurantos/orders', ['range' => $range_key]); ?>">Open the orders desk &rarr;</a>
    </div>
    <div class="ro-panel-body tight">
      <?php if (empty($recent)): ?>
        <div class="ro-empty"><i class="fa fa-inbox"></i><div class="t">No orders in this period</div>
          <div class="s">Once the Walk In desk or online store takes an order, it lands here.</div></div>
      <?php else: ?>
        <div class="ro-orders">
          <?php foreach ($recent as $r): ?>
            <a class="ro-order" href="<?= base_url($r['link']); ?>">
              <span class="when"><?= htmlspecialchars($when_text($r['time'])); ?></span>
              <span class="ref"><?= htmlspecialchars($r['ref']); ?></span>
              <span class="who"><?= htmlspecialchars($r['customer']); ?> · <?= htmlspecialchars($r['fulfilment']); ?></span>
              <span class="tags">
                <span class="ro-chip <?= $r['channel'] === 'counter' ? 'neutral' : 'blue' ?>"><?= htmlspecialchars($r['source']); ?></span>
                <?php if ($r['prep'] !== '' && isset($prep_text[$r['prep']])): ?>
                  <span class="ro-chip <?= in_array($r['prep'], ['ready', 'ready_now', 'served'], true) ? 'up' : 'warn' ?>"><?= htmlspecialchars($prep_text[$r['prep']]); ?></span>
                <?php endif; ?>
                <span class="ro-chip <?= $r['settled'] ? 'up' : 'down' ?>"><?= $pay_text($r['pay'], $r['settled']); ?></span>
              </span>
              <span class="amt"><?= $CI->currency($r['total']); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ============ Workspace shortcuts ============ -->
  <?php if (!empty($nav_items)): ?>
    <div class="ro-panel">
      <div class="ro-panel-head">
        <div>
          <div class="t"><i class="fa fa-th-large"></i> Your workspace</div>
          <div class="s">Shaped by the <?= htmlspecialchars($preset['label'] ?? 'Quick Service'); ?> layout —
            <a href="<?= base_url('restaurantos/settings'); ?>" class="ro-panel-link">change it</a>
          </div>
        </div>
      </div>
      <div class="ro-panel-body">
        <div class="ro-scgrid">
          <?php
          $sc_blurb = [
              'pos'        => 'Take payment at the walk-in desk',
              'orders'     => 'Every channel in one list',
              'kitchen'    => 'Tickets the kitchen is working',
              'fulfilment' => 'Pickups and deliveries to route',
              'tables'     => 'Floor plan and reservations',
              'menu'       => 'Prices, categories, combos',
              'recipes'    => 'Ingredients and dish costing',
              'outlets'    => 'Stock and staff per outlet',
          ];
          $sc_icons = [
              'pos' => 'fa-shopping-cart', 'orders' => 'fa-list-alt', 'kitchen' => 'fa-fire',
              'fulfilment' => 'fa-truck', 'tables' => 'fa-table', 'menu' => 'fa-cutlery',
              'recipes' => 'fa-flask', 'outlets' => 'fa-map-marker',
          ];
          foreach ($nav_items as $key => $item): ?>
            <a class="ro-sc" href="<?= $item['url']; ?>">
              <span class="ic"><i class="fa <?= $sc_icons[$key] ?? 'fa-arrow-right'; ?>"></i></span>
              <span>
                <b><?= htmlspecialchars($item['label']); ?></b>
                <span><?= htmlspecialchars($sc_blurb[$key] ?? ''); ?></span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

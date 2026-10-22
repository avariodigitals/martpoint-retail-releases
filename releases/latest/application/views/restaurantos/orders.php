<?php
// One list, two ledgers: counter invoices (db_sales) and online/QR orders
// (db_online_orders) arrive here already merged and time-sorted by the
// controller. Read-only — status changes happen on the Kitchen Display and
// the Online Store order desk; each row links to the screen that owns it.
$CI =& get_instance();

$q_url = function ($overrides = [], $path = 'restaurantos/orders') {
    $q = array_merge($_GET, $overrides);
    foreach ($q as $k => $v) {
        if ($v === '' || $v === null) unset($q[$k]);
    }
    return base_url($path) . ($q ? '?' . http_build_query($q) : '');
};

// Payment chip: settled / part-paid / unpaid, in the words staff use.
$pay_chip = function ($r) {
    $key = strtolower(trim((string) $r['pay']));
    $map = [
        'paid' => ['Settled', 'up'], 'served' => ['Settled', 'up'],
        'partial' => ['Part-paid', 'warn'], 'partially_paid' => ['Part-paid', 'warn'],
        'unpaid' => ['Unpaid', 'down'], 'failed' => ['Payment failed', 'down'],
        'refunded' => ['Refunded', 'neutral'],
    ];
    if (isset($map[$key])) {
        $label = $map[$key][0]; $tone = $map[$key][1];
    } else {
        $label = $r['settled'] ? 'Settled' : 'Unpaid';
        $tone  = $r['settled'] ? 'up' : 'down';
    }
    return '<span class="ro-chip ' . $tone . '">' . htmlspecialchars($label) . '</span>';
};

// Kitchen / order progress chip, only when there is a real state to show.
$prep_chip = function ($r) {
    $map = ['new' => ['Queued', 'neutral'], 'preparing' => ['Cooking', 'warn'],
            'ready' => ['Ready', 'up'], 'served' => ['Handed over', 'up'],
            'pending' => ['New', 'neutral'], 'processing' => ['Being made', 'warn'],
            'completed' => ['Done', 'up'], 'cancelled' => ['Cancelled', 'down']];
    $key = strtolower(trim((string) $r['prep']));
    if (!isset($map[$key])) return '';
    return '<span class="ro-chip ' . $map[$key][1] . '">' . $map[$key][0] . '</span>';
};

$when_full = function ($datetime) {
    $ts = strtotime($datetime);
    return $ts ? date('d M Y · H:i', $ts) : htmlspecialchars((string) $datetime);
};

$active_tabs = [
    'source' => $source,
    'fulfil' => $fulfil,
    'pay'    => $pay,
];

// Outlet names only exist where the store actually runs more than one till;
// a single-outlet store has nothing to distinguish, so the column is dropped.
$multi_outlet  = !empty($branch_opts);
$outlet_names  = [];
foreach ((array) $branch_opts as $o) {
    $outlet_names[(int) $o['id']] = $o['name'];
}
?>
<?= $this->load->view('restaurantos/_assets', [], TRUE); ?>

<div class="ro-wrap ro-stack">

  <div class="ro-head">
    <div>
      <h2>Orders</h2>
      <div class="ro-head-sub">
        Counter, online and table-QR orders in one timeline ·
        <b><?= htmlspecialchars($range_label); ?></b>
      </div>
    </div>
    <div class="ro-head-tools">
      <?php if (!empty($branch_opts)): ?>
        <div class="ro-seg">
          <span class="ro-seg-label">Outlet</span>
          <a class="<?= $branch === '' ? 'active' : ''; ?>" href="<?= $q_url(['branch_id' => '']); ?>">All</a>
          <?php foreach ($branch_opts as $o): ?>
            <a class="<?= (string) $branch === (string) $o['id'] ? 'active' : ''; ?>"
               href="<?= $q_url(['branch_id' => $o['id']]); ?>"><?= htmlspecialchars($o['name']); ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="ro-seg">
        <span class="ro-seg-label">Period</span>
        <?php foreach ($ranges as $key => $label): ?>
          <a class="<?= $range_key === $key ? 'active' : ''; ?>" href="<?= $q_url(['range' => $key]); ?>"><?= htmlspecialchars($label); ?></a>
        <?php endforeach; ?>
      </div>
      <a href="<?= base_url('restaurantos'); ?>" class="ro-act slate" style="padding:8px 14px;">
        <span class="ic"><i class="fa fa-dashboard"></i></span><span class="tx"><b>Overview</b></span>
      </a>
    </div>
  </div>

  <!-- Summary of what is on screen right now -->
  <div class="ro-summary">
    <div class="ro-sum">
      <div class="l">Orders shown</div>
      <div class="v"><?= number_format($summary['cnt']); ?></div>
    </div>
    <div class="ro-sum">
      <div class="l">Counter</div>
      <div class="v"><?= number_format($summary['counter']); ?></div>
    </div>
    <div class="ro-sum">
      <div class="l">Online &amp; QR</div>
      <div class="v"><?= number_format($summary['online']); ?></div>
    </div>
    <div class="ro-sum">
      <div class="l">Value shown</div>
      <div class="v"><?= $CI->currency($summary['total']); ?></div>
    </div>
    <div class="ro-sum">
      <div class="l">Still to collect</div>
      <div class="v" style="color:<?= $summary['open'] > 0 ? 'var(--mp-danger,#DC2626)' : 'inherit'; ?>;"><?= $CI->currency($summary['open']); ?></div>
    </div>
  </div>

  <!-- Filters: chips for the narrowings staff actually use, plus a search -->
  <div class="ro-panel">
    <div class="ro-panel-body">
      <div class="ro-filters" style="margin-bottom:10px;">
        <span class="ro-flabel">Channel</span>
        <a class="ro-fchip <?= $source === '' ? 'active' : ''; ?>" href="<?= $q_url(['source' => '']); ?>">All channels</a>
        <a class="ro-fchip <?= $source === 'counter' ? 'active' : ''; ?>" href="<?= $q_url(['source' => 'counter']); ?>">Counter</a>
        <a class="ro-fchip <?= $source === 'online' ? 'active' : ''; ?>" href="<?= $q_url(['source' => 'online']); ?>">Online &amp; QR</a>

        <span class="ro-flabel" style="margin-left:10px;">Payment</span>
        <a class="ro-fchip <?= $pay === '' ? 'active' : ''; ?>" href="<?= $q_url(['pay' => '']); ?>">Any</a>
        <a class="ro-fchip <?= $pay === 'settled' ? 'active' : ''; ?>" href="<?= $q_url(['pay' => 'settled']); ?>">Settled</a>
        <a class="ro-fchip <?= $pay === 'open' ? 'active' : ''; ?>" href="<?= $q_url(['pay' => 'open']); ?>">Not settled</a>
      </div>

      <?php if (!empty($fulfil_options)): ?>
        <div class="ro-filters" style="margin-bottom:12px;">
          <span class="ro-flabel">Service</span>
          <a class="ro-fchip <?= $fulfil === '' || $fulfil === 'all' ? 'active' : ''; ?>" href="<?= $q_url(['fulfilment' => '']); ?>">All</a>
          <?php foreach ($fulfil_options as $label => $cnt): ?>
            <a class="ro-fchip <?= $fulfil === $label ? 'active' : ''; ?>" href="<?= $q_url(['fulfilment' => $label]); ?>">
              <?= htmlspecialchars($label); ?> <span style="opacity:.65;"><?= (int) $cnt; ?></span></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="get" action="<?= base_url('restaurantos/orders'); ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <input type="hidden" name="range" value="<?= htmlspecialchars($range_key); ?>">
        <?php if ($source !== ''): ?><input type="hidden" name="source" value="<?= htmlspecialchars($source); ?>"><?php endif; ?>
        <?php if ($fulfil !== '' && $fulfil !== 'all'): ?><input type="hidden" name="fulfilment" value="<?= htmlspecialchars($fulfil); ?>"><?php endif; ?>
        <?php if ($pay !== ''): ?><input type="hidden" name="pay" value="<?= htmlspecialchars($pay); ?>"><?php endif; ?>
        <?php if ($branch !== ''): ?><input type="hidden" name="branch_id" value="<?= htmlspecialchars($branch); ?>"><?php endif; ?>
        <span class="ro-search">
          <i class="fa fa-search"></i>
          <input type="text" name="q" value="<?= htmlspecialchars($q); ?>" placeholder="Search a reference or a name">
        </span>
        <button type="submit" class="ro-btn"><i class="fa fa-search"></i> Search</button>
        <?php if ($filters_on): ?>
          <a href="<?= base_url('restaurantos/orders?range=' . urlencode($range_key)); ?>"
             style="font-size:12.5px;font-weight:700;color:var(--mp-muted,#78716C);text-decoration:none;">Clear filters</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <?php if ($branch !== '' && !empty($branch_opts)): ?>
    <div class="ro-banner info">
      <span class="bi"><i class="fa fa-info-circle"></i></span>
      <span class="bb">An outlet is focused — counter <b>and</b> online orders both follow it.
        Online orders placed before locations started being recorded carry no outlet, so they only appear under <b>All</b>.</span>
    </div>
  <?php endif; ?>

  <div class="ro-panel">
    <div class="ro-panel-head">
      <div>
        <div class="t"><i class="fa fa-list-alt"></i> Order timeline</div>
        <div class="s">From <?= htmlspecialchars($from); ?><?= $to !== $from ? ' to ' . htmlspecialchars($to) : ''; ?> ·
          <?= number_format($shown); ?> order<?= $shown === 1 ? '' : 's' ?>
          <?= $filters_on ? 'after filters' : 'in this period' ?></div>
      </div>
      <a class="ro-panel-link" href="<?= base_url('pos'); ?>">New counter order &rarr;</a>
    </div>
    <div class="ro-panel-body tight">
      <?php if (empty($rows)): ?>
        <div class="ro-empty">
          <i class="fa fa-inbox"></i>
          <div class="t"><?= $filters_on ? 'Nothing matches those filters' : 'No orders in this period yet' ?></div>
          <div class="s">
            <?= $filters_on ? 'Try widening the period or clearing the filters above.' : 'Once the till or the online store takes an order, it lands here.' ?>
          </div>
          <?php if (!$filters_on): ?>
            <div style="margin-top:14px;">
              <a href="<?= base_url('pos'); ?>" class="ro-btn"><i class="fa fa-shopping-cart"></i> Start a Walk-in Order</a>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <table class="ro-table">
          <thead>
            <tr>
              <th>When</th>
              <th>Reference</th>
              <th>Channel</th>
              <?php if ($multi_outlet): ?>
                <th>Outlet</th>
              <?php endif; ?>
              <th>Service</th>
              <th>Customer</th>
              <th>Payment</th>
              <th>Kitchen</th>
              <th class="num">Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td style="white-space:nowrap;color:var(--mp-muted,#78716C);font-size:12px;"><?= $when_full($r['time']); ?></td>
                <td><span class="ro-ref" style="font-family:ui-monospace,Menlo,monospace;font-size:12px;font-weight:700;color:#44403C;"><?= htmlspecialchars($r['ref']); ?></span></td>
                <td><span class="ro-chip <?= $r['channel'] === 'counter' ? 'neutral' : 'blue' ?>"><?= htmlspecialchars($r['source']); ?></span></td>
                <?php if ($multi_outlet): ?>
                  <?php $oname = $outlet_names[(int) ($r['outlet'] ?? 0)] ?? ''; ?>
                  <td style="white-space:nowrap;font-size:12px;<?= $oname === '' ? 'color:var(--mp-muted,#A8A29E);' : 'color:#44403C;font-weight:600;' ?>">
                    <?= $oname === '' ? 'No outlet' : htmlspecialchars($oname); ?>
                  </td>
                <?php endif; ?>
                <td><?= htmlspecialchars($r['fulfilment']); ?></td>
                <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($r['customer']); ?></td>
                <td><?= $pay_chip($r); ?></td>
                <td><?= $prep_chip($r); ?></td>
                <td class="num"><strong><?= $CI->currency($r['total']); ?></strong></td>
                <td class="num" style="white-space:nowrap;">
                  <a class="ro-panel-link" href="<?= base_url($r['link']); ?>">Open &rarr;</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if ($shown > count($rows)): ?>
          <div style="padding:10px 12px;font-size:12px;color:var(--mp-muted,#78716C);">
            Showing the most recent <?= count($rows); ?> of <?= number_format($shown); ?> matching orders — narrow the period to reach earlier ones.
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

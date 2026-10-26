<?php
/**
 * Kitchen Display (KDS) — RestaurantOS
 *
 * Board-first screen: three service lanes, tickets ordered the way a kitchen
 * actually reads them (newest arrivals first, oldest work first).
 *
 * Behaviour preserved from the previous screen:
 *   - 15s count polling via operations/kitchen?ajax=1
 *   - status advance via operations/kitchen_update_status
 *   - new-ticket chime, fullscreen mode, recently-served strip
 * The ids the polling script writes to (count_new / count_preparing /
 * count_ready / last_refresh) are kept stable.
 */
/* ---- Lane buckets ------------------------------------------------------
   Ordering is the point: a cook reads NEW newest-first (what just landed)
   and PREPARING/READY oldest-first (what is going cold). */
$lanes = ['new' => [], 'preparing' => [], 'ready' => []];
foreach (($orders ?? []) as $o) {
    if (isset($lanes[$o->kds_status])) {
        $lanes[$o->kds_status][] = $o;
    }
}
usort($lanes['new'], function ($a, $b) {
    // Elapsed time grows as a ticket gets older. New tickets therefore sort
    // by their creation timestamp, newest first.
    return strtotime($b->kitchen_created) <=> strtotime($a->kitchen_created);
});
usort($lanes['preparing'], function ($a, $b) {
    // Work already on the line is read oldest first so the coldest ticket is
    // always at the top of the lane.
    return strtotime($a->kitchen_created) <=> strtotime($b->kitchen_created);
});
usort($lanes['ready'], function ($a, $b) {
    // Ready tickets are likewise served oldest first.
    return strtotime($a->kitchen_created) <=> strtotime($b->kitchen_created);
});

$lane_meta = [
    'new'       => ['label' => 'New',       'sub' => 'Waiting to start',   'action' => 'Start preparing', 'next' => 'preparing'],
    'preparing' => ['label' => 'Preparing', 'sub' => 'On the line',        'action' => 'Mark ready',      'next' => 'ready'],
    'ready'     => ['label' => 'Ready',     'sub' => 'Awaiting pickup',    'action' => 'Served / collected', 'next' => 'served'],
];

$total_live = count($lanes['new']) + count($lanes['preparing']) + count($lanes['ready']);
$branch = (string) ($branch ?? '');
$branch_opts = (array) ($branch_opts ?? []);
$branch_names = (array) ($branch_names ?? []);
$branch_all_label = !empty($branch_can_view_all) ? 'All branches' : 'All assigned branches';
$branch_name = $branch !== '' ? ($branch_names[(int) $branch] ?? '') : '';
$kitchen_branch_url = function ($id) {
    $url = base_url('operations/kitchen');
    return $id === '' ? $url : $url . '?branch_id=' . urlencode((string) $id);
};

/**
 * One ticket. Kept as a closure so the three lanes cannot drift apart — the
 * previous version copy-pasted the card three times and the timer logic had
 * already diverged from what each column claimed to do.
 */
$render_ticket = function ($o, $lane) use ($lane_meta, $branch_names) {
    $elapsed = (int) $o->elapsed_seconds;
    /* Stale = opened before today. These are the tickets nobody closed; they
       are still real work, so they stay on the board but are labelled rather
       than silently rendered as if freshly fired. */
    $is_stale = date('Y-m-d', strtotime($o->kitchen_created)) !== date('Y-m-d');
    ?>
    <article class="kds-ticket <?= $is_stale ? 'is-late' : '' ?>" data-kid="<?= (int) $o->kitchen_order_id; ?>" data-elapsed="<?= $elapsed; ?>">
      <div class="kds-ticket-top">
        <div style="min-width:0">
          <div class="kds-ticket-ref">#<?= htmlspecialchars($o->sales_code ?: $o->kitchen_order_id); ?></div>
          <div class="kds-ticket-meta">
            <span class="rs-pill <?= !empty($o->online_order_id) ? 'peach' : 'outline'; ?>">
              <?php if (!empty($o->online_order_id)): ?>
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                Online order
              <?php else: ?>
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 10h16M6 10V7h12v3M5 10v8h14v-8M9 14h6"/></svg>
                Counter
              <?php endif; ?>
            </span>
            <span class="rs-pill outline">
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?>
            </span>
            <?php if (!empty($o->table_name)): ?>
            <span class="rs-pill sage">
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 10h18"/><path d="M5 10V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v4"/><path d="M5 10v8h14v-8"/></svg>
              <?= htmlspecialchars($o->table_name); ?>
            </span>
            <?php endif; ?>
            <?php if ($is_stale): ?>
            <span class="rs-pill red">Stale · <?= date('j M', strtotime($o->kitchen_created)); ?></span>
            <?php endif; ?>
            <span class="rs-pill <?= empty($o->warehouse_id) ? 'red' : 'outline'; ?>">
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V5l7-3 7 3v16"/><path d="M9 9h2M13 9h2M9 13h2M13 13h2"/></svg>
              <?= htmlspecialchars($branch_names[(int) ($o->warehouse_id ?? 0)] ?? 'Unassigned outlet'); ?>
            </span>
          </div>
        </div>
        <div class="kds-timer" data-initial="<?= $elapsed; ?>">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
          <span class="kds-timer-text">--:--</span>
        </div>
      </div>

      <div class="kds-ticket-items">
        <?php if (!empty($o->items)): ?>
          <?php foreach ($o->items as $itm): ?>
          <div class="kds-item">
            <span class="kds-item-qty"><?= (int) $itm->sales_qty; ?>×</span>
            <span class="kds-item-name">
              <?= htmlspecialchars($itm->item_name); ?>
              <?php if (!empty($itm->item_note)): ?>
                <span class="kds-item-note"><?= htmlspecialchars($itm->item_note); ?></span>
              <?php endif; ?>
            </span>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="kds-item"><span class="kds-item-name" style="color:var(--rs-muted)">No line items on this ticket</span></div>
        <?php endif; ?>
      </div>

      <div class="kds-ticket-actions">
        <button type="button" class="rs-btn rs-btn-sm"
                onclick="updateKdsStatus(<?= (int) $o->kitchen_order_id; ?>, '<?= $lane_meta[$lane]['next']; ?>', this)">
          <?= htmlspecialchars($lane_meta[$lane]['action']); ?>
        </button>
      </div>
    </article>
    <?php
};
?>

<?php $this->load->view('comman/code_flashdata.php'); ?>

<div class="rs-page">

  <div class="rs-head">
    <div class="rs-head-left">
      <span class="rs-eyebrow">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 11h18"/><path d="M5 11V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v4"/><path d="M6 11v7a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-7"/></svg>
        Restaurant service
      </span>
      <h1>Kitchen Display</h1>
      <p class="rs-sub">
        <?php if ($total_live > 0): ?>
          <strong style="color:var(--rs-ink)"><?= $total_live; ?></strong> live ticket<?= $total_live === 1 ? '' : 's'; ?> on the board
        <?php else: ?>
          The line is clear — no open tickets.
        <?php endif; ?>
        <?php if ($branch_name !== ''): ?><span style="color:var(--rs-faint)">·</span><span><?= htmlspecialchars($branch_name); ?></span><?php endif; ?>
        <span style="color:var(--rs-faint)">·</span>
        <span id="last_refresh">Auto-refresh every 15s</span>
      </p>
    </div>

    <div class="rs-head-actions">
      <?php if (!empty($branch_opts)): ?>
        <div class="ro-seg" aria-label="Kitchen outlet">
          <span class="ro-seg-label">Outlet</span>
          <a class="<?= $branch === '' ? 'active' : ''; ?>" href="<?= $kitchen_branch_url(''); ?>"><?= htmlspecialchars($branch_all_label); ?></a>
          <?php foreach ($branch_opts as $o): ?>
            <a class="<?= (string) $branch === (string) $o['id'] ? 'active' : ''; ?>" href="<?= $kitchen_branch_url($o['id']); ?>"><?= htmlspecialchars($o['name']); ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <a href="<?= base_url('operations/menu_items'); ?>" class="rs-btn">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 4h7l9 9-7 7-9-9z"/><circle cx="8.5" cy="8.5" r="1"/></svg>
        Menu &amp; combos
      </a>
      <button type="button" id="kds-fullscreen-btn" class="rs-btn" onclick="toggleKdsFullscreen()">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
        <span id="kds-fs-label">Fullscreen</span>
      </button>
    </div>
  </div>

  <div class="kds-board">
    <?php foreach ($lanes as $lane => $tickets): $meta = $lane_meta[$lane]; ?>
    <section class="kds-lane <?= $lane; ?>">
      <header class="kds-lane-head">
        <div class="lane-title">
          <span class="lane-dot"></span> <?= $meta['label']; ?>
        </div>
        <span class="rs-chip-count" id="count_<?= $lane; ?>"><?= count($tickets); ?></span>
      </header>

      <div class="kds-lane-body" id="col_<?= $lane; ?>">
        <?php if (empty($tickets)): ?>
          <div class="rs-empty">
            <div class="rs-empty-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 11h18"/><path d="M5 11V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v4"/><path d="M6 11v7a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-7"/></svg>
            </div>
            <div class="rs-empty-title"><?= $meta['sub']; ?></div>
            <div class="rs-empty-text">Tickets appear here the moment they are fired.</div>
          </div>
        <?php else: ?>
          <?php foreach ($tickets as $o) { $render_ticket($o, $lane); } ?>
        <?php endif; ?>
      </div>
    </section>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($served)): ?>
  <div class="rs-card">
    <div class="rs-card-head">
      <h3 class="rs-card-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 3v6h6"/><path d="M3.5 9a9 9 0 1 0 2.1-4.3L3 9"/><polyline points="12 8 12 12 15 14"/></svg>
        Recently served
      </h3>
      <span class="rs-pill outline"><?= count($served); ?> completed</span>
    </div>
    <div class="rs-card-body">
      <div class="kds-served-grid">
        <?php foreach (array_slice($served, 0, 12) as $sv): ?>
        <div class="kds-served-card">
          <div class="code">#<?= htmlspecialchars($sv->sales_code ?: $sv->kitchen_order_id); ?></div>
          <div class="cust"><?= htmlspecialchars($sv->customer_name ?: 'Walk-in'); ?></div>
          <div class="cust" style="color:var(--rs-muted);font-size:11px;"><?= htmlspecialchars($branch_names[(int) ($sv->warehouse_id ?? 0)] ?? 'Unassigned outlet'); ?></div>
          <div class="time"><?= date('H:i', strtotime($sv->updated_at)); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
var base_url = '<?= base_url(); ?>';
var kdsBranch = <?= json_encode($branch); ?>;
var previousNewCount   = <?= count($lanes['new']); ?>;
var previousPrepCount  = <?= count($lanes['preparing']); ?>;
var previousReadyCount = <?= count($lanes['ready']); ?>;

/* Service-time thresholds. Previously anything under 10 minutes was painted
   danger-red and anything over 20 minutes silently lost its warning, so a
   brand-new ticket shouted while a cold one went quiet. Now: neutral until it
   is genuinely worth looking at. */
var WARM_AT = 8 * 60;    // 8 min  → amber
var LATE_AT = 15 * 60;   // 15 min → red

function formatElapsed(seconds) {
    seconds = Math.max(0, parseInt(seconds, 10) || 0);
    var d = Math.floor(seconds / 86400);
    var h = Math.floor((seconds % 86400) / 3600);
    var m = Math.floor((seconds % 3600) / 60);
    var s = seconds % 60;
    if (d > 0) return d + 'd ' + h + 'h';
    if (h > 0) return h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    return m + ':' + (s < 10 ? '0' : '') + s;
}

function paintTimer(el, seconds) {
    var text = el.querySelector('.kds-timer-text');
    if (text) text.textContent = formatElapsed(seconds);
    el.classList.toggle('t-warm', seconds >= WARM_AT && seconds < LATE_AT);
    el.classList.toggle('t-late', seconds >= LATE_AT);
}

function tickTimers() {
    document.querySelectorAll('.kds-timer').forEach(function (el) {
        var elapsed = parseInt(el.getAttribute('data-elapsed'), 10);
        if (isNaN(elapsed)) elapsed = parseInt(el.getAttribute('data-initial'), 10) || 0;
        elapsed++;
        el.setAttribute('data-elapsed', elapsed);
        paintTimer(el, elapsed);
    });
}

document.querySelectorAll('.kds-timer').forEach(function (el) {
    paintTimer(el, parseInt(el.getAttribute('data-initial'), 10) || 0);
});
setInterval(tickTimers, 1000);

function updateKdsStatus(kitchenOrderId, newStatus, btn) {
    if (btn) {
        btn.disabled = true;
        btn.dataset.label = btn.innerHTML;
        btn.innerHTML = 'Updating…';
    }
    $.ajax({
        url: base_url + 'operations/kitchen_update_status',
        type: 'POST',
        data: { kitchen_order_id: kitchenOrderId, status: newStatus, branch_id: kdsBranch },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                location.reload();
            } else {
                toastr.error((res && res.message) || 'Update failed. Please try again.');
                if (btn) { btn.disabled = false; btn.innerHTML = btn.dataset.label; }
            }
        },
        error: function () {
            toastr.error('Network error. Please try again.');
            if (btn) { btn.disabled = false; btn.innerHTML = btn.dataset.label; }
        }
    });
}

function playNewOrderSound() {
    try {
        var AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        var ctx = new AudioContext();
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.5);
    } catch (e) {}
}

function kdsAutoRefresh() {
    $.ajax({
        url: base_url + 'operations/kitchen?ajax=1' + (kdsBranch ? '&branch_id=' + encodeURIComponent(kdsBranch) : ''),
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.status_counts) {
                var currentNew = parseInt(data.status_counts.new) || 0;
                var currentPrep = parseInt(data.status_counts.preparing) || 0;
                var currentReady = parseInt(data.status_counts.ready) || 0;
                var totalCurrent = currentNew + currentPrep + currentReady;
                var totalPrevious = previousNewCount + previousPrepCount + previousReadyCount;

                if (totalCurrent !== totalPrevious) {
                    if (currentNew > previousNewCount) playNewOrderSound();
                    location.reload();
                    return;
                }
                previousNewCount = currentNew;
                previousPrepCount = currentPrep;
                previousReadyCount = currentReady;
                $('#count_new').text(currentNew);
                $('#count_preparing').text(currentPrep);
                $('#count_ready').text(currentReady);
            }
            $('#last_refresh').text('Refreshed ' + new Date().toLocaleTimeString());
        }
    });
}
setInterval(kdsAutoRefresh, 15000);

function toggleKdsFullscreen() {
    document.body.classList.toggle('kds-fullscreen');
    var fs = document.body.classList.contains('kds-fullscreen');
    document.getElementById('kds-fs-label').textContent = fs ? 'Exit fullscreen' : 'Fullscreen';
    document.getElementById('kds-fullscreen-btn').classList.toggle('rs-btn-primary', fs);
    if (fs) {
        if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen().catch(function () {});
    } else {
        if (document.exitFullscreen && document.fullscreenElement) document.exitFullscreen().catch(function () {});
    }
}
document.addEventListener('fullscreenchange', function () {
    if (!document.fullscreenElement && document.body.classList.contains('kds-fullscreen')) {
        document.body.classList.remove('kds-fullscreen');
        document.getElementById('kds-fs-label').textContent = 'Fullscreen';
        document.getElementById('kds-fullscreen-btn').classList.remove('rs-btn-primary');
    }
});
</script>

<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance(); ?>

<style>
.tb-wrap { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 18px; align-items: start; }
@media (max-width: 1100px) { .tb-wrap { grid-template-columns: minmax(0, 1fr); } }
.tb-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px; }
.tb-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 14px;
  box-shadow: var(--mp-shadow-sm); cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease; }
.tb-card:hover { box-shadow: var(--mp-shadow); }
.tb-card.sel { border-color: var(--mp-primary); }
.tb-card.owing { border-left: 4px solid var(--mp-warning); }
.tb-card.clear { border-left: 4px solid var(--mp-success); }
.tb-code { font-size: 15px; font-weight: 700; color: var(--mp-text); }
.tb-meta { font-size: 12px; color: var(--mp-muted); margin: 3px 0 10px; }
.tb-money { font-size: 18px; font-weight: 700; color: var(--mp-text); }
.tb-owe { font-size: 12px; font-weight: 600; color: var(--mp-danger); }
.tb-owe.zero { color: var(--mp-success); }
.tb-row { display: flex; align-items: center; gap: 8px; justify-content: space-between; margin-top: 10px; }
.tb-btn { border: 1px solid var(--mp-border); background: var(--mp-surface); color: var(--mp-ink);
  border-radius: 8px; padding: 6px 10px; font-size: 12px; font-weight: 600; cursor: pointer; }
.tb-btn:hover { background: var(--mp-bg); }
.tb-btn.primary { border-color: var(--mp-primary); color: var(--mp-primary); }
.tb-btn.danger:hover { border-color: var(--mp-danger); color: var(--mp-danger); }
.tb-panel { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 16px; }
.tb-panel h3 { margin: 0 0 4px; font-size: 15px; }
.tb-empty { padding: 28px 12px; text-align: center; color: var(--mp-muted); font-size: 13px; }
.tb-round { display: flex; justify-content: space-between; gap: 10px; padding: 9px 0; border-bottom: 1px dashed var(--mp-border); font-size: 12.5px; }
.tb-round:last-child { border-bottom: 0; }
.tb-round .num { font-weight: 600; white-space: nowrap; }
.tb-round .unpaid { color: var(--mp-danger); }
.tb-round .paid { color: var(--mp-success); }
/* A voided round stays visible: the floor needs to see it happened. */
.tb-round.voided { opacity: .55; }
.tb-round.voided a { text-decoration: line-through; }
.tb-round a { color: inherit; text-decoration: underline; }
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Rounds a guest is still on, grouped in one place. Every round is still its own invoice.</div>
  </div>
</div>

<?php if (empty($available)): ?>
<div class="mp-card-form">
  <div class="mp-card-body">
    <p><strong>Running tabs are not installed on this store yet.</strong></p>
    <p class="mp-page-sub">Open System Update and apply the 4.0.9.227 update, then reload this page.</p>
  </div>
</div>
<?php else: ?>

<?php
$sum_total = 0.0; $sum_owe = 0.0; $sum_rounds = 0;
foreach ($tabs as $t) { $sum_total += (float)$t->total; $sum_owe += (float)$t->outstanding; $sum_rounds += (int)$t->rounds; }
?>
<div class="mp-kpi-grid">
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-glass"></i></div>
    <div class="mp-kpi-label">Open tabs</div>
    <div class="mp-kpi-value"><?= count($tabs); ?></div>
  </div>
  <div class="mp-kpi-card">
    <div class="mp-kpi-icon"><i class="fa fa-list-ul"></i></div>
    <div class="mp-kpi-label">Rounds served</div>
    <div class="mp-kpi-value"><?= (int)$sum_rounds; ?></div>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-file-o"></i></div>
    <div class="mp-kpi-label">On tabs</div>
    <div class="mp-kpi-value"><?= $CI->currency($sum_total); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Still owing</div>
    <div class="mp-kpi-value"><?= $CI->currency($sum_owe); ?></div>
  </div>
</div>

<div class="mp-card-form" style="margin-bottom:18px;">
  <div class="mp-card-head"><h3><i class="fa fa-plus-circle"></i> Seat a table or open a bar tab</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid" style="gap:12px;">
      <div class="mp-form-group">
        <label>Table</label>
        <select id="tabTable" class="mp-form-control">
          <option value="0">No table (bar tab)</option>
          <?php foreach ($tables as $t): ?>
          <option value="<?= (int)$t['id']; ?>">
            <?= htmlspecialchars($t['name']); ?><?= $t['zone'] ? ' — ' . htmlspecialchars($t['zone']) : ''; ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label>Guest name</label>
        <input type="text" id="tabGuest" class="mp-form-control" placeholder="e.g. Booth 3, Tolu">
      </div>
      <div class="mp-form-group" style="max-width:120px;">
        <label>Covers</label>
        <input type="number" id="tabCovers" class="mp-form-control" min="0" max="50" value="0">
      </div>
      <div class="mp-form-group" style="display:flex;align-items:flex-end;">
        <button type="button" class="mp-btn-primary" id="tabOpenBtn" onclick="openTab()">
          <i class="fa fa-glass"></i> Open tab
        </button>
      </div>
    </div>
    <p class="mp-page-sub" style="margin:10px 0 0;">A table can only hold one open tab, so a second press on the same table keeps adding rounds to the bill that is already running.</p>
  </div>
</div>

<div class="tb-wrap">
  <div>
    <?php if (empty($tabs)): ?>
    <div class="tb-panel"><div class="tb-empty">No open tabs. Seat a table above, or ring a round from the POS onto a tab.</div></div>
    <?php else: ?>
    <div class="tb-grid">
      <?php foreach ($tabs as $t): ?>
      <?php
        $t_table = null;
        foreach ($tables as $x) { if ((int)$x['id'] === (int)$t->table_id) { $t_table = (string)$x['name']; break; } }
        $t_owe = (float)$t->outstanding;
      ?>
      <div class="tb-card <?= $t_owe > 0.005 ? 'owing' : 'clear'; ?>" data-tab="<?= (int)$t->id; ?>" onclick="loadRounds(<?= (int)$t->id; ?>)">
        <div class="tb-code"><?= htmlspecialchars($t->tab_code); ?></div>
        <div class="tb-meta">
          <?php if (!empty($t_table)): ?><i class="fa fa-table"></i> <?= htmlspecialchars($t_table); ?><?php else: ?><i class="fa fa-glass"></i> Bar<?php endif; ?>
          <?php if (!empty($t->guest_name)): ?> · <?= htmlspecialchars($t->guest_name); ?><?php endif; ?>
          <?php if ((int)$t->covers > 0): ?> · <?= (int)$t->covers; ?> covers<?php endif; ?>
        </div>
        <div class="tb-money"><?= $CI->currency((float)$t->total); ?> <span class="mp-page-sub">in <?= (int)$t->rounds; ?> round(s)</span></div>
        <div class="tb-owe <?= $t_owe <= 0.005 ? 'zero' : ''; ?>">
          <?= $t_owe <= 0.005 ? 'Paid — ready to close' : $CI->currency($t_owe) . ' still owing'; ?>
        </div>
        <div class="tb-row">
          <select class="mp-form-control" style="flex:1;max-width:150px;font-size:12px;padding:5px 8px;"
                  onchange="setWaiter(<?= (int)$t->id; ?>, this.value)">
            <option value="0">No waiter named</option>
            <?php foreach ($staff as $s): ?>
            <option value="<?= (int)$s['id']; ?>" <?= (int)$t->waiter_user_id === (int)$s['id'] ? 'selected' : ''; ?>>
              <?= htmlspecialchars($s['name']); ?>
            </option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="tb-btn primary" onclick="event.stopPropagation();settleTab(<?= (int)$t->id; ?>)">Settle</button>
          <button type="button" class="tb-btn danger" onclick="event.stopPropagation();cancelTab(<?= (int)$t->id; ?>)">Cancel</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="tb-panel" id="tabDetail">
    <h3>Rounds</h3>
    <div class="mp-page-sub">Pick a tab to see the invoices behind it.</div>
    <div id="tabRounds" class="tb-empty">Nothing loaded.</div>
  </div>
</div>

<script>
var tabBase = '<?= base_url('operations/'); ?>';

function tabPost(url, data, done) {
  $.post(url, data, function (res) {
    if (res && res.success) { done(res); }
    else { cqError((res && res.message) || 'Something went wrong. Nothing was changed.'); }
  }, 'json').fail(function (xhr) {
    var msg = 'Network error. Nothing was changed.';
    try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
    cqError(msg);
  });
}

function openTab() {
  var btn = document.getElementById('tabOpenBtn');
  btn.disabled = true;
  tabPost(tabBase + 'tab_open', {
    table_id: parseInt(document.getElementById('tabTable').value, 10) || 0,
    guest_name: document.getElementById('tabGuest').value,
    covers: parseInt(document.getElementById('tabCovers').value, 10) || 0,
    tab_key: 'tab-' + Date.now() + '-' + Math.random().toString(36).slice(2)
  }, function (res) {
    cqSuccess(res.message || 'Tab opened.');
    setTimeout(function () { location.reload(); }, 400);
  });
  setTimeout(function () { btn.disabled = false; }, 1500);
}

function settleTab(id) {
  cqConfirm('Close this tab? Every round on it must be paid first — nothing is charged here.').then(function (ok) {
    if (!ok) return;
    tabPost(tabBase + 'tab_settle', { tab_id: id }, function (res) {
      cqSuccess(res.message || 'Tab settled.');
      setTimeout(function () { location.reload(); }, 400);
    });
  });
}

function cancelTab(id) {
  cqPrompt({ title: 'Cancel this tab', message: 'Only a tab with no rounds can be cancelled. Why is it being cancelled?', placeholder: 'e.g. Opened the wrong table', required: true, danger: true, okText: 'Cancel tab' })
    .then(function (reason) {
      if (reason === null) return;
      tabPost(tabBase + 'tab_cancel', { tab_id: id, reason: reason }, function (res) {
        cqSuccess(res.message || 'Tab cancelled.');
        setTimeout(function () { location.reload(); }, 400);
      });
    });
}

function setWaiter(id, waiterId) {
  tabPost(tabBase + 'tab_waiter', { tab_id: id, waiter_id: parseInt(waiterId, 10) || 0 }, function (res) {
    cqSuccess(res.message || 'Waiter updated.');
  });
}

function loadRounds(id) {
  document.querySelectorAll('.tb-card').forEach(function (c) { c.classList.toggle('sel', parseInt(c.getAttribute('data-tab'), 10) === id); });
  var box = document.getElementById('tabRounds');
  box.className = '';
  box.innerHTML = '<div class="tb-empty">Loading…</div>';
  $.get(tabBase + 'tab_rounds', { tab_id: id }, function (res) {
    if (!res || !res.success) { box.innerHTML = '<div class="tb-empty">Could not load this tab.</div>'; return; }
    var t = res.totals;
    var html = '<div class="tb-round"><span>' + escTab(res.tab.tab_code) + ' · ' + Number(t.rounds) + ' round(s)</span>'
             + '<span class="num"> owing ' + escTab(t.outstanding_fmt) + '</span></div>';
    // Beside the bill, never inside it: the tip is the waiter's money.
    if (Number(t.tips) > 0) {
      html += '<div class="tb-round"><span>Left as tip for the waiter</span>'
            + '<span class="num">' + escTab(t.tips_fmt) + '</span></div>';
    }
    if (!res.rounds.length) html += '<div class="tb-empty">No rounds on this tab yet.</div>';
    res.rounds.forEach(function (r) {
      html += '<div class="tb-round' + (r.voided ? ' voided' : '') + '"><span>#' + Number(r.round_no) + ' · '
            + '<a href="' + escAttr(r.bill_url) + '">' + escTab(r.sales_code) + '</a>'
            + (r.created_time ? ' · ' + escTab(r.created_time) : '')
            + (r.voided ? ' · <em>voided</em>' : '') + '</span>'
            + '<span class="num ' + (r.voided ? '' : (r.unpaid ? 'unpaid' : 'paid')) + '">' + escTab(r.total_fmt) + '</span></div>';
    });
    box.innerHTML = html;
  }, 'json').fail(function () { box.innerHTML = '<div class="tb-empty">Network error.</div>'; });
}

function escTab(v) {
  var d = document.createElement('div');
  d.textContent = (v === null || v === undefined) ? '' : String(v);
  return d.innerHTML;
}

function escAttr(v) {
  return String(v === null || v === undefined ? '' : v).replace(/"/g, '&quot;').replace(/[<>]/g, '');
}
</script>
<?php endif; ?>

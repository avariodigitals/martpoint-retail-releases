<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance();

$money  = function ($v) use ($CI) { return $CI->currency((float) $v); };
$paid   = (float) ($bill->paid_amount ?? 0);
$owing  = max(0.0, (float) $bill->grand_total - $paid);
// These columns hold usernames; the floor reads names.
$who = function ($username) use ($names) {
    if ($username === '' || $username === null) return 'someone at the bar';
    return isset($names[$username]) ? $names[$username] : 'a member of staff';
};
$lines_total = 0.0;
foreach ($bill->lines as $l) { $lines_total += (float) $l->total_cost; }

// Floor staff are chosen from the store's own people, and shown by first name.
$staff_names = [];
foreach ((array) $staff as $s) { $staff_names[(int) $s['id']] = (string) $s['name']; }
$waiter_name = $staff_names[(int) ($bill->waiter_id ?? 0)] ?? null;
$tip_for_name = $staff_names[(int) ($bill->tip_for_id ?? 0)] ?? null;
$tip_amount  = (float) ($bill->tip_amount ?? 0);
$charge_pct  = (float) ($bill->service_charge_pct ?? 0);
$charge_amt  = (float) ($bill->service_charge_amt ?? 0);
?>

<style>
.lb-wrap { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 18px; align-items: start; }
@media (max-width: 1100px) { .lb-wrap { grid-template-columns: minmax(0, 1fr); } }
.lb-panel { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 16px; }
.lb-panel h3 { margin: 0 0 4px; font-size: 15px; }
.lb-empty { padding: 20px 12px; text-align: center; color: var(--mp-muted); font-size: 13px; }
.lb-row { display: flex; align-items: center; gap: 8px; justify-content: space-between; margin-top: 10px; }
.lb-btn { display: inline-block; border: 1px solid var(--mp-border); background: var(--mp-surface); color: var(--mp-ink);
  border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; }
.lb-btn:hover { background: var(--mp-bg); }
.lb-btn.primary { border-color: var(--mp-primary); color: var(--mp-primary); }
.lb-btn.danger:hover { border-color: var(--mp-danger); color: var(--mp-danger); }
.lb-lines td, .lb-lines th { font-size: 13px; }
.lb-lines tr.onhouse { background: rgba(255, 193, 7, .08); }
.lb-lines tr.voided { opacity: .5; }
.lb-lines tr.voided td:nth-child(2) { text-decoration: line-through; }
.lb-comp-why { font-size: 11.5px; color: #b8860b; margin-top: 2px; }
.lb-was { text-decoration: line-through; color: var(--mp-muted); margin-right: 6px; }
.lb-free { font-weight: 700; color: #b8860b; }
.lb-totals { margin-top: 12px; border-top: 1px solid var(--mp-border); padding-top: 10px; }
.lb-totals div { display: flex; justify-content: space-between; font-size: 13px; padding: 3px 0; }
.lb-totals div.grand { font-size: 15px; font-weight: 700; border-top: 1px dashed var(--mp-border); margin-top: 6px; padding-top: 8px; }
.lb-notice { background: var(--mp-bg); border: 1px solid var(--mp-border); border-left: 4px solid var(--mp-warning);
  border-radius: 8px; padding: 10px 12px; font-size: 12.5px; margin-bottom: 14px; }
.lb-notice.void { border-left-color: var(--mp-danger); }
.lb-panel.locked { opacity: .92; }
.lb-approval { border-top: 1px dashed var(--mp-border); margin-top: 10px; padding-top: 10px; }
</style>

<div class="mp-page-head">
  <div>
    <h2>Round <?= htmlspecialchars($bill->sales_code); ?>
      <?php if ($bill->is_voided): ?><span class="label label-danger">Voided</span><?php endif; ?>
    </h2>
    <div class="mp-page-sub">
      <?php if (!empty($bill->tab)): ?>
        <i class="fa fa-glass"></i> Tab <?= htmlspecialchars($bill->tab->tab_code); ?>
        <?php if (!empty($bill->tab->guest_name)): ?> · <?= htmlspecialchars($bill->tab->guest_name); ?><?php endif; ?>
      <?php else: ?>
        Paid on its own — not on a tab
      <?php endif; ?>
      · rung up by <?= htmlspecialchars($who($bill->created_by)); ?>
      · served by <b id="lbWaiterName"><?= htmlspecialchars($waiter_name ?: 'nobody named yet'); ?></b>
      · <?= htmlspecialchars((string) $bill->sales_date); ?>
    </div>
  </div>
  <div>
    <a class="lb-btn" href="<?= base_url('operations/tabs'); ?>"><i class="fa fa-arrow-left"></i> Back to the floor</a>
  </div>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card">
    <div class="mp-kpi-icon"><i class="fa fa-file-o"></i></div>
    <div class="mp-kpi-label">This round</div>
    <div class="mp-kpi-value"><?= $money($bill->grand_total); ?></div>
  </div>
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Paid</div>
    <div class="mp-kpi-value"><?= $money($paid); ?></div>
  </div>
  <div class="mp-kpi-card <?= $owing > 0.005 ? 'debt' : 'success'; ?>">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Still owing</div>
    <div class="mp-kpi-value"><?= $money($owing); ?></div>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-gift"></i></div>
    <div class="mp-kpi-label">On the house</div>
    <div class="mp-kpi-value"><?= $money($bill->given_away); ?></div>
  </div>
</div>

<?php if ($bill->is_voided): ?>
<div class="lb-notice void">
  <strong>This round was voided.</strong>
  Its money came off the bill and its stock went back on the shelf.
  <?= htmlspecialchars((string) ($bill->void_reason ?: 'No reason recorded')); ?> —
  <?= htmlspecialchars($who($bill->voided_by)); ?>, <?= htmlspecialchars((string) $bill->voided_at); ?>.
</div>
<?php endif; ?>

<div class="lb-wrap">
  <div class="lb-panel">
    <h3>What was ordered</h3>
    <div class="mp-page-sub">Tick a line to give it away. The stock still leaves the shelf and its cost still lands on cost of goods — only the charge comes off.</div>
    <table class="table table-condensed lb-lines">
      <thead>
        <tr>
          <?php if ($floor_comp_on && $bill->can_comp): ?><th style="width:34px;"></th><?php endif; ?>
          <th>Item</th>
          <th style="width:90px;" class="text-right">Qty</th>
          <th style="width:110px;" class="text-right">Each</th>
          <th style="width:130px;" class="text-right">Charged</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($bill->lines as $l): ?>
        <?php
          $is_comp  = (int) ($l->comp_bit ?? 0) === 1;
          $is_void  = (string) ($l->sales_status ?? '') === 'Voided';
          $compable = $floor_comp_on && $bill->can_comp && !$is_comp && !$is_void;
        ?>
        <tr class="<?= $is_comp ? 'onhouse' : ''; ?> <?= $is_void ? 'voided' : ''; ?>">
          <?php if ($floor_comp_on && $bill->can_comp): ?>
          <td>
            <?php if ($compable): ?>
              <input type="checkbox" class="lb-comp" value="<?= (int) $l->id; ?>">
            <?php endif; ?>
          </td>
          <?php endif; ?>
          <td>
            <?= htmlspecialchars((string) ($l->item_name ?: 'Item')); ?>
            <?php if (!empty($l->unit_name)): ?><span class="mp-page-sub"> · <?= htmlspecialchars((string) $l->unit_name); ?></span><?php endif; ?>
            <?php if ($is_comp): ?>
              <div class="lb-comp-why">
                <i class="fa fa-gift"></i> On the house — <?= htmlspecialchars((string) ($l->comp_reason ?: 'no reason recorded')); ?>
                · <?= htmlspecialchars($who($l->comp_by)); ?>
              </div>
            <?php endif; ?>
          </td>
          <td class="text-right"><?= htmlspecialchars((string) $l->sales_qty); ?></td>
          <td class="text-right"><?= $money($l->price_per_unit); ?></td>
          <td class="text-right">
            <?php if ($is_comp): ?>
              <span class="lb-was"><?= $money($l->comp_amount); ?></span> <span class="lb-free">free</span>
            <?php elseif ($is_void): ?>
              <span class="lb-was"><?= $money($l->total_cost); ?></span> <span class="lb-free">off the bill</span>
            <?php else: ?>
              <?= $money($l->total_cost); ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($bill->lines)): ?>
        <tr><td colspan="5"><div class="lb-empty">This bill has no lines.</div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <div class="lb-totals">
      <div><span>Lines charged</span><b><?= $money($lines_total); ?></b></div>
      <?php if ((float) ($bill->tot_discount_to_all_amt ?? 0) > 0.005): ?>
      <div><span>Bill discount</span><b>− <?= $money($bill->tot_discount_to_all_amt); ?></b></div>
      <?php endif; ?>
      <?php if ($bill->given_away > 0.005): ?>
      <div><span>Given away</span><b>− <?= $money($bill->given_away); ?></b></div>
      <?php endif; ?>
      <?php if ($charge_amt > 0.005): ?>
      <!-- Already inside what the guest pays: a service charge is house money. -->
      <div><span>Service charge<?= $charge_pct > 0 ? ' (' . rtrim(rtrim(number_format($charge_pct, 2, '.', ''), '0'), '.') . '%)' : ''; ?></span><b>+ <?= $money($charge_amt); ?></b></div>
      <?php endif; ?>
      <div class="grand"><span>Guest pays</span><b><?= $money($bill->grand_total); ?></b></div>
      <?php if ($tip_amount > 0.005): ?>
      <div><span>Tip for <?= htmlspecialchars($tip_for_name ?: 'the waiter'); ?></span>
        <b><?= $money($tip_amount); ?> <span class="mp-page-sub">beside the bill</span></b></div>
      <?php endif; ?>
    </div>
    <div class="mp-page-sub" style="margin-top:8px;">
      A discount and a compliment are different things: a discount means the guest paid less for the round, a compliment means the house paid for it.
    </div>
  </div>

  <div>
    <?php if ($floor_waiter_on || $floor_tip_on || $floor_charge_on): ?>
    <div class="lb-panel" style="margin-bottom:16px;">
      <h3><i class="fa fa-user-circle-o"></i> The waiter and the tip</h3>

      <?php if ($floor_waiter_on): ?>
      <div class="mp-form-group">
        <label>Served by</label>
        <select id="lbWaiter" class="mp-form-control">
          <option value="0">Nobody named</option>
          <?php foreach ((array) $staff as $s): ?>
          <option value="<?= (int) $s['id']; ?>" <?= (int) ($bill->waiter_id ?? 0) === (int) $s['id'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($s['name']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="lb-row">
        <span class="mp-page-sub">Every round names the person behind it.</span>
        <button type="button" class="lb-btn" id="waiterBtn">Save waiter</button>
      </div>
      <?php endif; ?>

      <?php if ($floor_tip_on): ?>
      <div class="mp-form-group" style="margin-top:12px;">
        <label>Tip the guest left</label>
        <input type="text" id="lbTipAmount" class="mp-form-control" inputmode="decimal"
          placeholder="e.g. 500"
          value="<?= $tip_amount > 0.005 ? htmlspecialchars(rtrim(rtrim(number_format($tip_amount, 2, '.', ''), '0'), '.')) : ''; ?>">
      </div>
      <div class="mp-form-group">
        <label>Recorded for</label>
        <select id="lbTipFor" class="mp-form-control">
          <?php
            $tip_default = (int) ($bill->tip_for_id ?? 0) ?: (int) ($bill->waiter_id ?? 0);
            foreach ((array) $staff as $s):
          ?>
          <option value="<?= (int) $s['id']; ?>" <?= $tip_default === (int) $s['id'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($s['name']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="lb-row">
        <span class="mp-page-sub"><?= $tip_amount > 0.005
          ? 'Recorded for ' . htmlspecialchars($tip_for_name ?: 'the waiter')
          : 'No tip recorded yet'; ?></span>
        <button type="button" class="lb-btn primary" id="tipBtn">Save tip</button>
      </div>
      <?php endif; ?>

      <?php if ($floor_charge_on): ?>
      <div style="border-top:1px dashed var(--mp-border); margin-top:14px; padding-top:12px;">
        <label>Service charge on this bill</label>
        <?php if (!$bill->can_comp): ?>
          <div class="lb-notice"><?= htmlspecialchars((string) $bill->comp_block); ?></div>
        <?php else: ?>
        <div class="mp-page-sub">Optional. It is house money, so it joins what the guest pays — unlike a tip.</div>
        <div class="lb-row">
          <input type="text" id="lbChargePct" class="mp-form-control" inputmode="decimal" style="max-width:90px;"
            placeholder="%" value="<?= $charge_pct > 0 ? htmlspecialchars(rtrim(rtrim(number_format($charge_pct, 2, '.', ''), '0'), '.')) : ''; ?>">
          <button type="button" class="lb-btn" id="chargeBtn"><?= $charge_amt > 0.005 ? 'Change charge' : 'Add charge'; ?></button>
          <?php if ($charge_amt > 0.005): ?>
          <button type="button" class="lb-btn danger" id="chargeOffBtn">Take off</button>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <div class="mp-page-sub" style="margin-top:10px;">
        A tip is money the house is only holding. It stays out of the bill total on purpose, so the store's takings stay the store's and the waiter's stay the waiter's.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($floor_comp_on): ?>
    <div class="lb-panel" style="margin-bottom:16px;">
      <h3><i class="fa fa-gift"></i> Give it away</h3>
      <div class="mp-form-group">
        <label>Why</label>
        <select id="compReason" class="mp-form-control" onchange="lbOther(this,'compOther')">
          <?php foreach ($comp_reasons as $r): ?>
          <option value="<?= htmlspecialchars($r); ?>"><?= htmlspecialchars($r); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group" id="compOther" style="display:none;">
        <label>Say what happened</label>
        <input type="text" id="compOtherText" class="mp-form-control" maxlength="200" placeholder="e.g. Wrong gin, remade">
      </div>
      <div class="lb-row">
        <span class="mp-page-sub" id="compCount">Nothing ticked</span>
        <button type="button" class="lb-btn primary" id="compBtn">On the house</button>
      </div>
      <div class="mp-page-sub" style="margin-top:8px;">
        This is your call as the cashier — no manager approval — and it is recorded with your name on the line.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($floor_void_on): ?>
    <div class="lb-panel <?= $bill->can_void ? '' : 'locked'; ?>">
      <h3><i class="fa fa-ban"></i> Take the round off</h3>
      <?php if (!$bill->can_void): ?>
        <div class="lb-notice"><?= htmlspecialchars((string) $bill->void_block); ?></div>
      <?php else: ?>
        <div class="mp-page-sub">The charge leaves the bill and every item goes back on the shelf. If the round was already handed out, record wastage instead.</div>
        <div class="mp-form-group" style="margin-top:10px;">
          <label>Why</label>
          <select id="voidReason" class="mp-form-control" onchange="lbOther(this,'voidOther')">
            <?php foreach ($void_reasons as $r): ?>
            <option value="<?= htmlspecialchars($r); ?>"><?= htmlspecialchars($r); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group" id="voidOther" style="display:none;">
          <label>Say what happened</label>
          <input type="text" id="voidOtherText" class="mp-form-control" maxlength="200" placeholder="e.g. Guest changed their mind">
        </div>
        <?php if (!empty($void_approval['required'])): ?>
        <div class="lb-approval">
          <div class="mp-page-sub">A manager has to approve this void before the bill changes.</div>
          <div class="mp-form-group">
            <label>Approving manager</label>
            <select id="voidApprover" class="mp-form-control">
              <option value="0">Choose…</option>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Their PIN or password</label>
            <input type="password" id="voidPin" class="mp-form-control" autocomplete="off">
          </div>
        </div>
        <?php endif; ?>
        <div class="lb-row">
          <span></span>
          <button type="button" class="lb-btn danger" id="voidBtn">Void this round</button>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
var LB = {
  salesId: <?= (int) $bill->id; ?>,
  compUrl: '<?= base_url('operations/bill_comp'); ?>',
  voidUrl: '<?= base_url('operations/bill_void'); ?>',
  waiterUrl: '<?= base_url('operations/bill_waiter'); ?>',
  tipUrl: '<?= base_url('operations/bill_tip'); ?>',
  chargeUrl: '<?= base_url('operations/bill_service_charge'); ?>',
  needsApproval: <?= !empty($void_approval['required']) ? 'true' : 'false'; ?>,
  approversUrl: '<?= base_url('approvals/get_approvers'); ?>'
};

function lbOther(sel, boxId) {
  var box = document.getElementById(boxId);
  if (box) box.style.display = (sel.value === 'Other') ? '' : 'none';
}

function lbCompCount() {
  var n = document.querySelectorAll('.lb-comp:checked').length;
  var el = document.getElementById('compCount');
  if (el) el.textContent = n ? (n + ' item(s) ticked') : 'Nothing ticked';
}
document.addEventListener('change', function (e) {
  if (e.target && e.target.classList && e.target.classList.contains('lb-comp')) lbCompCount();
});

function lbPost(url, data, btn) {
  if (btn) btn.disabled = true;
  $.post(url, data, function (res) {
    if (btn) btn.disabled = false;
    if (res && res.success) {
      cqSuccess(res.message || 'Saved.');
      setTimeout(function () { location.reload(); }, 500);
    } else {
      cqError((res && res.message) || 'Something went wrong. Nothing was changed.');
    }
  }, 'json').fail(function (xhr) {
    if (btn) btn.disabled = false;
    var msg = 'Network error. Nothing was changed.';
    try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
    cqError(msg);
  });
}

function lbReason(prefix) {
  var sel = document.getElementById(prefix + 'Reason');
  var other = document.getElementById(prefix + 'OtherText');
  return { reason: sel ? sel.value : '', reason_other: other ? other.value : '' };
}

var compBtn = document.getElementById('compBtn');
if (compBtn) compBtn.addEventListener('click', function () {
  var ids = [];
  document.querySelectorAll('.lb-comp:checked').forEach(function (c) { ids.push(c.value); });
  if (!ids.length) { cqError('Tick the items going on the house first.'); return; }
  var payload = Object.assign({ sales_id: LB.salesId, line_ids: ids }, lbReason('comp'));
  cqConfirm('Give ' + ids.length + ' item(s) away? The stock leaves the shelf and the cost is booked to the house.')
    .then(function (ok) { if (ok) lbPost(LB.compUrl, payload, compBtn); });
});

var voidBtn = document.getElementById('voidBtn');
if (voidBtn) voidBtn.addEventListener('click', function () {
  var payload = Object.assign({ sales_id: LB.salesId }, lbReason('void'));
  if (LB.needsApproval) {
    payload.approver_id = document.getElementById('voidApprover').value;
    payload.approver_pin = document.getElementById('voidPin').value;
    if (!parseInt(payload.approver_id, 10)) { cqError('Choose the approving manager.'); return; }
    if (!payload.approver_pin) { cqError('Enter their PIN or password.'); return; }
  }
  cqConfirm('Void this round? The bill stops carrying it and the stock goes back on the shelf.')
    .then(function (ok) { if (ok) lbPost(LB.voidUrl, payload, voidBtn); });
});

var waiterBtn = document.getElementById('waiterBtn');
if (waiterBtn) waiterBtn.addEventListener('click', function () {
  lbPost(LB.waiterUrl, { sales_id: LB.salesId, waiter_id: document.getElementById('lbWaiter').value }, waiterBtn);
});

var tipBtn = document.getElementById('tipBtn');
if (tipBtn) tipBtn.addEventListener('click', function () {
  var amount = (document.getElementById('lbTipAmount').value || '').trim();
  if (amount === '' || isNaN(Number(amount))) { cqError('Enter the tip as a number, like 500 — or 0 to take it off.'); return; }
  lbPost(LB.tipUrl, {
    sales_id: LB.salesId,
    amount: amount,
    for_waiter_id: document.getElementById('lbTipFor').value
  }, tipBtn);
});

function lbCharge(percent, btn) {
  lbPost(LB.chargeUrl, { sales_id: LB.salesId, percent: percent }, btn);
}
var chargeBtn = document.getElementById('chargeBtn');
if (chargeBtn) chargeBtn.addEventListener('click', function () {
  var pct = (document.getElementById('lbChargePct').value || '').trim();
  if (pct === '' || isNaN(Number(pct))) { cqError('Enter the service charge as a percentage, like 10.'); return; }
  cqConfirm('Put ' + pct + '% service charge on this bill?')
    .then(function (ok) { if (ok) lbCharge(pct, chargeBtn); });
});
var chargeOffBtn = document.getElementById('chargeOffBtn');
if (chargeOffBtn) chargeOffBtn.addEventListener('click', function () {
  cqConfirm('Take the service charge off this bill?')
    .then(function (ok) { if (ok) lbCharge(0, chargeOffBtn); });
});

if (LB.needsApproval) {
  $.get(LB.approversUrl, function (res) {
    var sel = document.getElementById('voidApprover');
    if (!sel) return;
    var list = (res && res.approvers) || [];
    list.forEach(function (a) {
      var o = document.createElement('option');
      o.value = a.id; o.textContent = a.name;
      sel.appendChild(o);
    });
    if (!list.length) {
      var n = document.createElement('option');
      n.value = '0'; n.textContent = 'No manager available';
      sel.appendChild(n);
    }
  }, 'json');
}
</script>

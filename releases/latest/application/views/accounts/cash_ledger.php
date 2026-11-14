<?php
$CI =& get_instance();
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$account_names = [];
foreach ($accounts as $account) $account_names[$account->id] = $account->account_name;
$total_in = array_sum(array_column($ledger, 'amount_in'));
$total_out = array_sum(array_column($ledger, 'amount_out'));
$closing = max(0, (float)$opening_balance + $total_in - $total_out);
$cash_account_name = $cash_account_name ?? ($account_names[$cash_account_id] ?? 'Cash Account');
$from_label = show_date(date('d-m-Y', strtotime($from_date)));
$to_label = show_date(date('d-m-Y', strtotime($to_date)));
$can_move_cash = $CI->permissions('money_deposit_add');
?>
<div class="mp-cash-ledger" data-ledger-ui="2">
  <div class="cl-page-head">
    <div><h2>Cash In Hand Ledger</h2><p>Track cash received, cash paid out and your running balance.</p></div>
    <?php if ($can_move_cash): ?>
    <button type="button" class="cl-button cl-primary" data-toggle="modal" data-target="#moveCashModal"><i class="fa fa-exchange" aria-hidden="true"></i> Move Cash</button>
    <?php endif; ?>
  </div>

  <form method="get" action="<?= base_url('accounts/cash_ledger'); ?>" class="cl-filter" id="cash-ledger-filter">
    <div class="cl-field"><label for="ledger-from">From date</label><input type="date" id="ledger-from" name="from_date" value="<?= $escape($from_date); ?>" required></div>
    <div class="cl-field"><label for="ledger-to">To date</label><input type="date" id="ledger-to" name="to_date" value="<?= $escape($to_date); ?>" required></div>
    <button type="submit" class="cl-button cl-primary"><i class="fa fa-filter" aria-hidden="true"></i> View Ledger</button>
    <a class="cl-button cl-secondary" href="<?= base_url('accounts/cash_ledger'); ?>">Today</a>
    <span class="cl-filter-note"><i class="fa fa-calendar" aria-hidden="true"></i> <?= $escape($from_label); ?> — <?= $escape($to_label); ?></span>
  </form>

  <section class="cl-summary" aria-label="Cash movement summary">
    <article class="cl-stat"><span class="cl-stat-label">Opening balance</span><strong><?= $CI->currency((float)$opening_balance); ?></strong><span class="cl-stat-hint">Before <?= $escape($from_label); ?></span></article>
    <article class="cl-stat cl-in"><span class="cl-stat-label"><i class="fa fa-arrow-down" aria-hidden="true"></i> Cash in</span><strong><?= $CI->currency($total_in); ?></strong><span class="cl-stat-hint">Received in this period</span></article>
    <article class="cl-stat cl-out"><span class="cl-stat-label"><i class="fa fa-arrow-up" aria-hidden="true"></i> Cash out</span><strong><?= $CI->currency($total_out); ?></strong><span class="cl-stat-hint">Paid out in this period</span></article>
    <article class="cl-stat"><span class="cl-stat-label">Net movement</span><strong><?= $CI->currency($total_in - $total_out); ?></strong><span class="cl-stat-hint">Cash in minus cash out</span></article>
    <article class="cl-stat cl-balance"><span class="cl-stat-label">Closing balance</span><strong><?= $CI->currency($closing); ?></strong><span class="cl-stat-hint"><?= $escape($cash_account_name); ?> · <?= $escape($to_label); ?></span></article>
  </section>

  <section class="cl-table-card" aria-labelledby="ledger-transactions-heading">
    <div class="cl-table-head"><div><h3 id="ledger-transactions-heading">Transactions</h3><p><?= count($ledger); ?> transaction<?= count($ledger) !== 1 ? 's' : ''; ?> in the selected period</p></div><button type="button" class="cl-button cl-secondary" id="ledger-export"><i class="fa fa-download" aria-hidden="true"></i> Export CSV</button></div>
    <div class="cl-table-scroll" tabindex="0" role="region" aria-label="Cash ledger transactions; scroll horizontally for all columns">
      <table id="ledger-table" class="cl-table">
        <caption class="cl-sr-only">Cash ledger from <?= $escape($from_label); ?> to <?= $escape($to_label); ?></caption>
        <thead><tr><th scope="col">Date</th><th scope="col">Transaction</th><th scope="col">Payment mode</th><th scope="col">Account</th><th scope="col">Direction</th><th scope="col" class="cl-number">Cash in</th><th scope="col" class="cl-number">Cash out</th><th scope="col" class="cl-number">Balance</th><th scope="col">Note / reference</th><th scope="col">Recorded by</th></tr></thead>
        <tbody>
          <?php $running = (float)$opening_balance; if ($running > 0): ?>
          <tr class="cl-opening"><td colspan="5"><strong>Opening balance</strong><span class="cl-row-meta">Carried forward before <?= $escape($from_label); ?></span></td><td class="cl-number">—</td><td class="cl-number">—</td><td class="cl-number cl-amount"><?= $CI->currency($running); ?></td><td colspan="2"></td></tr>
          <?php endif; ?>
          <?php if (!$ledger): ?>
          <tr class="cl-empty-row"><td colspan="10"><div class="cl-empty"><span class="cl-empty-icon"><i class="fa fa-exchange" aria-hidden="true"></i></span><strong>No cash movements in this period</strong><p>Choose another date range to view earlier transactions.</p></div></td></tr>
          <?php endif; ?>
          <?php foreach ($ledger as $row):
            $incoming = $row['direction'] === 'IN';
            $running = max(0, $running + (float)$row['amount_in'] - (float)$row['amount_out']);
          ?>
          <tr><td class="cl-date"><?= $escape(show_date(date('d-m-Y', strtotime($row['txn_date'])))); ?><span class="cl-row-meta"><?= $escape($row['txn_time']); ?></span></td><td class="cl-description"><?= $escape($row['description']); ?></td><td><?= $escape($row['mode']); ?></td><td><?= $escape($account_names[$row['account_id']] ?? 'Not linked'); ?></td><td><span class="cl-badge <?= $incoming ? 'cl-badge-in' : 'cl-badge-out'; ?>"><?= $incoming ? 'Cash in' : 'Cash out'; ?></span></td><td class="cl-number cl-in-amount"><?= $row['amount_in'] > 0 ? $CI->currency($row['amount_in']) : '—'; ?></td><td class="cl-number cl-out-amount"><?= $row['amount_out'] > 0 ? $CI->currency($row['amount_out']) : '—'; ?></td><td class="cl-number cl-amount"><?= $CI->currency($running); ?></td><td class="cl-note"><?= $escape($row['note']); ?></td><td><?= $escape($row['created_by']); ?></td></tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot><tr><th colspan="5" scope="row">Period totals</th><td class="cl-number cl-in-amount"><?= $CI->currency($total_in); ?></td><td class="cl-number cl-out-amount"><?= $CI->currency($total_out); ?></td><td class="cl-number cl-amount"><?= $CI->currency($running); ?></td><td colspan="2"></td></tr></tfoot>
      </table>
    </div>
  </section>
</div>

<?php if ($can_move_cash): ?>
<div class="modal fade" id="moveCashModal" tabindex="-1" role="dialog" aria-labelledby="move-cash-title">
  <div class="modal-dialog" role="document"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button><h3 id="move-cash-title">Move cash between accounts</h3><p>Record a cash transfer or bank deposit.</p></div>
    <form id="move-cash-form">
      <div class="modal-body">
        <input type="hidden" name="<?= $escape($this->security->get_csrf_token_name()); ?>" value="<?= $escape($this->security->get_csrf_hash()); ?>">
        <div class="cl-field"><label for="mc_amount">Amount <span aria-hidden="true">*</span></label><input type="number" step="0.01" min="0.01" name="amount" id="mc_amount" placeholder="0.00" required></div>
        <div class="cl-account-grid">
          <div class="cl-field"><label for="mc_debit">From account <span aria-hidden="true">*</span></label><select name="debit_account_id" id="mc_debit" required><option value="">Select source account</option><?php foreach ($accounts as $account): ?><option value="<?= (int)$account->id; ?>" <?= (int)$cash_account_id === (int)$account->id ? 'selected' : ''; ?>><?= $escape($account->account_name); ?></option><?php endforeach; ?></select></div>
          <div class="cl-field"><label for="mc_credit">To account <span aria-hidden="true">*</span></label><select name="credit_account_id" id="mc_credit" required><option value="">Select destination account</option><?php foreach ($accounts as $account): ?><option value="<?= (int)$account->id; ?>"><?= $escape($account->account_name); ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="cl-field"><label for="mc_note">Note / reference <span class="cl-optional">(optional)</span></label><input type="text" name="note" id="mc_note" placeholder="e.g. Cash deposited at bank"></div>
        <p id="move-cash-error" class="cl-form-error" role="alert" hidden></p>
      </div>
      <div class="modal-footer"><button type="button" class="cl-button cl-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="cl-button cl-primary" id="move-cash-submit">Move Cash</button></div>
    </form>
  </div></div>
</div>
<?php endif; ?>

<style>
.mp-cash-ledger{color:var(--mp-text,#292524);min-width:0;padding-bottom:24px}
.mp-cash-ledger .cl-page-head{width:100%;clear:both;display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:24px}
.mp-cash-ledger .cl-page-head h2{font-size:24px;font-weight:700;letter-spacing:-.5px;margin:0 0 6px;color:var(--mp-ink,#1c1917)}
.mp-cash-ledger .cl-page-head p,.mp-cash-ledger .cl-table-head p{margin:0;color:var(--mp-muted,#78716c);font-size:13px;line-height:1.5}
.cl-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:10px 16px;border:1px solid transparent;border-radius:10px;font-family:inherit;font-size:13px;font-weight:600;line-height:1.4;cursor:pointer;text-decoration:none;white-space:nowrap;transition:background .15s}
.cl-button:hover,.cl-button:focus{text-decoration:none}
.cl-button.cl-primary{background:var(--mp-primary,#0057ff);border-color:var(--mp-primary,#0057ff);color:#fff}
.cl-button.cl-primary:hover{filter:brightness(.94);color:#fff}
.cl-button.cl-secondary{background:var(--mp-surface,#fff);border-color:var(--mp-border,#e7e5e4);color:var(--mp-text,#292524)}
.cl-button.cl-secondary:hover{background:var(--mp-bg,#f5f5f4);color:var(--mp-text,#292524)}
.cl-button:focus-visible{outline:3px solid #93b4ff;outline-offset:2px}
.cl-button:disabled{opacity:.55;cursor:wait}
.mp-cash-ledger .cl-filter{display:flex;align-items:flex-end;flex-wrap:wrap;gap:14px;background:var(--mp-surface,#fff);border:1px solid var(--mp-border,#e7e5e4);border-radius:14px;padding:20px;margin-bottom:20px}
.cl-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.cl-field label{font-size:12px;font-weight:600;color:var(--mp-text,#292524);margin:0}
.cl-field input,.cl-field select{height:42px;box-sizing:border-box;width:100%;max-width:100%;min-width:0;padding:9px 12px;border:1px solid var(--mp-border,#e7e5e4);border-radius:9px;background:var(--mp-surface,#fff);color:var(--mp-ink,#1c1917);font-family:inherit;font-size:14px}
.cl-field input:focus,.cl-field select:focus{outline:2px solid #93b4ff;outline-offset:1px}
.mp-cash-ledger .cl-filter .cl-field{flex:0 1 190px}
.mp-cash-ledger .cl-filter-note{margin-left:auto;align-self:center;display:flex;align-items:center;gap:8px;color:var(--mp-muted,#78716c);font-size:12px}
.mp-cash-ledger .cl-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:24px}
.mp-cash-ledger .cl-stat{background:var(--mp-surface,#fff);border:1px solid var(--mp-border,#e7e5e4);border-radius:14px;padding:19px 18px;display:flex;flex-direction:column;gap:12px;min-width:0}
.mp-cash-ledger .cl-stat-label{font-size:12px;font-weight:600;color:var(--mp-muted,#78716c);display:flex;align-items:center;gap:7px;line-height:1.4}
.mp-cash-ledger .cl-stat strong{font-size:clamp(18px,1.45vw,24px);line-height:1.25;letter-spacing:-.5px;font-variant-numeric:tabular-nums;overflow-wrap:anywhere;color:var(--mp-ink,#1c1917)}
.mp-cash-ledger .cl-stat-hint{font-size:11px;line-height:1.5;color:var(--mp-muted,#78716c)}
.mp-cash-ledger .cl-in strong,.mp-cash-ledger .cl-in .cl-stat-label{color:#087853}
.mp-cash-ledger .cl-out strong,.mp-cash-ledger .cl-out .cl-stat-label{color:#c43b35}
.mp-cash-ledger .cl-balance{background:var(--mp-primary,#0057ff);border-color:var(--mp-primary,#0057ff)}
.mp-cash-ledger .cl-balance strong,.mp-cash-ledger .cl-balance .cl-stat-label{color:#fff}
.mp-cash-ledger .cl-balance .cl-stat-hint{color:#fff;opacity:.88}
.mp-cash-ledger .cl-table-card{border:1px solid var(--mp-border,#e7e5e4);border-radius:14px;background:var(--mp-surface,#fff);overflow:hidden}
.mp-cash-ledger .cl-table-head{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:20px;border-bottom:1px solid var(--mp-border,#e7e5e4)}
.mp-cash-ledger .cl-table-head h3{font-size:15px;font-weight:700;margin:0 0 4px;color:var(--mp-ink,#1c1917)}
.mp-cash-ledger .cl-table-scroll{overflow:auto;max-height:65vh;scrollbar-color:#cbd5e1 transparent}
.mp-cash-ledger .cl-table{width:100%;min-width:1080px;border-collapse:separate;border-spacing:0;margin:0;font-size:12px}
.mp-cash-ledger .cl-table th,.mp-cash-ledger .cl-table td{padding:15px 16px;vertical-align:middle;border:0;border-bottom:1px solid var(--mp-border,#e7e5e4);line-height:1.6}
.mp-cash-ledger .cl-table thead th{position:sticky;top:0;z-index:1;background:var(--mp-bg,#f5f5f4);color:var(--mp-muted,#78716c);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;text-align:left}
.mp-cash-ledger .cl-table .cl-number{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
.mp-cash-ledger .cl-table tbody tr:not(.cl-empty-row):hover{background:var(--mp-bg,#f5f5f4)}
.mp-cash-ledger .cl-table .cl-amount,.mp-cash-ledger .cl-description{font-weight:600}
.mp-cash-ledger .cl-row-meta{display:block;color:var(--mp-muted,#78716c);font-size:11px;font-weight:400;white-space:nowrap}
.mp-cash-ledger .cl-date{white-space:nowrap}
.mp-cash-ledger .cl-note{max-width:240px;min-width:140px;overflow-wrap:anywhere}
.mp-cash-ledger .cl-in-amount{color:#087853;font-weight:600}
.mp-cash-ledger .cl-out-amount{color:#c43b35;font-weight:600}
.mp-cash-ledger .cl-opening{background:var(--mp-bg,#f5f5f4)}
.mp-cash-ledger .cl-badge{display:inline-flex;border-radius:20px;padding:3px 9px;font-size:11px;font-weight:600;white-space:nowrap}
.mp-cash-ledger .cl-badge-in{background:#e8f6ee;color:#087853}
.mp-cash-ledger .cl-badge-out{background:#fff0ed;color:#b3332d}
.mp-cash-ledger .cl-empty{text-align:center;padding:28px 16px;display:flex;flex-direction:column;align-items:center;gap:8px}
.mp-cash-ledger .cl-empty-icon{display:grid;place-items:center;width:42px;height:42px;border-radius:50%;background:var(--mp-bg,#f5f5f4);color:var(--mp-muted,#78716c);font-size:17px;margin-bottom:3px}
.mp-cash-ledger .cl-empty strong{font-size:14px;font-weight:600}
.mp-cash-ledger .cl-empty p{margin:0;color:var(--mp-muted,#78716c);font-size:12px}
.mp-cash-ledger .cl-table tfoot{background:var(--mp-bg,#f5f5f4);font-weight:600}
.mp-cash-ledger .cl-table tfoot td,.mp-cash-ledger .cl-table tfoot th{border-bottom:0}
.cl-sr-only{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
#moveCashModal .modal-dialog{max-width:560px;width:calc(100% - 40px);margin:60px auto}
#moveCashModal .modal-content{background:var(--mp-surface,#fff);color:var(--mp-text,#292524);border:1px solid var(--mp-border,#e7e5e4);border-radius:16px;overflow:hidden;box-shadow:0 20px 65px rgba(0,0,0,.18)}
#moveCashModal .modal-header{padding:24px;border-bottom:1px solid var(--mp-border,#e7e5e4);background:var(--mp-surface,#fff)}
#moveCashModal .modal-header h3{margin:0 28px 7px 0;font-size:18px;font-weight:700}
#moveCashModal .modal-header p{margin:0;color:var(--mp-muted,#78716c);font-size:13px}
#moveCashModal .close{color:var(--mp-text,#292524);opacity:1;font-size:26px}
#moveCashModal .modal-body{padding:24px;display:grid;gap:20px;max-height:60vh;overflow:auto}
#moveCashModal .cl-account-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px}
#moveCashModal .cl-optional{font-weight:400;color:var(--mp-muted,#78716c)}
#moveCashModal .modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:18px 24px;background:var(--mp-bg,#f5f5f4);border-top:1px solid var(--mp-border,#e7e5e4)}
#moveCashModal .modal-footer:before,#moveCashModal .modal-footer:after{display:none}
#moveCashModal .cl-form-error{color:#b3332d;background:#fff0ed;padding:10px;border-radius:8px;margin:0;font-size:13px}
@media(min-width:1024px) and (max-width:1279px){.mp-cash-ledger .cl-summary{grid-template-columns:repeat(3,minmax(0,1fr))}.mp-cash-ledger .cl-filter-note{width:100%;margin-left:0}.mp-cash-ledger .cl-stat strong{font-size:22px}}
</style>
<script>
(function($){
  'use strict';
  var filter = document.getElementById('cash-ledger-filter');
  var from = document.getElementById('ledger-from'), to = document.getElementById('ledger-to');
  function validateRange(){to.setCustomValidity(from.value && to.value && from.value > to.value ? 'To date must be on or after From date.' : '');}
  from.addEventListener('change', validateRange); to.addEventListener('change', validateRange);
  filter.addEventListener('submit', function(e){validateRange();if(!filter.checkValidity()){e.preventDefault();filter.reportValidity();}});
  document.getElementById('ledger-export').addEventListener('click', function(){
    var csv = [];
    document.querySelectorAll('#ledger-table tr').forEach(function(tr){
      if(tr.classList.contains('cl-empty-row')) return;
      var values = [];
      tr.querySelectorAll('td,th').forEach(function(cell){
        var text = cell.innerText.replace(/\s*\n\s*/g, ' ').trim();
        if(/^[=+@-]/.test(text)) text = "'" + text;
        values.push('"' + text.replace(/"/g, '""') + '"');
        for(var i=1;i<cell.colSpan;i++) values.push('""');
      });
      csv.push(values.join(','));
    });
    var blob = new Blob(['\uFEFF' + csv.join('\r\n')], {type:'text/csv;charset=utf-8;'});
    var url = URL.createObjectURL(blob), a = document.createElement('a');
    a.href = url; a.download = 'Cash_Ledger_' + from.value + '_to_' + to.value + '.csv';
    document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(url);},1000);
  });
  var form = document.getElementById('move-cash-form');
  if(form) form.addEventListener('submit', function(e){
    e.preventDefault();
    var error = document.getElementById('move-cash-error'), button = document.getElementById('move-cash-submit');
    error.hidden = true;
    if(!form.checkValidity()){form.reportValidity();return;}
    if(document.getElementById('mc_debit').value === document.getElementById('mc_credit').value){error.textContent='Choose different source and destination accounts.';error.hidden=false;return;}
    button.disabled=true;button.textContent='Moving cash…';
    var data = $(form).serializeArray();data.push({name:'deposit_date',value:<?= json_encode(date('d-m-Y')); ?>});
    $.ajax({url:<?= json_encode(base_url('money_deposit/new_money_deposit')); ?>,type:'POST',data:data})
      .done(function(result){
        if(String(result).trim()==='success'){location.reload();return;}
        error.textContent='The transfer was not saved. Please check the details and try again.';error.hidden=false;
      })
      .fail(function(){error.textContent='The transfer could not be confirmed. Check the ledger before retrying.';error.hidden=false;})
      .always(function(){button.disabled=false;button.textContent='Move Cash';});
  });
  var active = document.querySelector('.mp-nav-item[href="<?= base_url('accounts'); ?>"]');
  if(active){document.querySelectorAll('.mp-nav-item.active').forEach(function(item){item.classList.remove('active');});active.classList.add('active');var group=active.closest('.mp-nav-group');if(group)group.classList.add('open');}
})(jQuery);
</script>

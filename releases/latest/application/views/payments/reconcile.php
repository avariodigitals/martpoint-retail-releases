<style>
.rc-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:16px}
.rc-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border,#E2E8F0);border-radius:12px;padding:14px}
.rc-card .n{font-size:22px;font-weight:700}
.rc-card .l{font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--mp-muted,#64748B)}
.rc-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600}
.rc-unmatched{background:#FEF2F2;color:#991B1B}
.rc-late_payment{background:#FEF3C7;color:#92400E;font-weight:600}
.rc-discrepancy{background:#FFFBEB;color:#92400E}
.rc-partial{background:#EFF6FF;color:#1D4ED8}
.rc-refund{background:#F5F3FF;color:#6D28D9}
.rc-dispute{background:#FFF1F2;color:#BE123C}
.rc-open{color:#991B1B;font-weight:600}
.rc-acknowledged{color:#92400E;font-weight:600}
.rc-resolved{color:#065F46;font-weight:600}
.rc-dismissed{color:#64748B;font-weight:600}
.rc-act{padding:4px 8px;border-radius:8px;border:1px solid var(--mp-border,#E2E8F0);background:#fff;font-size:11px;cursor:pointer;margin-right:4px}
.rc-act:hover{background:var(--mp-bg,#F1F5F9)}
.rc-filter{display:flex;gap:8px;align-items:center;margin-bottom:14px;flex-wrap:wrap}
.rc-filter select,.rc-filter button{padding:8px 12px;border:1px solid var(--mp-border,#E2E8F0);border-radius:8px;background:var(--mp-surface,#fff);font-size:13px}
.rc-scan{margin-left:auto;padding:8px 14px;border-radius:8px;border:none;background:var(--mp-primary,#0057FF);color:#fff;font-weight:600;cursor:pointer}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Exception queue for payment anomalies — provider receipts, mismatches, partials, refunds, disputes</div>
  </div>
</div>

<div class="rc-cards">
  <div class="rc-card"><div class="n rc-open"><?= (int)($counts['open'] ?? 0); ?></div><div class="l">Open</div></div>
  <div class="rc-card"><div class="n rc-unmatched"><?= (int)($counts['unmatched_open'] ?? 0); ?></div><div class="l">Unmatched</div></div>
  <div class="rc-card"><div class="n rc-late_payment"><?= (int)($counts['late_payment_open'] ?? 0); ?></div><div class="l">Late payments</div></div>
  <div class="rc-card"><div class="n"><?= (int)($counts['discrepancy_open'] ?? 0); ?></div><div class="l">Discrepancies</div></div>
  <div class="rc-card"><div class="n"><?= (int)($counts['partial_open'] ?? 0); ?></div><div class="l">Partial</div></div>
  <div class="rc-card"><div class="n"><?= (int)($counts['refund_open'] ?? 0); ?></div><div class="l">Refunds</div></div>
  <div class="rc-card"><div class="n"><?= (int)($counts['dispute_open'] ?? 0); ?></div><div class="l">Disputes</div></div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head">
    <h3>Exception Queue</h3>
  </div>
  <div class="box-body">
    <form class="rc-filter" method="get" action="<?= base_url('reconcile'); ?>">
      <select name="status" onchange="this.form.submit()">
        <?php foreach(array('open'=>'Open','acknowledged'=>'Acknowledged','resolved'=>'Resolved','dismissed'=>'Dismissed','all'=>'All') as $k=>$v): ?>
        <option value="<?= $k; ?>" <?= $f_status===$k?'selected':''; ?>><?= $v; ?></option>
        <?php endforeach; ?>
      </select>
      <select name="type" onchange="this.form.submit()">
        <option value="">All types</option>
        <?php foreach(array('unmatched','late_payment','discrepancy','partial','refund','dispute') as $t): ?>
        <option value="<?= $t; ?>" <?= $f_type===$t?'selected':''; ?>><?= ucwords(str_replace('_',' ',$t)); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="rc-scan" id="rcScanBtn" onclick="rcScan()"><i class="fa fa-refresh"></i> Run Scan</button>
    </form>
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" width="100%">
        <thead><tr>
          <th>ID</th><th>Type</th><th>Provider</th><th>Reference</th><th>Linked</th><th>Amount</th><th>Expected</th><th>Detail</th><th>Detected</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php if(empty($exceptions)): ?>
          <tr><td colspan="11" style="text-align:center;color:var(--mp-muted,#64748B);padding:24px;">No <?= htmlspecialchars($f_status); ?> exceptions.</td></tr>
        <?php else: foreach($exceptions as $e): ?>
          <tr>
            <td><?= (int)$e->id; ?></td>
            <td><span class="rc-badge rc-<?= htmlspecialchars($e->exception_type); ?>"><?= htmlspecialchars($e->exception_type); ?></span></td>
            <td><?= htmlspecialchars($e->provider); ?><?= $e->provider === 'manual' ? ' <span class="rc-badge" style="background:#F1F5F9;color:#475569;">manually recorded</span>' : ''; ?></td>
            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($e->reference); ?></td>
            <td>
              <?php if($e->sales_id): ?>Sale #<?= (int)$e->sales_id; ?><?php endif; ?>
              <?php if($e->order_id): ?>Order #<?= (int)$e->order_id; ?><?php endif; ?>
              <?php if(!$e->sales_id && !$e->order_id): ?><span style="color:var(--mp-muted,#64748B);">—</span><?php endif; ?>
            </td>
            <td><?= $e->amount !== null ? store_number_format($e->amount) : '—'; ?></td>
            <td><?= $e->expected_amount !== null ? store_number_format($e->expected_amount) : '—'; ?></td>
            <td style="max-width:220px;"><?= htmlspecialchars($e->detail); ?></td>
            <td style="white-space:nowrap;"><?= htmlspecialchars($e->created_date); ?><br><span style="font-size:10px;color:var(--mp-muted,#64748B);">via <?= htmlspecialchars($e->detected_by); ?></span></td>
            <td class="rc-<?= htmlspecialchars($e->status); ?>"><?= htmlspecialchars($e->status); ?></td>
            <td style="white-space:nowrap;">
              <?php if($e->status === 'open' || $e->status === 'acknowledged'): ?>
              <button class="rc-act" onclick="rcResolve(<?= (int)$e->id; ?>,'resolved')">Resolve</button>
              <button class="rc-act" onclick="rcResolve(<?= (int)$e->id; ?>,'acknowledged')">Acknowledge</button>
              <?php if($e->exception_type !== 'dispute'): ?>
              <button class="rc-act" onclick="rcResolve(<?= (int)$e->id; ?>,'dispute')">Dispute</button>
              <?php endif; ?>
              <button class="rc-act" onclick="rcResolve(<?= (int)$e->id; ?>,'dismissed')">Dismiss</button>
              <?php else: ?>
              <span style="font-size:11px;color:var(--mp-muted,#64748B);"><?= htmlspecialchars($e->resolved_by ?: ''); ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
var rcCsrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var rcCsrfHash = '<?= $this->security->get_csrf_hash(); ?>';
var rcRowType = { <?php $rt=array(); foreach($exceptions as $e){ $rt[] = (int)$e->id . ':' . json_encode($e->exception_type); } echo implode(',', $rt); ?> };
function rcScan(){
  var btn = document.getElementById('rcScanBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Scanning…';
  var d = {}; d[rcCsrfName] = rcCsrfHash;
  $.post('<?= base_url('reconcile/scan'); ?>', d, function(res){
    if(res.csrf_hash) rcCsrfHash = res.csrf_hash;
    btn.disabled = false; btn.innerHTML = '<i class="fa fa-refresh"></i> Run Scan';
    if(res.status === 'success'){
      var f = res.found || {};
      toastr.success('Scan complete — '+(f.unmatched||0)+' unmatched, '+(f.discrepancy||0)+' discrepancies, '+(f.partial||0)+' partial, '+(f.refund||0)+' refunds detected');
      setTimeout(function(){ window.location.reload(); }, 1200);
    } else { toastr.error(res.message || 'Scan failed'); }
  }, 'json').fail(function(){ btn.disabled=false; btn.innerHTML='<i class="fa fa-refresh"></i> Run Scan'; toastr.error('Scan failed'); });
}
function rcResolve(id, action){
  var note = '';
  var moneyTypes = ['unmatched','late_payment'];
  var type = (window.rcRowType && window.rcRowType[id]) || '';
  var needsNote = (action === 'acknowledged' && moneyTypes.indexOf(type) !== -1);
  if(needsNote){
    // Money-received exceptions: a bare acknowledgement hides real cash.
    var raw = prompt('This exception reports money actually received. Acknowledging it does NOT settle the money.\n\nRecord what happened (refund sent, order reinstated, etc.) — required:');
    if(raw === null) return;
    note = (raw || '').trim();
    if(!note){ toastr.error('A note is required for money-received exceptions.'); return; }
  } else if(action === 'resolved' || action === 'dismissed' || action === 'dispute'){
    var raw2 = prompt((action === 'dispute' ? 'Dispute' : action === 'resolved' ? 'Resolve' : 'Dismiss') + ' this exception? Add a note (optional):');
    if(raw2 === null) return;
    note = raw2;
  }
  var d = {id:id, action:action, note:note}; d[rcCsrfName] = rcCsrfHash;
  $.post('<?= base_url('reconcile/resolve'); ?>', d, function(res){
    if(res.csrf_hash) rcCsrfHash = res.csrf_hash;
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(function(){ window.location.reload(); }, 800); }
    else toastr.error(res.message || 'Update failed');
  }, 'json').fail(function(){ toastr.error('Update failed'); });
}
</script>

<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance(); ?>
<style>
.op-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.op-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.op-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.op-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.op-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.op-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:800;text-transform:uppercase}
.op-entered{background:#FEF3C7;color:#92400E}.op-reviewed{background:#DBEAFE;color:#1E40AF}
.op-approved{background:#D1FAE5;color:#065F46}.op-rejected{background:#FEE2E2;color:#991B1B}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub">Migrated balances post only after enter → review → approve. The approver is never the enterer.</div></div>
</div>

<?php if($can['enter']): ?>
<div class="op-box">
  <h4>Enter opening position</h4>
  <div class="mp-page-sub" style="margin-bottom:8px">Old debt, unused funds and remaining sessions are entered <b>separately</b>, each with an evidence reference and an “as of” date. Unknown values are left out — pending is not zero.</div>
  <form onsubmit="return opEnter(this);">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px">
      <select name="patient_id" required style="flex:2;min-width:180px;padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px">
        <option value="">— patient —</option>
        <?php foreach($patients as $pt): ?>
          <option value="<?= (int)$pt->id; ?>"><?= htmlspecialchars($pt->patient_code . ' — ' . ($pt->customer_name ?? '')); ?></option>
        <?php endforeach; ?>
      </select>
      <select name="item_type" id="opType" required style="padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px" onchange="opTypeChange()">
        <option value="outstanding_debt">Outstanding debt (unpaid)</option>
        <option value="unused_money">Unused funds (prepaid money)</option>
        <option value="unused_sessions">Remaining sessions</option>
      </select>
      <input type="number" step="0.01" name="amount" placeholder="Amount" style="width:120px;padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px">
      <input type="number" name="units_total" id="opUnits" placeholder="Units" disabled style="width:90px;padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px">
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <label style="font-size:11px;color:var(--mp-muted);align-self:center">As of&nbsp;<input type="date" name="cutoff_date" required style="padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px"></label>
      <input name="evidence_document_id" placeholder="Evidence doc id (optional)" style="width:170px;padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px">
      <input name="evidence_note" placeholder="Evidence note (statement ref, page…)" style="flex:2;min-width:200px;padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px">
      <button class="mp-qa-btn blue" type="submit">Enter — pending review</button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="op-box">
  <h4>Positions</h4>
  <table class="op-table">
    <tr><th>#</th><th>Patient</th><th>Items</th><th>Entered by</th><th>Reviewed by</th><th>Approved by</th><th>Status</th><th>Actions</th></tr>
    <?php foreach($positions as $p): ?>
    <tr>
      <td>OP-<?= (int)$p->id; ?></td>
      <td><?= htmlspecialchars(($p->patient_code ?? '') . ' ' . ($p->customer_name ?? '')); ?></td>
      <td>
        <?php foreach(($items[$p->id] ?? []) as $it): $pl = json_decode($it->payload_json, true) ?: array(); ?>
          <div><?= htmlspecialchars(mp_code_label($it->item_type)); ?><?= $it->amount !== null ? ' — ' . $CI->currency($it->amount) : ''; ?>
            <?php if(!empty($pl['units_total'])): ?><?= (int)$pl['units_total']; ?> units<?php endif; ?>
            <?php if(!empty($pl['entitlement_id'])): ?>→ ENT-<?= (int)$pl['entitlement_id']; ?><?php endif; ?>
            <span class="op-badge op-<?= htmlspecialchars($it->status); ?>"><?= htmlspecialchars(mp_code_label($it->status)); ?></span>
          </div>
        <?php endforeach; ?>
      </td>
      <td><?= htmlspecialchars(mp_first_name($p->entered_by, $p->entered_by_name ?? '')); ?></td>
      <td><?= $p->reviewed_by ? htmlspecialchars(mp_first_name($p->reviewed_by, $p->reviewed_by_name ?? '')) : '—'; ?></td>
      <td><?= $p->approved_by ? htmlspecialchars(mp_first_name($p->approved_by, $p->approved_by_name ?? '')) : '—'; ?><?= $p->reject_reason ? '<br><small>' . htmlspecialchars($p->reject_reason) . '</small>' : ''; ?></td>
      <td><span class="op-badge op-<?= htmlspecialchars($p->status); ?>"><?= htmlspecialchars(mp_code_label($p->status)); ?></span></td>
      <td>
        <?php if($can['review'] && $p->status === 'entered'): ?>
          <button class="mp-qa-btn blue" onclick="opAct(<?= (int)$p->id; ?>,'opening_review')">Review</button>
        <?php endif; ?>
        <?php if($can['review'] && $p->status === 'reviewed'): ?>
          <button class="mp-qa-btn green" onclick="opAct(<?= (int)$p->id; ?>,'opening_approve')">Approve &amp; Post</button>
        <?php endif; ?>
        <?php if($can['review'] && !in_array($p->status, array('approved','rejected'))): ?>
          <button class="mp-qa-btn" onclick="opReject(<?= (int)$p->id; ?>)">Reject</button>
        <?php endif; ?>
        <?php if(($can['delete'] ?? false) && in_array($p->status, array('entered','rejected'))): ?>
          <button class="mp-qa-btn" style="color:#B91C1C;border-color:#FCA5A5" onclick="opDelete(<?= (int)$p->id; ?>)">Delete</button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($positions)): ?><tr><td colspan="8" style="color:var(--mp-muted)">No opening positions yet.</td></tr><?php endif; ?>
  </table>
</div>

<script>
function opToast(m, t){ if(window.cqToast) window.cqToast(m, t || 'info'); }
function opTypeChange(){ document.getElementById('opUnits').disabled = document.getElementById('opType').value !== 'unused_sessions'; }
function opPost(url, data){
  var fd = new FormData();
  Object.keys(data||{}).forEach(function(k){ fd.append(k, data[k]); });
  // Every mutating call needs the session token — without it the server
  // silently refuses and the buttons look dead.
  if(window.csrfName){ fd.append(window.csrfName, window.csrfHash); }
  else {
    var tok = document.querySelector('input[name="<?= $CI->security->get_csrf_token_name(); ?>"]');
    if(tok) fd.append(tok.name, tok.value);
  }
  return fetch(url, {method:'POST', body:fd}).then(function(r){ return r.json(); });
}
function opEnter(f){
  var fd = new FormData(f);
  if(document.getElementById('opType').value !== 'unused_sessions') fd.delete('units_total');
  fetch('<?= base_url('patient_funds/opening_enter/'); ?>' + fd.get('patient_id'), {method:'POST', body:fd})
    .then(function(r){ return r.json(); })
    .then(function(d){ opToast(d.message || '', d.status==='success'?'success':'error'); if(d.status==='success') location.reload(); });
  return false;
}
function opAct(id, act){
  opPost('<?= base_url('patient_funds/'); ?>' + act + '/' + id, {})
    .then(function(d){ opToast(d.message || '', d.status==='success'?'success':'error'); if(d.status==='success') location.reload(); });
}
function opReject(id){
  window.mpPrompt({title:'Reject this opening position', message:'Why is it being rejected? The reason is kept with the record.', required:true, okText:'Reject', danger:true})
    .then(function(reason){
      if(!reason) return;
      opPost('<?= base_url('patient_funds/opening_reject/'); ?>' + id, {reason: reason})
        .then(function(d){ opToast(d.message || '', d.status==='success'?'success':'error'); if(d.status==='success') location.reload(); });
    });
}
function opDelete(id){
  window.mpConfirmAsync('Delete this opening position? Nothing has been posted from it, so no balances change. This cannot be undone.', {title:'Delete opening position', okText:'Delete'})
    .then(function(ok){
      if(!ok) return;
      opPost('<?= base_url('patient_funds/opening_delete/'); ?>' + id, {})
        .then(function(d){ opToast(d.message || '', d.status==='success'?'success':'error'); if(d.status==='success') location.reload(); });
    });
}
</script>

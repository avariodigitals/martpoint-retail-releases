<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $this->load->view('components/book_session'); $CI =& get_instance(); ?>
<style>
.ss-card{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;display:flex!important;flex-direction:column!important;justify-content:space-between!important;gap:10px!important;min-height:120px!important}
.ss-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(255px,1fr))!important;gap:12px!important;align-items:stretch!important;justify-content:start!important;margin-bottom:8px!important}
.ss-grid .ss-card{margin-bottom:0!important;text-align:left!important}
.ss-card .ss-acts{display:flex!important;gap:6px!important;flex-wrap:wrap!important;margin-top:auto!important}
.ss-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.st-scheduled{background:#E0E7FF!important;color:#4338CA!important}.st-checked_in{background:#FEF3C7!important;color:#92400E!important}
.st-in_progress{background:#DBEAFE!important;color:#1E40AF!important}.st-completed{background:#DCFCE7!important;color:#166534!important}
.st-cancelled,.st-no_show{background:#FEE2E2!important;color:#991B1B!important}.st-interrupted{background:#FFEDD5!important;color:#9A3412!important}
.ss-sec{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.6px!important;color:var(--mp-muted)!important;margin:18px 0 8px!important}
.ss-note{font-size:12px!important;color:var(--mp-muted)!important;margin:0 0 10px!important}
.ss-days{display:flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important;margin:0 0 12px!important}
.ss-days-lbl{font-size:10px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin-right:2px!important}
.ss-days a{font-size:11px!important;font-weight:700!important;padding:5px 11px!important;border-radius:999px!important;border:1px solid var(--mp-border)!important;color:var(--mp-muted)!important;text-decoration:none!important;background:#fff!important}
.ss-days a.on{background:#EEF2FF!important;border-color:#6366F1!important;color:#4338CA!important}
.ss-next{font-size:12px!important;color:var(--mp-muted)!important;margin:0 0 12px!important}
.ss-next b{color:#166534!important}
.ss-search{width:100%!important;max-width:420px!important;font-size:13px!important;padding:8px 12px!important;border:1px solid var(--mp-border,#E2E8F0)!important;border-radius:10px!important;background:#fff!important;margin:0 0 14px!important}
.ss-card.ss-off{display:none!important}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub">Completion applies the fee and consumes entitlement exactly once — check-in and reprints never do. On the booked day the session appears on the Care Queue, where checking the patient in raises their ticket and vitals.</div></div>
</div>

<input type="text" id="ssSearch" class="ss-search" placeholder="Search patient name, code or treatment…" autocomplete="off">
<p class="ss-note" id="ssNoMatch" style="display:none;">Nothing on this page matches your search. Clear the box, widen the diary window, or check Everything.</p>

<div class="ss-sec">Paid sessions waiting to be booked</div>
<?php if(empty($waiting)): ?>
  <p class="ss-note">None waiting. Sessions become available here when a course is paid for — a paid plan bill or a sold session package.</p>
<?php else: ?>
  <p class="ss-note">Still owed, but not yet had. Booking one puts it on the diary below.</p>
  <div class="ss-grid">
  <?php foreach($waiting as $w):
    $rem = (int)((float)$w->units_total - (float)$w->units_used);
    $openBooked = (int)($w->booked_open ?? 0);
    $free = max(0, $rem - $openBooked);
    $perSession = (float)$w->units_total > 0 ? round((float)$w->funded_amount / (float)$w->units_total, 2) : 0;
  ?>
  <div class="ss-card">
    <div>
      <b><?= htmlspecialchars($w->patient_name ?: '—'); ?></b> <span style="color:var(--mp-muted);font-size:12px;"><?= htmlspecialchars($w->patient_code); ?></span>
      <div style="font-size:12px;color:var(--mp-muted);margin-top:3px;">
        <?= htmlspecialchars($w->treatment_name ?: 'Treatment course'); ?> —
        <b><?= $rem; ?> of <?= (int)$w->units_total; ?> session(s) still owed</b>
        · <b><?= $free; ?> free to book</b>
        <?php if($openBooked > 0): ?><span title="<?= $openBooked; ?> session(s) already on the diary">(<?= $openBooked; ?> booked)</span><?php endif; ?>
        · funded <?= $CI->currency($w->funded_amount); ?>
        <?= $w->expiry_date ? ' · expires ' . htmlspecialchars($w->expiry_date) : ''; ?>
      </div>
    </div>
    <?php if(!empty($can['schedule'])): ?>
      <div class="ss-acts">
      <?php if($free < 1): ?>
        <button class="mp-qa-btn blue" disabled style="opacity:.5;cursor:not-allowed;" title="Every remaining session is already booked"><i class="fa fa-calendar-plus-o"></i> Book a session</button>
      <?php else: ?>
        <button class="mp-qa-btn blue" onclick="bookSession(<?= (int)$w->id; ?>, <?= $perSession; ?>)"><i class="fa fa-calendar-plus-o"></i> Book a session</button>
      <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="ss-sec">Booked sessions (the diary)</div>
<div class="ss-days">
  <span class="ss-days-lbl">Show</span>
  <?php foreach($sess_ranges as $key => $lbl): ?>
    <a class="<?= $sess_range === $key ? 'on' : ''; ?>" href="<?= base_url('sessions') . '?range=' . $key; ?>"><?= htmlspecialchars($lbl); ?></a>
  <?php endforeach; ?>
</div>
<?php if(!empty($next_sess)): ?>
  <p class="ss-next">Next up: <b><?= htmlspecialchars($next_sess->patient_name ?: 'Patient'); ?></b> —
    <?= date('D d M H:i', strtotime($next_sess->scheduled_at)); ?><?= strtotime($next_sess->scheduled_at) >= strtotime('today') && strtotime($next_sess->scheduled_at) < strtotime('tomorrow') ? ' (today)' : ''; ?>,
    session <?= (int)$next_sess->session_no; ?> of <?= (int)$next_sess->units_total; ?>.</p>
<?php endif; ?>
<?php if((int)$sess_hidden > 0): ?>
  <p class="ss-note"><?= (int)$sess_hidden ?> booked session(s) fall outside this window —
    <a href="<?= base_url('sessions') . '?range=all'; ?>" style="text-decoration:underline">show everything</a>.</p>
<?php endif; ?>
<?php if(empty($sessions)): ?>
  <div class="ss-card"><?php if((int)$sess_hidden > 0): ?>Nothing in this window — the sessions are booked on other days, use the choices above.<?php else: ?>Nothing booked yet. Book one from the waiting list above, or check in a visit as a Treatment session on the Care Queue.<?php endif; ?></div>
<?php endif; ?>
<div class="ss-grid">
<?php foreach($sessions as $s): ?>
<div class="ss-card">
  <div>
    <b><?= htmlspecialchars($s->patient_name); ?></b> <span style="color:var(--mp-muted);font-size:12px;"><?= htmlspecialchars($s->patient_code); ?></span>
    <span class="ss-badge st-<?= $s->status; ?>"><?= strtoupper(str_replace('_',' ',$s->status)); ?></span>
    <div style="font-size:12px;color:var(--mp-muted);margin-top:3px;">
      Session <?= (int)$s->session_no; ?> of <?= (int)$s->units_total; ?> · <?= htmlspecialchars($s->scheduled_at); ?> · fee <?= $CI->currency($s->fee); ?>
      <?= $s->fee_posted ? ' · fee posted' : ''; ?>
    </div>
  </div>
  <div class="ss-acts">
    <?php if(!empty($s->encounter_id)): ?>
      <a class="mp-qa-btn blue" href="<?= base_url('care_queue/encounter/' . (int)$s->encounter_id); ?>"><i class="fa fa-list-ol"></i> On the Care Queue</a>
    <?php endif; ?>
    <?php if($s->status === 'scheduled' && $can['checkin']): ?>
      <button class="mp-qa-btn" onclick="act(<?= (int)$s->id; ?>,'checkin')">Check in</button>
    <?php endif; ?>
    <?php if($s->status === 'checked_in' && $can['checkin']): ?>
      <button class="mp-qa-btn blue" onclick="act(<?= (int)$s->id; ?>,'start')">Start</button>
    <?php endif; ?>
    <?php if(in_array($s->status,['in_progress','interrupted']) && $can['complete']): ?>
      <button class="mp-qa-btn green" onclick="act(<?= (int)$s->id; ?>,'complete')">Complete</button>
    <?php endif; ?>
    <?php if(in_array($s->status,['scheduled','checked_in']) && $can['checkin']): ?>
      <button class="mp-qa-btn" onclick="ns(<?= (int)$s->id; ?>)">No-show</button>
    <?php endif; ?>
    <a class="mp-qa-btn" href="<?= base_url('sessions/ticket/' . $s->id); ?>" target="_blank"><i class="fa fa-print"></i></a>
  </div>
</div>
<?php endforeach; ?>
</div>

<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
$('#ssSearch').on('input', function(){
  var q = $.trim($(this).val()).toLowerCase(), hits = 0;
  $('.ss-card').each(function(){
    var hit = q === '' || $(this).text().toLowerCase().indexOf(q) !== -1;
    $(this).toggleClass('ss-off', !hit);
    if(hit) hits++;
  });
  $('#ssNoMatch').toggle(q !== '' && hits === 0);
});
function act(id, a){ $.post('<?= base_url('sessions/'); ?>'+a+'/'+id, CSRF, function(r){
  if(r.status==='success'){
    cqToast(r.message, 'success');
    if(a === 'complete' && r.next){ offerNextSess(r.next); return; }
    setTimeout(function(){ location.reload(); }, 700);
  } else cqToast(r.message || 'Could not update the session.', 'error');
}, 'json'); }
function offerNextSess(n){
  var left = parseInt(n.sessions_left || 0, 10) || 0;
  cqConfirm('This session is done and drawn from the package. ' + left + ' session(s) are still owed on ' + (n.treatment_name || 'the package') + ' — book the next one now?',
    { title: 'Book the next session', okText: 'Book it now' }).then(function(yes){
    if(!yes){ setTimeout(function(){ location.reload(); }, 400); return; }
    bookSession(n.entitlement_id, parseFloat(n.fee) || 0);
  });
}
function ns(id){
  cqPrompt({ title: 'No-show', message: 'Why did the patient not attend?', placeholder: 'Reason', required: true, okText: 'Record no-show' }).then(function(reason){
    if(!reason) return;
    $.post('<?= base_url('sessions/no_show/'); ?>'+id, Object.assign({reason: reason}, CSRF), function(x){ if(x.status==='success'){ cqToast(x.message, 'success'); setTimeout(function(){ location.reload(); }, 700); } else cqToast(x.message || 'Could not record the no-show.', 'error'); }, 'json');
  });
}
function bookSession(entId, fee){
  mpBookSession({ title: 'Book a session', message: 'Pick the day and the time the patient will come in. On that day the session appears on the Care Queue, so the patient can be checked in for vitals and their ticket raised.' }).then(function(when){
    if(!when) return;
    $.post('<?= base_url('sessions/schedule'); ?>', Object.assign({entitlement_id: entId, scheduled_at: when, fee: fee.toFixed(2)}, CSRF), function(r){
      if(r.status==='success'){ cqToast(r.message, 'success'); setTimeout(function(){ location.reload(); }, 700); } else cqToast(r.message || 'Could not book the session.', 'error');
    }, 'json');
  });
}
</script>

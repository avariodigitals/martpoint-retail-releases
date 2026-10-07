<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.ss-card{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:10px!important;display:flex!important;justify-content:space-between!important;align-items:center!important;flex-wrap:wrap!important;gap:10px!important}
.ss-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.st-scheduled{background:#E0E7FF!important;color:#4338CA!important}.st-checked_in{background:#FEF3C7!important;color:#92400E!important}
.st-in_progress{background:#DBEAFE!important;color:#1E40AF!important}.st-completed{background:#DCFCE7!important;color:#166534!important}
.st-cancelled,.st-no_show{background:#FEE2E2!important;color:#991B1B!important}.st-interrupted{background:#FFEDD5!important;color:#9A3412!important}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub">Completion applies the fee and consumes entitlement exactly once — check-in and reprints never do</div></div>
</div>

<?php if(empty($sessions)): ?><div class="ss-card">No sessions yet.</div><?php endif; ?>
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
  <div style="display:flex;gap:6px;flex-wrap:wrap;">
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

<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
function act(id, a){ $.post('<?= base_url('sessions/'); ?>'+a+'/'+id, CSRF, function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json'); }
function ns(id){ var r=prompt('No-show reason:'); if(!r) return; $.post('<?= base_url('sessions/no_show/'); ?>'+id, Object.assign({reason:r},CSRF), function(x){ alert(x.message); if(x.status==='success') location.reload(); }, 'json'); }
</script>

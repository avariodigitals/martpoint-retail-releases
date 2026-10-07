<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.fb-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px;margin-bottom:16px}
table.fb{width:100%;border-collapse:collapse;font-size:13px}
table.fb th{text-align:left;font-size:11px;text-transform:uppercase;color:var(--mp-muted);padding:6px 4px;border-bottom:1px solid var(--mp-border)}
table.fb td{padding:9px 4px;border-bottom:1px solid var(--mp-border);vertical-align:top}
.stars{color:#F59E0B;font-weight:700}
.fb-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px}
.fb-badge.resolved{background:#D1FAE5;color:#065F46}.fb-badge.new,.fb-badge.acknowledged,.fb-badge.in_progress{background:#FEF3C7;color:#B45309}
.fb-btn{background:var(--mp-primary,#2563EB);color:#fff;border:0;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer}
</style>
<div class="mp-page-head"><div><h2>Patient Feedback</h2>
  <div class="mp-page-sub">Private ratings — never published automatically. <a href="<?= base_url('feedback/testimonials'); ?>">Testimonial moderation &rarr;</a></div>
</div></div>
<div class="fb-card">
<?php if(!$rows): ?><p style="color:var(--mp-muted);font-size:13px">No feedback yet.</p><?php else: ?>
<table class="fb">
  <tr><th>When</th><th>Patient</th><th>Rating</th><th>Comment</th><th>About</th><th>Follow-up</th><th></th></tr>
  <?php foreach($rows as $r): ?>
  <tr>
    <td><?= date('M j, Y', strtotime($r->created_at)); ?></td>
    <td><?= htmlspecialchars($r->patient_name ?: '—'); ?></td>
    <td class="stars"><?= str_repeat('★',(int)$r->rating) ?><span style="color:var(--mp-muted)"><?= (int)$r->rating ?>/5</span></td>
    <td style="max-width:320px"><?= nl2br(htmlspecialchars($r->comment)); ?></td>
    <td><?= htmlspecialchars($r->ref_type); ?> #<?= (int)$r->ref_id; ?></td>
    <td><span class="fb-badge <?= $r->follow_up_status; ?>"><?= htmlspecialchars(str_replace('_',' ',$r->follow_up_status)); ?></span>
      <?php if($r->follow_up_note): ?><br><span style="font-size:11px;color:var(--mp-muted)"><?= htmlspecialchars($r->follow_up_note); ?></span><?php endif; ?></td>
    <td><?php if($can_manage && $r->follow_up_status !== 'resolved'): ?>
      <button class="fb-btn" onclick="followUp(<?= (int)$r->id; ?>)">Follow up</button><?php endif; ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
</div>
<script src="<?= base_url('theme/assets/js/jquery.min.js'); ?>"></script>
<script>
var CSRF={'<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'};
function followUp(id){
  var n=prompt('Follow-up note (what was done / agreed)'); if(!n) return;
  var s=prompt('Status: acknowledged | in_progress | resolved','in_progress') || 'in_progress';
  $.post('<?= base_url('feedback/follow_up/'); ?>'+id, Object.assign({note:n,status:s},CSRF), function(r){alert(r.message); if(r.status==='success')location.reload();},'json');
}
</script>

<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.tm-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px;margin-bottom:16px}
table.tm{width:100%;border-collapse:collapse;font-size:13px}
table.tm th{text-align:left;font-size:11px;text-transform:uppercase;color:var(--mp-muted);padding:6px 4px;border-bottom:1px solid var(--mp-border)}
table.tm td{padding:9px 4px;border-bottom:1px solid var(--mp-border);vertical-align:top}
.tm-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px}
.tm-badge.pending{background:#FEF3C7;color:#B45309}.tm-badge.approved{background:#D1FAE5;color:#065F46}
.tm-badge.rejected,.tm-badge.withdrawn{background:#FEE2E2;color:#B91C1C}
.tm-btn{background:var(--mp-primary,#2563EB);color:#fff;border:0;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer;margin-right:6px}
.tm-btn.danger{background:#B91C1C}
</style>
<div class="mp-page-head"><div><h2>Testimonials — moderation</h2>
  <div class="mp-page-sub">Publication needs <b>patient consent + staff approval</b>. Patients can withdraw at any time — effective immediately.</div>
</div></div>
<div class="tm-card">
<?php if(!$rows): ?><p style="color:var(--mp-muted);font-size:13px">No testimonials submitted.</p><?php else: ?>
<table class="tm">
  <tr><th>When</th><th>By</th><th>Testimonial</th><th>Consent</th><th>Displays as</th><th>Status</th><th></th></tr>
  <?php foreach($rows as $t): ?>
  <tr>
    <td><?= date('M j, Y', strtotime($t->created_at)); ?></td>
    <td><?= htmlspecialchars($t->patient_name ?: '—'); ?></td>
    <td style="max-width:340px">“<?= nl2br(htmlspecialchars($t->body)); ?>”</td>
    <td><?= $t->publish_consent ? '<span style="color:#059669">Consented '.date('M j, Y', strtotime($t->consent_at ?: $t->created_at)).'</span>' : '<span style="color:#B91C1C">No consent</span>'; ?></td>
    <td><?= htmlspecialchars($t->display_mode==='anonymous'?'Anonymous':($t->display_mode==='first_name'?'First name':($t->display_name ?: 'Custom'))); ?></td>
    <td><span class="tm-badge <?= $t->status; ?>"><?= htmlspecialchars($t->status); ?></span>
      <?php if($t->status==='withdrawn' && $t->withdrawn_at): ?><br><span style="font-size:11px;color:var(--mp-muted)">withdrawn <?= date('M j, Y', strtotime($t->withdrawn_at)); ?></span><?php endif; ?>
      <?php if($t->moderation_note): ?><br><span style="font-size:11px;color:var(--mp-muted)"><?= htmlspecialchars($t->moderation_note); ?></span><?php endif; ?></td>
    <td><?php if($can_publish && $t->status==='pending'): ?>
      <button class="tm-btn" onclick="mod(<?= (int)$t->id; ?>,'approved')">Approve</button>
      <button class="tm-btn danger" onclick="mod(<?= (int)$t->id; ?>,'rejected')">Reject</button>
      <?php endif; ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
</div>
<script src="<?= base_url('theme/assets/js/jquery.min.js'); ?>"></script>
<script>
var CSRF={'<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'};
function mod(id,decision){ var n=decision==='rejected'?prompt('Reason for rejection (optional)')||'':''; $.post('<?= base_url('feedback/moderate/'); ?>'+id, Object.assign({decision:decision,note:n},CSRF), function(r){alert(r.message); if(r.status==='success')location.reload();},'json'); }
</script>
